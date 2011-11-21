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
        foreach ((array) $slugParts as $part) {
            $slugifiedParts[] = Centurion_Inflector::slugify($this->_row->{$part});
        }

        $slug = implode('-', $slugifiedParts);

        $currentSlug = $this->_row->slug;
        if ($separatorPos = strpos($this->_row->slug, '_'))
            $currentSlug = substr($currentSlug, 0, $separatorPos);

        $name = $this->_row->getTable()->info('name');

        $where = array();
        if ($this->_row->pk) {
            $where = array($name . '.id <> ?' => $this->_row->pk);
        }
        $count = $this->_row->getTable()->fetchAll(array_merge($where, array($name . '.slug LIKE ?' => $slug.'%')))->count();

        $slug .= ($count ? '_' . (++$count) : '');
        $this->_row->slug = $slug;
    }
}