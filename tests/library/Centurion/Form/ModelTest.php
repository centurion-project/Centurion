<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Form_ModelTest extends PHPUnit_Framework_TestCase
{
    public function testDateFormatForDatePicker()
    {
        //Without time
        $date = new Zend_Date('1/12/2009', 'dd/MM/YYYY');
        $form = new Cms_Form_Model_Flatpage();
        $form->setDateFormat('dd/MM/yy');

        $row = Centurion_Db::getSingleton('cms/flatpage')->createRow();
        
        $row->published_at = $date->get('YYYY-MM-dd HH:mm:ss');
        $form->setInstance($row);

        $expected = $date->get('dd/MM/yy');
        $value = $form->getElement('published_at')->getValue();
        $this->assertEquals($expected, $value);
        
        $values = $form->processValues($form->getValues());
        $this->assertEquals($date->get('YYYY-MM-dd HH:mm:ss'), $values['published_at']);

        //With Time
        $date = new Zend_Date('1/12/2009 11:32', 'dd/MM/YYYY HH:mm');
        $form = new Cms_Form_Model_Flatpage();
        $form->setDateFormat('dd/MM/yy', 'hh:mm');
        
        $row = Centurion_Db::getSingleton('cms/flatpage')->createRow();
        
        $row->published_at = $date->get('YYYY-MM-dd HH:mm:ss');
        $form->setInstance($row);
        
        $expected = $date->get('dd/MM/yy hh:mm');
        $value = $form->getElement('published_at')->getValue();
        $this->assertEquals($expected, $value);
        
        $values = $form->processValues($form->getValues());
        $this->assertEquals($date->get('YYYY-MM-dd HH:mm:ss'), $values['published_at']);
    }
}
