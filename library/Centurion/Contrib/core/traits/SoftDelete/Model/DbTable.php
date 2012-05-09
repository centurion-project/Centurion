<?php
class Core_Traits_SoftDelete_Model_DbTable extends Centurion_Traits_Model_DbTable_Abstract
{
    protected $_requiredColumns = array('is_deleted');

    public function init()
    {
        Centurion_Signal::factory('on_select_joinInner')->connect(array($this, 'onJoinInner'), $this->_model->getSelectClass());
    }

    /**
     * add filters to the default select query
     * @see Centurion/Contrib/core/traits/Version/Model/Core_Traits_Version_Model_DbTable::onSelect()
     */
    public function onJoinInner($signal, $sender, $select, $name)
    {
        if (!$select instanceof Centurion_Db_Table_Select) {
            return;
        }

        $corellationName = 0;
        if (is_array($name)) {
            $corellationName = key($name);
            $name = current($name);
        }

        if (0 === $corellationName) {
            $corellationName = $name;
        }

        if ($name !== $this->_model->info(Zend_Db_Table_Abstract::NAME)) {
            return;
        }

        if (!Centurion_Db_Table_Abstract::getFiltersStatus()) {
            return;
        }

        $select->where($name . '.is_deleted  = 0');
    }

    public function delete($where)
    {
        $this->_model->update(array('is_deleted' => 1), $where);
    }

    public function checkForRequiredColumn($additionalCols = array())
    {
        try {
            parent::checkForRequiredColumn($additionalCols);
        } catch (Exception $e) {
            if (APPLICATION_ENV == 'development') {
                $tableName = $this->_model->info(Zend_Db_Table_Abstract::NAME);
                $str = 'ALTER TABLE  `' . $tableName . '` ADD  `is_deleted` INT( 1 ) UNSIGNED NOT NULL , ADD INDEX (  `is_deleted` )';
                throw new Exception('Fix this by adding "' . $str . '" to your BDD.', 0, $e);
            }

            throw $e;
        }
    }
}
