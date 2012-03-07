<?php
require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Db_Table_RowSetTest extends PHPUnit_Framework_TestCase
{
    public function testSleep()
    {
        $table = Centurion_Db::getSingleton('auth/group');
        
        $table->insert(array('name' => 'test1'));
        
        $rowSet = $table->fetchAll(array('name=?' => 'test1'));
        
        $str = serialize($rowSet);
        $rowSet = unserialize($str);
        
        foreach ($rowSet as $row) {
            $row->delete();
        }
    }
}