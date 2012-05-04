<?php

class Asset_Model_DbTable_WithRef extends Asset_Model_DbTable_Abstract
{
    protected $_name = 'test_with_ref';

    protected $_referenceMap = array(
        'simple'  =>  array(
            'columns'       => 'simple_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Asset_Model_DbTable_Simple',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        )
    );

    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE if not exists test_with_ref  (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `simple_id` INT( 11 ) UNSIGNED DEFAULT NULL ,
            `title` VARCHAR( 255 ) NOT NULL ,
            INDEX (  `simple_id` )
            ) ENGINE = INNODB;
EOS
        );

    }

    public function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_with_ref');
    }
}
