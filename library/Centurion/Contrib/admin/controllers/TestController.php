<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Admin
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Admin_TestController extends Centurion_Controller_Action
{
    public function indexAction()
    {
        $form = new TestForm();
        $form->setMethod('post')
             ->setLegend('Legend form')
             ->setDescription('My super hyper genial form description')
             ->setAction($this->_helper->url('index', 'test', 'admin'));

        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            if ($form->isValid($posts)) {

            } else {
                $form->populate($posts);
            }
        }

        $this->view->form = $form;
    }

    public function subAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();

        $form = new TestFormWithSubForm();
        $form->setMethod('post')
             ->setDescription('My super hyper genial form description')
             ->setAction($this->_helper->url('index', 'test', 'admin'));

        $form->removeDecorator('Fieldset');

        if ($this->getRequest()->isPost()) {
            $posts = $this->getRequest()->getPost();
            if ($form->isValid($posts)) {

            } else {
                $form->populate($posts);
            }
        }

        $this->view->form = $form;

        $this->render('index');
    }

    public function testAction()
    {
        $this->getHelper('viewRenderer')->setNoRender();
        $form = new My_Form_Registration();

        echo $form;
    }
}


class My_Form_Registration extends Centurion_Form
{
    public function init()
    {
        // Create user sub form: username and password
        $user = new Zend_Form_SubForm();
        $user->addElements(array(
            new Zend_Form_Element_Text('username', array(
                'required'   => true,
                'label'      => 'Username:',
                'filters'    => array('StringTrim', 'StringToLower'),
                'validators' => array(
                    'Alnum',
                    array('Regex',
                          false,
                          array('/^[a-z][a-z0-9]{2,}$/'))
                )
            )),

            new Zend_Form_Element_Password('password', array(
                'required'   => true,
                'label'      => 'Password:',
                'filters'    => array('StringTrim'),
                'validators' => array(
                    'NotEmpty',
                    array('StringLength', false, array(6))
                )
            )),
        ));

        // Create demographics sub form: given name, family name, and
        // location
        $demog = new Zend_Form_SubForm();
        $demog->addElements(array(
            new Zend_Form_Element_Text('givenName', array(
                'required'   => true,
                'label'      => 'Given (First) Name:',
                'filters'    => array('StringTrim'),
                'validators' => array(
                    array('Regex',
                          false,
                          array('/^[a-z][a-z0-9., \'-]{2,}$/i'))
                )
            )),

            new Zend_Form_Element_Text('familyName', array(
                'required'   => true,
                'label'      => 'Family (Last) Name:',
                'filters'    => array('StringTrim'),
                'validators' => array(
                    array('Regex',
                          false,
                          array('/^[a-z][a-z0-9., \'-]{2,}$/i'))
                )
            )),

            new Zend_Form_Element_Text('location', array(
                'required'   => true,
                'label'      => 'Your Location:',
                'filters'    => array('StringTrim'),
                'validators' => array(
                    array('StringLength', false, array(2))
                )
            )),
        ));

        // Create mailing lists sub form
        $listOptions = array(
            'none'        => 'No lists, please',
            'fw-general'  => 'Zend Framework General List',
            'fw-mvc'      => 'Zend Framework MVC List',
            'fw-auth'     => 'Zend Framwork Authentication and ACL List',
            'fw-services' => 'Zend Framework Web Services List',
        );
        $lists = new Zend_Form_SubForm();
        $lists->addElements(array(
            new Zend_Form_Element_MultiCheckbox('subscriptions', array(
                'disableTranslator' => true,
                'label'        =>
                    'Which lists would you like to subscribe to?',
                'multiOptions' => $listOptions,
                'required'     => true,
                'filters'      => array('StringTrim'),
                'validators'   => array(
                    array('InArray',
                          false,
                          array(array_keys($listOptions)))
                )
            )),
        ));

        // Attach sub forms to main form
        $this->addSubForms(array(
            'user'  => $user,
            'demog' => $demog,
            'lists' => $lists
        ));
    }

}

