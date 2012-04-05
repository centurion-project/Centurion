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

    protected function _getRelativePathFromTo($from, $to)
    {
        $from = realpath($from);
        $to = realpath($to);

        $relative = '';

        $currentTab = preg_split('`[/\\\\]`', $from);
        $toTab = preg_split('`[/\\\\]`', $to);

        $separated = false;

        foreach ($currentTab as $key => $val) {
            if (isset($toTab[$key]) && $toTab[$key] !== $val) {
                $separated = true;
                $separatedAt = $key;
            }
            if ($separated) {
                $relative .= '../';
            }
        }

        $relative .= implode('/', array_slice($toTab, $separatedAt));

        return $relative;
    }

    /**
     * Test unit of test unit
     */
    public function testGetRelativePathFromTo()
    {
        $this->assertEquals('../user', $this->_getRelativePathFromTo('/home', '/user'));
        $this->assertEquals('../../user', $this->_getRelativePathFromTo('/home/lchenay', '/user'));
        $this->assertEquals('../oo/test', $this->_getRelativePathFromTo('/home/lchenay', '/home/oo/test'));
    }


    /**
     * Test the update of an image
     * @covers Media_Model_DbTable_File::insert
     */
    public function testInsertImageWithRelativePath()
    {

        $currentDir = realpath('.');
        $img = APPLICATION_PATH . '/../library/Centurion/Contrib/media/tests/Support/images/centurion.png';

        $file = $this->_getRelativePathFromTo($currentDir, $img);

        $mediaRow = Centurion_Db::getSingleton('media/file')->createRow(array('local_filename' => $file));
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
