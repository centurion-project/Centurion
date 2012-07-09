<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Contrib_Core_SlugTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test slug when this one is already taken in the same table
     */
    public function testUniqSlug()
    {
        /**
         * Create a row
         *
         * In this case we expect :
         *  - slug = Centurion_Inflector::slugify($uniqIq)
         */
        $table = new Asset_Model_DbTable_SimpleWithSlug();
        $row = $table->createRow();
        $row->title = 'test';
        $row->save();
        
        // slug = Centurion_Inflector::slugify($uniqIq)
        $this->assertEquals(Centurion_Inflector::slugify('test'), $row->slug
            , 'The slug isn\'t equals to what we expect');


        /**
         * Create a row with the same name than the first we have create
         *
         * In this case, we expect :
         *  - slug (of flatpageTwoRow) != slug (of flatpageOneRow)
         */
        $row2 = $table->createRow();
        $row2->title = 'test';
        $row2->save();

        // slug (of flatpageTwoRow) != slug (of flatpageOneRow)
        $this->assertNotEquals($row->slug, $row2->slug
            , 'The slug need to be different in this case because he is already taken by the first object');
    }

    public function testIfIDontChangeDataSlugDontChange()
    {
        /**
         * @Given an empty table that implement slug
         */
        $table = new Asset_Model_DbTable_SimpleWithSlug();

        /**
         * @If a create a row with a title
         */
        $row = $table->createRow();
        $row->title = 'test';
        $row->save();

        $slug = $row->slug;
        /**
         * @And a save it again
         */
        $row->save();

        /**
         * @Except That the slug have not change
         */
        $this->assertEquals($slug, $row->slug);
    }

    public function testIfIChangeDataSlugChange()
    {
        /**
         * @Given an empty table that implement slug
         */
        $table = new Asset_Model_DbTable_SimpleWithSlug();
        $table->select()->fetchAll()->delete();
        
        /**
         * @If a create a row with a title
         */
        $row = $table->createRow();
        $row->title = 'test';
        $row->save();

        $slug = $row->slug;
        /**
         * @And i change the title
         * @And a save it again
         */
        $row->title = 'New title';
        $row->save();

        /**
         * @Except That the slug is not the same anymore
         */
        $this->assertNotEquals($slug, $row->slug);
    }
    
    /**
     *  Test slug when this one is composed of several columns
     */
    public function testSlugResultWithEmptyColumn()
    {
        /**
         * @Given an empty table that implement slug 
         * @And that this table have 2 columns that implement is used to create the slug
         */
        $table = new Asset_Model_DbTable_WithMultiColumnsForSlug();
        $table->select()->fetchAll()->delete();

        /**
         * @If i a create a new row
         * @And i fill the 2 columns
         */
        $titleSubtitleRow = $table->createRow();
        $titleSubtitleRow->title = 'Title slug test';
        $titleSubtitleRow->subtitle = 'Subtitle slug test';
        $titleSubtitleRow->save();

        /**
         * @Except that the slug is composed by the 2 columns, slugify, concatened by 
         */
        $this->assertEquals(Centurion_Inflector::slugify('Title slug test') . '-' . Centurion_Inflector::slugify('Subtitle slug test'), $titleSubtitleRow->slug
            , 'The slug isn\'t equals to what we expect');


        /**
         * In this case the slug is composed of the title
         *
         * We except :
         *  - slug = Centurion_Inflector::slugify('Titre de test')
         */
        $titleRow = $table->createRow();
        $titleRow->title = 'Only title slug test';
        $titleRow->save();

        $this->assertEquals(Centurion_Inflector::slugify('Only title slug test'), $titleRow->slug
            , 'The slug isn\'t equals to what we expect');


        /**
         * In this case the slug is composed of the subtitle
         *
         * We except :
         *  - slug = Centurion_Inflector::slugify('SubTitre de test')
         */
        $subtitleRow = $table->createRow();
        $subtitleRow->subtitle = 'Only subtitle slug test';
        $subtitleRow->save();

        $this->assertEquals(Centurion_Inflector::slugify('Only subtitle slug test'), $subtitleRow->slug
            , 'The slug isn\'t equals to what we expect');
    }
}
