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
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
class Admin_View_Helper_GridForm extends Zend_View_Helper_Abstract
{
    static protected $_firstTime = true;

    public function __call($method, array $args)
    {
        $method = strtolower($method);

        if (!strncmp($method, 'add', 3)) {
            $type = substr($method, 3, strlen($method));
            array_unshift($args, $type);
            call_user_func_array(array($this, 'add'), $args);
        }
    }

    public function add($type, $form, array $data)
    {
        if (self::$_firstTime) {
            $this->view->placeholder('section-class')->set('tpl-form');
            self::$_firstTime = false;
        }

        if (!isset($data['elements']) || empty($data['elements'])) {
            throw new Centurion_Exception('No element provided');
        }

        $data['elements'] = (array) $data['elements'];

        if ('header' === $type) {
            $form->addDisplayGroup($data['elements'], '_header', array('class' => 'form-header'));
            $form->moveElement('_header', Centurion_Form::FIRST);

            foreach ($data['elements'] as $element) {
                $element = $form->getElement($element);
                if ($element) {
                    $element->setAttrib('large', true);
                    $element->setAttrib('noLabel', true);
                    $element->setAttrib('class', $element->getAttrib('class') . ' field-text-big');
                }
            }
        }

        if ($type === 'main') {
            $this->_makeDisplayGroup($form, $data, '_main');
        }

        if ($type === 'aside') {
            $this->_makeDisplayGroup($form, $data, '_aside');
        }
    }

    public function makeOptionCheckbox(Zend_Form $form, $elements, $label = 'Options', $displayGroupName = 'options')
    {
        foreach ($elements as $element) {
            $form->getElement($element)->setAttrib('noFormItem', true);
        }

        $form->addDisplayGroup($elements, $displayGroupName);
        $form->getDisplayGroup($displayGroupName)->setAttrib('class', 'form-checkbox')
                                                ->setAttrib('formItem', 'true')
                                                ->setLegend($label);
        return $form;
    }

    /**
     * @return $this
     */
    public function gridForm()
    {
        return $this;
    }

    protected function _makeDisplayGroup(Centurion_Form $form, $data, $class)
    {
        if (isset($data['noFieldset']) && $data['noFieldset'] === true) {
            $form->addInDisplayGroup($data['elements'] , $class, array('class' => 'form-'.substr($class, 1)));
            
            foreach ($data['elements'] as $key => $element) {
                if (null !== ($element = $form->getElement($element))) {
                    $element->setLabel(null);
                    $element->removeDecorator('label');
                }
            }

            return true;
        }
        
        if (!isset($data['label'])) {
            $name = uniqid();
        } else {
            $name = Centurion_Inflector::slugify($data['label']);
        }
        $name = $name . '_group';
        $form->addDisplayGroup($data['elements'], $name, array('class' => 'form-group'));
        $displayGroup = $form->getDisplayGroup($name);
        
        if (isset($data['label']) && is_string($data['label'])) {
            $displayGroup->setLegend($data['label']);
            if (isset($data['description'])) {
                $displayGroup->setDescription($data['description']);
            }
        } else {
            $displayGroup->setDescription('&nbsp;');
        }

        $form->addInDisplayGroup(array($name), $class, array('class' => 'form-'.substr($class, 1)));
    }
}
