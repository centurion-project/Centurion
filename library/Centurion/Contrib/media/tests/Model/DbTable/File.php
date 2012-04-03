<?php

require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

class Media_Test_Model_DbTable_File extends PHPUnit_Framework_TestCase
{
    public function testGetPx()
    {
        $fileTable = new Media_Model_DbTable_File();
        $pxRow = $fileTable->findOneBy('id', '88888888');

        if (null !== $pxRow) {
            $pxRow->delete();
        }

        $row = $fileTable->getPx();

        $this->assertNotNull($row);

        try {
            $str = $row->getStaticUrl();
        } catch (Exception $e) {
            $this->fail('Function getStaticUrl should not raise an exception');
        }

        //TODO: check that the file is 1px height and width
    }

    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::insert
     */
    public function testInsertImage()
    {
        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png'));
        $mediaRow->save();

        $imageInfoArray = array(
            'mime' => 'image/png',
            'local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png',
            'filename' => 'centurion.png',
            'filesize' => '676'
        );

        $this->assertEquals($imageInfoArray['mime'], $mediaRow->mime, 'Mime type doesn\'t match');
    }

    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::update
     */
    public function testUpdateImage()
    {
        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png'));
        $mediaRow->save();

        $newMediaRow = clone $mediaRow;
        $newMediaRow->local_filename = APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/tank_centurion.jpg';
        $newMediaRow->save();

        $this->assertEquals(filesize(APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/tank_centurion.jpg'), $newMediaRow->filesize);
        $this->assertEquals('tank_centurion.jpg', $newMediaRow->filename);
        $this->assertEquals('image/jpg', $newMediaRow->mime);
    }
}
