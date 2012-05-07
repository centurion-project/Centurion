<?php

require_once dirname(__FILE__) . '/../../../../../../../../tests/TestHelper.php';

class Media_Test_Form_Model_Admin_FileTest extends PHPUnit_Framework_TestCase
{
    public function testForm()
    {
        $fileName = tempnam(sys_get_temp_dir(), 'test');
        copy(APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png', $fileName);

        //We create empty data.
        $_FILES['filename_'] = array(
            'name' => 'centurion.png',
            'type' => 'image/png',
            'tmp_name' => $fileName,
            'error' => 4,
            'size' => 676,
        );

        $form = new Media_Form_Model_Admin_File();

        $result = $form->getFilename()->isValid(array());
        $this->assertTrue($result);

        $result = $form->isValid(array());

        $this->assertTrue($result);

    }
}
