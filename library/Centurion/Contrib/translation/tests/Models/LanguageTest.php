<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_LanguageTest extends Centurion_Test_DbTable
{
    
    public function setUp()
    {
        $this->setTable('translation/language');
        
        $this->addColumns(
            array(
                'id',
                'locale',
                'name',
                'flag',
            )
        );
        
        parent::setUp();
    }
}
