<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_UidTest extends Centurion_Test_DbTable
{
    
    public function setUp()
    {
        $this->setTable('translation/uid');
        
        $this->addColumns(
            array(
                'id',
                'uid',
            )
        );
        
        parent::setUp();
    }
}
