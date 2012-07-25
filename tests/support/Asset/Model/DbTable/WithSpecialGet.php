<?php

class Asset_Model_DbTable_WithSpecialGet extends Asset_Model_DbTable_Abstract
{
    protected $_name = 'test_with_special_get';

    protected $_rowClass = 'Asset_Model_DbTable_Row_SimpleWithSpecialGet';


    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS test_with_special_get (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NOT NULL
            ) ENGINE = INNODB;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_with_special_get');
    }

    public function __construct($config = array())
    {
        parent::__construct($config);
    }

}

