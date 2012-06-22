<?php

class Core_Model_DbTable_Row_ContentType extends Centurion_Db_Table_Row_Abstract
{
    public function __toString()
    {
        return $this->name;
    }
}
