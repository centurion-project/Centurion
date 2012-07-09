<?php
require_once dirname(__FILE__) . '/../../../../../../../tests/TestHelper.php';

class Core_Test_Traits_SoftDelete_DbTable extends PHPUnit_Framework_TestCase
{
    public function testGetContentTypeIdOf()
    {
        $contentTypeTable = new Core_Model_DbTable_ContentType();

        $table = new Asset_Model_DbTable_WithMultiColumnsForSlug();
        $row = new Asset_Model_DbTable_Row_WithMultiColumnsForSlug(array('table' => $table));

        $contentType1 = $contentTypeTable->getContentTypeIdOf($table);
        $contentType2 = $contentTypeTable->getContentTypeIdOf($row);

        //The content Type Id of table and row should be the same.
        $this->assertEquals($contentType1, $contentType2);
    }
}
