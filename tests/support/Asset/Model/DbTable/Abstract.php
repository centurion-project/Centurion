<?php

abstract class Asset_Model_DbTable_Abstract extends Centurion_Db_Table
{
    static $_instance = array();

     public function __construct($config = array())
    {
        if (!isset(self::$_instance[get_class($this)])) {
            self::$_instance[get_class($this)] = 0;
        }

        if (self::$_instance[get_class($this)] == 0) {
            $this->_createTable();
        }

        self::$_instance[get_class($this)]++;

        parent::__construct($config);
    }

    abstract protected function _createTable();
    abstract protected function _destructTable();

    public function __destruct()
    {
        self::$_instance[get_class($this)]--;

        if (self::$_instance[get_class($this)] == 0) {
            $this->_destructTable();
        }
    }
}
