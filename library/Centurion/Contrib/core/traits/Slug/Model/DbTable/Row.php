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
        $slugParts = $this->_row->getSlugifyName();

        $slugifiedParts = array();
        if (count(array_intersect($this->_row->getModifiedFields(), (array) $slugParts)) || $this->isNew()) {
            foreach ((array) $slugParts as $part) {
                $partValue = $this->_row->{$part};
                if (null == $partValue) {
                    continue;
                }
                // Slugify each value of selected columns
                $slugifiedParts[] = Centurion_Inflector::slugify($partValue);
            }

            $slug = implode('-', $slugifiedParts);

            if (null == $slug) {
                $slug = '-';
            }

            // Get the current slug of the row ($currentSlug = null if the row is new)
            $currentSlug = $this->_row->slug;
            if ($separatorPos = strpos($this->_row->slug, '_')) {
                $currentSlug = substr($currentSlug, 0, $separatorPos);
            }

            $name = $this->_row->getTable()->info('name');

            $select = $this->getTable()->select();
            $filters = array();
            if(method_exists($this->_row, 'getFilterFieldsForSlug')) {
                $filterField = $this->_row->getFilterFieldsForSlug();
                if(is_array($filterField)) {
                    foreach($filterField as $field) {
                        $filters[$field] = $this->_row->{$field};
                    }
                }
            }
            if (!$this->_row->isNew()) {
                foreach($this->_getPrimaryKey() as $key => $value) {
                    $filters[Centurion_Db_Table_Select::OPERATOR_NEGATION . $key] = $value;
                }
            }
            $filters['slug__' . Centurion_Db_Table_Select::OPERATOR_CONTAINS] = $slug.'%';

            $rows = $select->filter($filters)->fetchAll();
            
            if ($rows->count() > 0) {
                $identicalSlugIds = array();
                foreach ($rows as $row) {
                    if ($separatorPos = strpos($row->slug, '_')) {
                        $identicalSlugIds[] = substr($row->slug, ++$separatorPos);
                    }
                }
                if(count($identicalSlugIds) > 0) {
                    $i = 1;
                    while(in_array($i, $identicalSlugIds)) {
                        $i++;
                    }
                    $slug .= '_' . $i;
                }
            }
            $this->_row->slug = $slug;
        }
    }
}
