<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_TagUidTest extends Centurion_Test_DbTable
{
    
    public function setUp()
    {
        global $application;
        $application->bootstrap('db');

        $this->setTable('translation/tagUid');
        
        $this->addColumns(
            array(
                'uid_id',
                'tag_id',
            )
        );
        
        parent::setUp();
    }
}
