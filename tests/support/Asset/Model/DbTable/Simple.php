<?php

class Asset_Model_DbTable_Simple extends Centurion_Db_Table_Abstract
{
    protected $_name = 'test_simple';

    public function __construct($config = array())
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE  test_simple (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NOT NULL
            ) ENGINE = INNODB;
EOS
        );

        parent::__construct($config);
    }

    public function __destruct()
    {
        $this->getDefaultAdapter()->query('Drop table test_simple');
    }
}
