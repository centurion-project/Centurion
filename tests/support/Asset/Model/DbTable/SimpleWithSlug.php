<?php

class Asset_Model_DbTable_SimpleWithSlug extends Asset_Model_DbTable_Abstract
{
    protected $_name = 'test_simple_with_slug';

    protected $_rowClass = 'Asset_Model_DbTable_Row_SimpleWithSlug';
    
    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS test_simple_with_slug (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NOT NULL,
            `subtitle` VARCHAR( 255 ) NOT NULL,
            `slug` VARCHAR( 255 ) NOT NULL
            ) ENGINE = INNODB;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_simple_with_slug');
    }
}
