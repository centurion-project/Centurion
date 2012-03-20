<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Cms_Test_FlatPageTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        
    }
    
    protected function tearDown()
    {
        
    }
    
    public function testModel()
    {
        try {
            $guidelineTable = Centurion_Db::getSingleton('cms/flatpage');
        } catch (Centurion_Db_Exception $e) {
            $this->fail('Table does not exists');
        }
        
        $this->assertTrue($guidelineTable->hasColumn('id'));
        $this->assertTrue($guidelineTable->hasColumn('title'));
        $this->assertTrue($guidelineTable->hasColumn('slug'));
        $this->assertTrue($guidelineTable->hasColumn('description'));
        $this->assertTrue($guidelineTable->hasColumn('keywords'));
        $this->assertTrue($guidelineTable->hasColumn('body'));
        $this->assertTrue($guidelineTable->hasColumn('url'));
        $this->assertTrue($guidelineTable->hasColumn('id'));
        
        $this->assertTrue($guidelineTable->hasColumn('flatpage_template_id'));
        $this->assertTrue($guidelineTable->hasColumn('published_at'));
        $this->assertTrue($guidelineTable->hasColumn('created_at'));
        $this->assertTrue($guidelineTable->hasColumn('updated_at'));
        $this->assertTrue($guidelineTable->hasColumn('is_published'));
        
        $this->assertTrue($guidelineTable->hasColumn('mptt_lft'));
        $this->assertTrue($guidelineTable->hasColumn('mptt_rgt'));
        $this->assertTrue($guidelineTable->hasColumn('mptt_level'));
        $this->assertTrue($guidelineTable->hasColumn('mptt_tree_id'));
        $this->assertTrue($guidelineTable->hasColumn('mptt_parent_id'));
        
        $this->assertTrue($guidelineTable->hasColumn('original_id'));
        $this->assertTrue($guidelineTable->hasColumn('language_id'));
        $this->assertTrue($guidelineTable->hasColumn('forward_url'));
        $this->assertTrue($guidelineTable->hasColumn('flatpage_type'));
        $this->assertTrue($guidelineTable->hasColumn('route'));
        $this->assertTrue($guidelineTable->hasColumn('class'));
        $this->assertTrue($guidelineTable->hasColumn('cover_id'));
        
    }
}
