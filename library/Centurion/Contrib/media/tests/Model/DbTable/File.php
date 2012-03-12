<?php

require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

class Media_Test_Model_DbTable_File extends PHPUnit_Framework_TestCase
{
    public function testGetPx()
    {
        $fileTable = Centurion_Db::getSingleton('media/file');
        $pxRow = $fileTable->findOneBy('id', '88888888');

        if (null !== $pxRow) {
            //$pxRow->delete();
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
}