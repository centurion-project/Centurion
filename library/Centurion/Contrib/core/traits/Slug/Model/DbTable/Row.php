<?php

class Core_Traits_Slug_Model_DbTable_Row extends Centurion_Traits_Model_DbTable_Row_Abstract
{

    protected $_requiredFields = array('slug');

    public function init(){
        parent::init();

        Centurion_Signal::factory('pre_save')->connect(array($this, 'preSave'), $this->_row);
    }

    public function preSave()
    {
        // Get the column used to generate the slug
        $slugParts = $this->_row->getSlugifyName();

        $slugifiedParts = array();
        // If one of the column used to generate the slug has been modified OR if the row is new : generate a slug
        if (count(array_intersect($this->_row->getModifiedFields(), (array) $slugParts)) || $this->isNew()) {
            foreach ((array) $slugParts as $part) {
                $partValue = $this->_row->{$part};
                if (null == $partValue) {
                    continue;
                }
                // Slugify each value of selected columns
                $slugifiedParts[] = Centurion_Inflector::slugify($partValue);
            }

            // Assemble the slugified values to get a slug
            $slug = implode('-', $slugifiedParts);

            if (null == $slug) {
                $slug = '-';
            }

            // Get the current slug of the row ($currentSlug = null if the row is new)
            $currentSlug = $this->_row->slug;
            /**
             * Get the original slug
             *
             * Explanation : If a slug is already taken by another row, the generated slug is {expected_slug}_#
             *               where # is an incremental value of the number of occurrences of this slug
             */
            if ($separatorPos = strpos($this->_row->slug, '_')) {
                $currentSlug = substr($currentSlug, 0, $separatorPos);
            }

            // Get the table name
            $name = $this->_row->getTable()->info('name');

            $select = $this->getTable()->select();
            $filters = array();
            // TODO : Comment needed here
            if (method_exists($this->_row, 'getFilterFieldsForSlug')) {
                $filterField = $this->_row->getFilterFieldsForSlug();
                if(is_array($filterField)) {
                    foreach($filterField as $field) {
                        $filters[$field] = $this->_row->{$field};
                    }
                }
            }
            // TODO : Comment needed here
            if (!$this->_row->isNew()) {
                foreach($this->_getPrimaryKey() as $key => $value) {
                    $filters[Centurion_Db_Table_Select::OPERATOR_NEGATION . $key] = $value;
                }
            }
            // Generate the array used for filters method to find identical slug
            $filters['slug__' . Centurion_Db_Table_Select::OPERATOR_CONTAINS] = $slug.'%';

            // Get all the rows of the table with the same slug
            $rows = $select->filter($filters)->fetchAll();

            // If the slug we want to use is already taken generate a new one with the syntax : {slug}_#
            if ($rows->count() > 0) {
                $identicalSlugIds = array();
                foreach ($rows as $row) {
                    if ($separatorPos = strpos($row->slug, '_')) {
                        $identicalSlugIds[] = substr($row->slug, ++$separatorPos);
                    }
                }

                /**
                 * Generate the suffix "_#" for the slug
                 *
                 * /!\ During the generation of suffix _#, if we have a a free slot between two suffix
                 *     Ex : We have a table with three row, their respective slug are : slug, slug_1 & slug_3
                 *          We fill this slot with the current generated slug and we get :
                 *          slug, slug_1, slug_3, slug_2 (= current generated slug)
                 */
                $i = 1;
                if(count($identicalSlugIds) > 0) {
                    while(in_array($i, $identicalSlugIds)) {
                        $i++;
                    }
                }
                // Add suffix "_#" at the end of the generated slug
                $slug .= '_' . $i;
            }

            $this->_row->slug = $slug;
        }
    }
}
