<?php
class User_Model_DbTable_Row_Profile extends Centurion_Db_Table_Row_Abstract
{
    public function __toString()
    {
        return $this->nickname;
    }
}
