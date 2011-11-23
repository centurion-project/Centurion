<?php

class Cms_Model_DbTable_Row_FlatpageTemplate extends Centurion_Db_Table_Row_Abstract
{
    public function __toString()
    {
        return $this->name;
    }
}