abstract class AbstractTestForm extends Centurion_Form_Composite
{
    public function init()
    {
        parent::init();
        $this->addElement('text', 'username', array(
            'label'         =>  'Username',
            'description'   =>  'You can change this any time after you join'
        ));
        $this->addElement('password', 'password', array(
            'label'         =>  'Password',
            'required'      =>  true
        ));

        $this->addElement('text', 'email', array(
            'label'         =>  'Email',
        ));

        $this->addElement('text', 'created_at', array(
            'label'         =>  'Creation date',
            'class'         =>  'datepicker'
        ));

        $this->addElement('radio', 'sex', array(
            'label'         =>  'Sex',
            'multioptions'       =>  array(
                'M'     =>  'Male',
                'F'     =>  'Female'
            ),
            'separator' =>  ' &nbsp; '
        ));

        $this->addElement('select', 'country', array(
            'label'         =>  'Country',
            'multioptions'       =>  array(
                'fr'     =>  'France',
                'en'     =>  'England'
            )
        ));

        $this->addElement('textarea', 'biography', array('label' => 'Biography'));

        $this->addElement('textarea', 'text', array(
            'label'         =>  'Example with a very very long label like a question or a Rich Text Editor:',
        ));

        $this->addElement('checkbox', 'cgu', array(
            'label'         =>  'I have read & agree the Centurion Terms of Service',
            'class'         =>  'checkbox'
        ));

        $this->addElement('checkbox', 'newsletter', array(
            'label'         =>  'I want to receive newsletter',
            'class'         =>  'checkbox'
        ));

        $this->addElement('checkbox', 'option1', array(
            'label'         =>  'Option1 with description',
            'class'         =>  'checkbox'
        ));

        $this->addElement('checkbox', 'option2', array(
            'label'         =>  'Option2 with description',
            'class'         =>  'checkbox'
        ));

        $this->addDisplayGroup(array('option1', 'option2'), 'options');
        $this->getDisplayGroup('options')->setAttrib('label', 'Options');

        $this->addDisplayGroup(array('cgu', 'newsletter'), 'checkbox');
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->getElement('text')
             ->setAttrib('class', 'large rte');

        $this->getElement('text')->getDecorator('Label')
                                 ->setOption('tagClass', 'border-top large');

        $this->getElement('text')->getDecorator('ElementContainer')->setOption('class', 'large');

        $this->getDisplayGroup('checkbox')->setAttrib('class', 'large nicyless border-top');

        $this->getDisplayGroup('checkbox')->getDecorator('HtmlTag')
                                          ->setOption('class', 'checklist');

        $this->getDisplayGroup('options')->setAttrib('class', 'nicyless border-top');

        $this->getDisplayGroup('options')->getDecorator('DtDdWrapperEv')
                                         ->setOption('class', 'border-top');

        $this->getDisplayGroup('options')->getDecorator('HtmlTag')
                                         ->setOption('class', 'checklist');

        $this->getElement('country')
             ->setValue('fr');

        $this->getElement('biography')->getDecorator('Label')
                                      ->setOption('tagClass', 'border-top');

        $this->getElement('biography')->getDecorator('ElementContainer')
                                      ->setOption('class', 'border-top');

        $this->getElement('sex')
             ->getDecorator('ElementContainer')->setOption('class', 'nicyless');

        $this->getElement('sex')->setAttrib('class', 'nicyless radio');
    }
}

class TestFormRandom extends Centurion_Form_Composite
{
    public function init()
    {
        parent::init();

        $this->setLegend('Form1');

        $this->addElement('text', 'test1', array(
            'label'         =>  'Test1',
        ));

        $this->addElement('text', 'test2', array(
            'label'         =>  'Test2',
        ));

        $this->addElement('text', 'test3', array(
            'label'         =>  'Test3',
        ));
    }
}


class TestSubForm extends AbstractTestForm
{
    public function init()
    {
        parent::init();

        $this->setLegend('Form2');
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();


    }
}

class TestForm extends AbstractTestForm
{
    public function init()
    {
        parent::init();

        $this->addElement('submit', '_save', array(
            'label'         =>  'Save',
            'class'         =>  'save'
        ));

        $this->addElement('submit', '_continue', array(
            'label'         =>  'Save and continue',
            'class'         =>  'continue'
        ));

        $this->addElement('submit', '_addanother', array(
            'label'         =>  'Save and add another',
            'class'         =>  'addanother'
        ));

        $this->addDisplayGroup(array('_save', '_continue', '_addanother'), 'submit');
    }

    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->getDisplayGroup('submit')->setAttrib('class', 'submit large nicyless border-top');

        $this->getElement('_save')->getDecorator('Label')
                                  ->setOption('tagClass', 'hidden');

        $this->getElement('_continue')->getDecorator('Label')
                                      ->setOption('tagClass', 'hidden');

        $this->getElement('_addanother')->getDecorator('Label')
                                        ->setOption('tagClass', 'hidden');
    }
}

class TestFormWithSubForm extends TestForm
{
    public function init()
    {
        parent::init();

        $form1 = new TestFormRandom();

        $form2 = new TestSubForm();

        $this->addSubForms(array(
            'form1' =>  $form1,
            'form2' =>  $form2
        ));

        $this->moveElement('submit', 'after', 'form2');
    }
}
