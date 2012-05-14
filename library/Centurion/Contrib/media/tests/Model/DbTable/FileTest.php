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

        Media_Model_DbTable_File::setPx(null);

        $row2 = $fileTable->getPx();
        $this->assertEquals($row->id, $row2->id);
    }

    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::insert
     */
    public function testInsertImage()
    {
        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png', 'delete_original' => 0));
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
     * Test unit of test unit
     */
    public function testGetRelativePathFromTo()
    {
        $fileTable  = new Media_Model_DbTable_File();

        $row = $fileTable->createRow();

        $os = 'linux';
        if (isset($_SERVER['OS']) && preg_match('`Window`i', $_SERVER['OS'])) {
            $os = 'windows';
        }
        if ('linux' == $os) {
            $this->assertEquals('../user', $row->getRelativePathFromTo('/home', '/user', false));
            $this->assertEquals('../../user', $row->getRelativePathFromTo('/home/lchenay', '/user', false));
            $this->assertEquals('../oo/test', $row->getRelativePathFromTo('/home/lchenay', '/home/oo/test', false));
        } else {
            $this->assertEquals('..\user', $row->getRelativePathFromTo('c:\home', 'c:\user', false));
            $this->assertEquals('..\..\user', $row->getRelativePathFromTo('c:\home\lchenay', 'c:\user', false));
            $this->assertEquals('..\oo\test', $row->getRelativePathFromTo('c:\home\lchenay', 'c:\home\oo\test', false));
            $this->assertEquals('..\library\Centurion\Contrib\media\tests\Support\images\centurion.png', $row->getRelativePathFromTo('C:\UwAmp\www\Centurion\tests', 'C:\UwAmp\www\Centurion/library/Centurion/Contrib/media/tests/Support/images/centurion.png', false));
        }
    }


    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::insert
     */
    public function testInsertImageWithRelativePath()
    {
        $table = Centurion_Db::getSingleton('media/file');

        $currentDir = realpath('.');
        $img = realpath(APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png');

        if (!file_exists($img)) {
            $this->fail('Original image is missing for test');
        }

        $row = $table->createRow();
        $file = $row->getRelativePathFromTo($currentDir, $img);

        $mediaRow = $table->createRow(array('local_filename' => $file, 'delete_original' => 0));
        $mediaRow->save();

        $imageInfoArray = array(
            'mime' => 'image/png',
            'local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png',
            'filename' => 'centurion.png',
            'filesize' => '676'
        );

        $this->assertEquals($imageInfoArray['mime'], $mediaRow->mime, 'Mime type doesn\'t match');

        $mediaRow->delete();
    }

    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::update
     */
    public function testUpdateImage()
    {
        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png', 'delete_original' => 0));
        $mediaRow->save();

        $newMediaRow = clone $mediaRow;
        $newMediaRow->local_filename = APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/tank_centurion.jpg';
        $newMediaRow->save();

        $this->assertEquals(filesize(APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/tank_centurion.jpg'), $newMediaRow->filesize);
        $this->assertEquals('tank_centurion.jpg', $newMediaRow->filename);

        if ('image/jpg' !== $newMediaRow->mime && 'image/jpeg' !== $newMediaRow->mime) {
            $this->fail('Failed in mime type detection.');
        }
    }


    public function testMultipleSameImage()
    {
        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png', 'delete_original' => 0));
        $mediaRow->save();

        $mediaRow2 = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png', 'delete_original' => 0));
        $mediaRow2->save();

        $this->assertNotEquals($mediaRow->id, $mediaRow2->id);
        $this->assertEquals($mediaRow->file_id, $mediaRow2->file_id);
        $this->assertEquals($mediaRow->filesize, $mediaRow2->filesize);

        $this->assertEquals($mediaRow->local_filename, $mediaRow2->local_filename);
        $this->assertEquals($mediaRow->mime, $mediaRow2->mime);

        $this->assertEquals($mediaRow->sha1, $mediaRow2->sha1);

        $mediaRow2->local_filename = APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/tank_centurion.jpg';
        $mediaRow2->save();

        $this->assertNotEquals($mediaRow->id, $mediaRow2->id);
        $this->assertNotEquals($mediaRow->file_id, $mediaRow2->file_id);
        $this->assertNotEquals($mediaRow->filesize, $mediaRow2->filesize);

        $this->assertNotEquals($mediaRow->local_filename, $mediaRow2->local_filename);
        $this->assertNotEquals($mediaRow->mime, $mediaRow2->mime);

        $this->assertNotEquals($mediaRow->sha1, $mediaRow2->sha1);
    }
}
