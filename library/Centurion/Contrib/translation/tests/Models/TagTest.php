<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_TagTest extends Centurion_Test_DbTable
{
    
    public function setUp()
    {
        $this->setTable('translation/tag');
        
        $this->addColumns(
            array(
                'id',
                'tag',
            )
        );
        
        parent::setUp();
    }
}
