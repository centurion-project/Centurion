<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Contrib_Core_SlugTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test slug when this one is already taken in the same table
     */
    public function testUniqSlug()
    {
        // TODO : remove uniqid
        $uniqId = 'test' . uniqid();

        /**
         * Create a flatpage
         *
         * In this case we expect :
         *  - slug = Centurion_Inflector::slugify($uniqIq)
         */
        $flatpageOneRow = Centurion_Db::getSingleton('cms/flatpage')->createRow(array(
            'title' => $uniqId,
            'flatpage_template_id' => 1,
        ));
        $flatpageOneRow->save();

        // slug = Centurion_Inflector::slugify($uniqIq)
        $this->assertEquals(Centurion_Inflector::slugify($uniqId), $flatpageOneRow->slug
            , 'The slug isn\'t equals to what we expect');


        /**
         * Create a flatpage with the same name than the first we have create
         *
         * In this case, we expect :
         *  - slug (of flatpageTwoRow) != slug (of flatpageOneRow)
         */
        $flatpageTwoRow = Centurion_Db::getSingleton('cms/flatpage')->createRow(array(
            'title' => $uniqId,
            'flatpage_template_id' => 1,
        ));
        $flatpageTwoRow->save();

        // slug (of flatpageTwoRow) != slug (of flatpageOneRow)
        $this->assertNotEquals($flatpageOneRow->slug, $flatpageTwoRow->slug
            , 'The slug need to be different in this case because he is already taken by the first object');
    }

    /**
     *  Test slug when this one is composed of several columns
     */
    public function testSlugResultWithEmptyColumn()
    {
        $table = new Asset_Model_DbTable_WithMultiColumnsForSlug();

        /**
         * In this case the slug is composed of the title and the subtitle
         *
         * We except :
         *  - slug = Centurion_Inflector::slugify('Titre test') . '-' . Centurion_Inflector::slugify('Subtitle test')
         */
        $titleSubtitleRow = $table->createRow();
        $titleSubtitleRow->title = 'Title slug test';
        $titleSubtitleRow->subtitle = 'Subtitle slug test';
        $titleSubtitleRow->save();

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
