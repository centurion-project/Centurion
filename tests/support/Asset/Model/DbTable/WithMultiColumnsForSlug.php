<?php

class Asset_Model_DbTable_WithMultiColumnsForSlug extends Asset_Model_DbTable_Abstract
{
    protected $_name = 'test_withmulticolumnsforslug';

    protected $_rowClass = 'Asset_Model_DbTable_Row_WithMultiColumnsForSlug';

    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS test_withmulticolumnsforslug (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NULL ,
            `subtitle` VARCHAR( 255 ) NULL,
            `slug` VARCHAR( 255 ) NOT NULL

            ) ENGINE = INNODB;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_withmulticolumnsforslug');
    }
}
