<?php

class Asset_Model_DbTable_SimpleWithSlugAndTranslation extends Asset_Model_DbTable_Abstract implements Core_Traits_Slug_Model_DbTable_Row_Interface
{
    protected $_name = 'test_simple_with_slug_and_translation';

    protected $_rowClass = 'Asset_Model_DbTable_Row_SimpleWithSlugAndTranslation';
    
    protected function _createTable()
    {
        $this->getDefaultAdapter()->query(<<<EOS
            CREATE TABLE IF NOT EXISTS test_simple_with_slug_and_translation (
            `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
            `title` VARCHAR( 255 ) NOT NULL,
            `subtitle` VARCHAR( 255 ) NOT NULL,
            `slug` VARCHAR( 255 ) NOT NULL,
            'original_id' int(11) unsigned default NULL,
            'language_id' int(11) unsigned not null
            ) ENGINE = INNODB;
EOS
        );
    }

    protected function _destructTable()
    {
        $this->getDefaultAdapter()->query('Drop table test_simple_with_slug_and_translation');
    }

    public function getSlugifyName()
    {
        return array('title', 'subtitle');
    }
}
