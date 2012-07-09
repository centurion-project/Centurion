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
 * @package     Centurion_Form
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Locale
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * Display groups
     * @var array
     */
    protected $_displayGroups = array();
    protected $_orders = array();

    /**
     * Stack of subforms
     * @var array
     */
    protected $_subForms = array();

    /**
     * Add multiple elements at once
     *
     * @param  array $elements
     * @return Zend_Form_DisplayGroup
     * @throws Zend_Form_Exception if any element is not a Zend_Form_Element
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            if ($element instanceof Zend_Form_Element) {
                $this->addElement($element);
            } else if ($element instanceof Zend_Form_DisplayGroup) {
                $this->addDisplayGroup($element);
            } else if ($element instanceof Zend_Form) {
                $this->addSubForm($element);
            } else {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('elements passed via array to addElements() must be Zend_Form_Elements or Zend_Form_DisplayGroup only');
            }
        }
        return $this;
    }

    public function addElement(Zend_Form_Element $element)
    {
        parent::addElement($element);
        $this->_orders[$element->getName()] = count($this->_order);
        return $this;

    }
    /**
     * Add element to stack
     *
     * @param  Zend_Form_Element $element
     * @return Centurion_Form_DisplayGroup
     */
    public function addDisplayGroup(Zend_Form_DisplayGroup $element)
    {
        $this->_displayGroups[$element->getName()] = $element;
        $this->_orders[$element->getName()] = count($this->_order);
        $this->_groupUpdated = true;
        return $this;
    }


    /**
     * add subform element to stack
     *
     * @param Zend_Form $form
     * @return Centurion_Form_DisplayGroup
     */
    public function addSubForm(Zend_Form $form) {
        $this->_subForms[$form->getName()] = $form;
        $this->_orders[$form->getName()] = count($this->_order);
        $this->_groupUpdated = true;
        return $this;
    }

    /**
     * Count of elements/subforms that are iterable
     *
     * @return int
     */
    public function count()
    {
        return count($this->getElements());
    }

    public function getElement($name)
    {
        $return = parent::getElement($name);

        if (null !== $return) {
            return $return;
        }

        $elements = $this->getElements();

        if (isset($elements[$name])) {
            return $elements[$name];
        }

        if (isset($this->_subForms[$name])) {
            return $this->_subForms[$name];
        }

        return null;
    }

    /**
     * Retrieve elements
     * @return array
     */
    public function getElements()
    {
        return array_merge($this->_orders, $this->_elements, $this->_displayGroups, $this->_subForms);
    }

    /**
     * Move the position of an element with a pivot element.
     *
     * @param   string          $elementName
     * @param   string          $action
     * @param   string          $pivotElementName OPTIONAL
     * @return  Centurion_Form
     */
    public function moveElement($elementName, $action, $pivotElementName = null)
    {
        $elementName = (string) $elementName;
        if (!array_key_exists($elementName, $this->_orders)) {
            throw new Centurion_Form_Exception(sprintf('Element "%s" does not exist.', $elementName));
        }

        unset($this->_orders[$elementName]);

        if (null !== $pivotElementName) {
            $pivotElementName = (string) $pivotElementName;

            if (!array_key_exists($pivotElementName, $this->_orders)) {
                throw new Centurion_Form_Exception(sprintf('$pivotElementName "%s" does not exist.', $pivotElementName));
            }

            $pivotPosition = array_search($pivotElementName, array_keys($this->_orders));
        }

        switch ($action) {
            case Centurion_Form::FIRST:
                $this->_orders = array_merge(array($elementName => null), $this->_orders);
                break;
            case Centurion_Form::LAST:
                $this->_orders = array_merge($this->_orders, array($elementName => count($this->_orders)));
                break;
            case Centurion_Form::BEFORE:
                if (null === $pivotElementName) {
                    throw new Centurion_Form_Exception(sprintf('Unable to move element "%s" without a relative $element.', $elementName));
                }

                $pivot = $pivotPosition ? $pivotPosition - 1 : 0;

                $this->_order = array_merge(array_slice($this->_orders, 0, $pivot), array($elementName   => null),
                    array_slice($this->_orders, $pivot));
                break;
            case Centurion_Form::AFTER:
                if (null === $pivotElementName) {
                    throw new Centurion_Form_Exception(sprintf('Unable to move element "%s" without a relative element.', $elementName));
                }

                $this->_order = array_merge(array_slice($this->_orders, 0, $pivotPosition + 1), array($elementName   => null),
                    array_slice($this->_orders, $pivotPosition + 1));
                break;
            default:
                throw new Centurion_Form_Exception(sprintf('Unknown move operation for element "%s".', $elementName));
        }

        return $this;
    }
}
