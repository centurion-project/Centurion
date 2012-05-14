<?php


class Media_Test_Controller_ImageController extends Centurion_Test_PHPUnit_ControllerTestCase
{
    public function testImageWithoutEffect()
    {
        list($file, $created) = Centurion_Db::getSingleton('media/file')->getOrCreate(array('delete_original' => 0, 'local_filename' => APPLICATION_PATH . '/../tests/support/imgs/php.jpg'));

        $url = $file->getStaticUrl();

        $this->assert200($url);

        $file->delete();

        $this->assertNot200($url);
    }
}
