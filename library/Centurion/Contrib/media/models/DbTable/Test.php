<?php

class Media_Model_DbTable_Test extends Centurion_Db_Table_Abstract
{
    protected $_primary = 'id';
    
    protected $_name = 'media_test';
    
    protected $_manyDependentTables = array(
        'files'        =>  array(
            'refTableClass'     =>  'Media_Model_DbTable_File', 
            'intersectionTable' =>  'Media_Model_DbTable_JoinTestFile',
            'columns'   =>  array(
                'local'     =>  'test_id',
                'foreign'   =>  'file_id'
            )
        )
    );
}