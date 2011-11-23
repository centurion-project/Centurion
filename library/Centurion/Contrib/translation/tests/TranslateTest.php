<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Translation_Test_TranslateTest extends PHPUnit_Framework_TestCase
{
    public function testUntranslateWordAreCatchedWithTag()
    {
        $tableTagUid = Centurion_Db::getSingleton('translation/tagUid');
        $tableTranslation = Centurion_Db::getSingleton('translation/translation');
        $tableTag = Centurion_Db::getSingleton('translation/tag');
        $tableUid = Centurion_Db::getSingleton('translation/uid');
        
//        $tableTagUid->delete(array(new Zend_Db_Expr('true')));
//        $tableTranslation->delete(array(new Zend_Db_Expr('true')));
//        $tableTag->delete(array(new Zend_Db_Expr('true')));
//        $tableUid->delete(array(new Zend_Db_Expr('true')));
        
        $tableTagUid->fetchAll()->delete();
        $tableTranslation->fetchAll()->delete();
        $tableTag->fetchAll()->delete();
        $tableUid->fetchAll()->delete();
        
        $translate = new Translation_Model_Translate_Adapter_Array(array());
        
        $this->assertEquals(0, $tableTag->select(true)->count());
        $this->assertEquals(0, $tableTagUid->select(true)->count());
        $this->assertEquals(0, $tableTranslation->select(true)->count());
        $this->assertEquals(0, $tableUid->select(true)->count());
        
        $str = sha1(uniqid());
        $translate->translate($str . '@tag1,tag2');
        
        $this->assertEquals(2, $tableTag->select(true)->count());
        $this->assertEquals(2, $tableTagUid->select(true)->count());
        $this->assertEquals(0, $tableTranslation->select(true)->count());
        $this->assertEquals(1, $tableUid->select(true)->count());
        
        $uidRow = $tableUid->findOneByUid($str);
        
        $this->assertNotNull($uidRow);
        
        $this->assertEquals(2, $uidRow->tags->count());
        
        $this->assertEquals('tag1', $uidRow->tags[0]->tag);
        $this->assertEquals('tag2', $uidRow->tags[1]->tag);
        
        $translate = null;
        
        $uidRow->delete();
    }
    
    public function testApostropheInOriginalWording()
    {
        $translate = new Translation_Model_Translate_Adapter_Array(array());
        $original = '3 raisons de s\'inscrir ? ! ? %s e';
        $translatedWording = $translate->translate($original);
        $this->assertEquals($original, $translatedWording);
        
        $uid = Centurion_Db::getSingleton('translation/uid')->fetchRow(null, 'id desc');
        $this->assertEquals($original, $uid->uid);
    }
}
