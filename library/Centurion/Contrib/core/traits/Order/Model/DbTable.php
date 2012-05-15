<?php

class Core_Traits_Order_Model_DbTable extends Centurion_Traits_Model_DbTable_Abstract
{

    protected $_requiredColumns = array('order');

    public function init()
    {
        Centurion_Signal::factory('on_select_joinInner')->connect(array($this, 'onJoinInner'), $this->_model->getSelectClass());
    }

    /**
     * add filters to the default select query
     * @param $select Zend_Db_Table_Select
     * @see Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onJoinInner($signal, $sender, $select, $name)
    {
        if (!$select instanceof Centurion_Db_Table_Select) {
            return;
        }

        if (is_array($name)) {
            $name = current($name);
        }

        if ($name !== $this->_model->info(Zend_Db_Table_Abstract::NAME)) {
            return;
        }

        if (!Centurion_Db_Table_Abstract::getFiltersStatus()) {
            return;
        }

        $select->order('auth_user.order asc');
    }
}
