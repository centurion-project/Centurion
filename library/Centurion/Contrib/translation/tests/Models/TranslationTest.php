<?php

require_once dirname(__FILE__) . '/../../../../../../tests/TestHelper.php';

class Translation_Test_Models_TranslationTest extends Centurion_Test_DbTable
{
    
    public function setUp()
    {
        $this->setTable('translation/translation');
        
        $this->addColumns(
            array(
                'translation',
                'uid_id',
                'language_id',
            )
        );
        
        parent::setUp();
    }
}
