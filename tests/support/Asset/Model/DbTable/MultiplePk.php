<?php

class Asset_Model_DbTable_MultiplePk extends Centurion_Db_Table_Abstract
{
    protected $_name = 'test_multiple_pk';

    public function __construct($config = array())
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE test_multiple_pk (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
            `title` VARCHAR( 255 ) NOT NULL ,
            PRIMARY KEY (  `id` ,  `title` )
            ) ENGINE = INNODB;
EOS
        );

        parent::__construct($config);
    }

    public function __destruct()
    {
        $this->getDefaultAdapter()->query('Drop table test_multiple_pk');
    }
}
