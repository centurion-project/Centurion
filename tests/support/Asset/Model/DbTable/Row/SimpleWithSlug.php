<?php

class Asset_Model_DbTable_Row_SimpleWithSlug extends Centurion_Db_Table_Row implements Core_Traits_Slug_Model_DbTable_Row_Interface
{
    public function getSlugifyName()
    {
        return array('title', 'subtitle');
    }
}
