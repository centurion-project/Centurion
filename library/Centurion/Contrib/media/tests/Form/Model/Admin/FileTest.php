<?php

require_once dirname(__FILE__) . '/../../../../../../../../tests/TestHelper.php';

class Media_Test_Form_Model_Admin_FileTest extends PHPUnit_Framework_TestCase
{
    public function testFormEmpty()
    {
        $form = new Media_Form_Model_Admin_File();

        //We create empty data.
        $_FILES[$form->getFilename()->getName()] = array();

        var_dump($form->getFilename()->isValid(array()));
        var_dump($form->getFilename()->getErrorMessages());
        $result = $form->isValid(array());

        var_dump($form->getErrorMessages());
        $this->assertTrue($result);

    }
}
