<?php
class Core_Traits_SoftDelete_Model_DbTable extends Centurion_Traits_Model_DbTable_Abstract
{
    protected $_requiredColumns = array('is_deleted');

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
