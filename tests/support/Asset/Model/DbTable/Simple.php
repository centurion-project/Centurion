<?php

class Asset_Model_DbTable_Simple extends Asset_Model_DbTable_Abstract
{
    protected $_name = 'test_simple';

    protected $_dependentTables = array(
        'with_refs' => 'Asset_Model_DbTable_WithRef',
    );

    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS test_simple (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NOT NULL
            ) ENGINE = INNODB;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_simple');
    }
}
