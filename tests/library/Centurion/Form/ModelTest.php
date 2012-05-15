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
        
        $row->published_at = $date->get(Centurion_Date::MYSQL_DATETIME);
        $form->setInstance($row);

        $expected = $date->get('dd/MM/yy');
        $value = $form->getElement('published_at')->getValue();
        $this->assertEquals($expected, $value);
        
        $values = $form->processValues($form->getValues());
        $this->assertEquals($date->get(Centurion_Date::MYSQL_DATETIME), $values['published_at']);

        //With Time
        $date = new Zend_Date('1/12/2009 11:32', 'dd/MM/YYYY HH:mm');
        $form = new Cms_Form_Model_Flatpage();
        $form->setDateFormat('dd/MM/yy', 'hh:mm');
        
        $row = Centurion_Db::getSingleton('cms/flatpage')->createRow();
        
        $row->published_at = $date->get(Centurion_Date::MYSQL_DATETIME);
        $form->setInstance($row);
        
        $expected = $date->get('dd/MM/yy hh:mm');
        $value = $form->getElement('published_at')->getValue();
        $this->assertEquals($expected, $value);
        
        $values = $form->processValues($form->getValues());
        $this->assertEquals($date->get(Centurion_Date::MYSQL_DATETIME), $values['published_at']);
    }

    /**
     * Check if many to many works when we save a form
     * @TODO : Ajouter la vérification de chaque groupe
     * @covers Centurion_Form_Model_Abstract::_saveManyDependentTables
     */
    public function testSaveManyToMany()
    {
        $groupTable = Centurion_Db::getSingleton('auth/group');

        $groupTable->getOrCreate(array('id' => 1, 'name' => 'Administrator'));
        $groupTable->getOrCreate(array('id' => 2, 'name' => 'Webmaster'));

        $groupArray = array('1', '2');

        $belongRowset = Centurion_Db::getSingleton('auth/belong')->findByUser_id(1)->delete();

        $userForm = new Auth_Form_Model_User();
        $userRow = Centurion_Db::getSingleton('auth/user')->findOneById(1);

        $userForm->setInstance($userRow);

        $userForm->getElement('groups')->setValue($groupArray);

        $this->assertEquals($groupArray, $userForm->getElement('groups')->getValue(), 'Avant enregistrement');

        $userRow = $userForm->saveInstance();

        $belongRowset = Centurion_Db::getSingleton('auth/belong')->findByUser_id(1);

        $this->assertEquals(count($groupArray), count($belongRowset), 'Après enregistrement');

        //$this->markTestIncomplete('Ajouter la vérification de chaque groupe');
    }
}
