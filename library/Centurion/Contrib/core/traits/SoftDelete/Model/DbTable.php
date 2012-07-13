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

        if (is_array($name)) {
            $name = current($name);
        }

        if ($name !== $this->_model->info(Zend_Db_Table_Abstract::NAME)) {
            return;
        }

        if (!Centurion_Db_Table_Abstract::getFiltersStatus()) {
            return;
        }

        /**
         * @TODO: add manage of alias
         */
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
            if (APPLICATION_ENV == 'testing') {
                return $this->forceColumn();
            }

            if (APPLICATION_ENV == 'development') {
                $str = $this->_getSqlForCreateColumn();
                throw new Exception('Fix this by adding "' . $str . '" to your BDD.', 0, $e);
            }

            throw $e;
        }
    }

    protected function _getSqlForCreateColumn()
    {
        $tableName = $this->_model->info(Zend_Db_Table_Abstract::NAME);
        $str = 'ALTER TABLE  `' . $tableName . '` ADD  `is_deleted` INT( 1 ) UNSIGNED NOT NULL , ADD INDEX (  `is_deleted` )';
        return $str;
    }

    public function forceColumn()
    {
        $this->_model->getAdapter()->query($this->_getSqlForCreateColumn());
    }
}
