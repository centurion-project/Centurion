<?php
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

    public function moveElement($elementName, $action, $pivotElementName = null)
    {
        // @todo : ré-implement ordering management inside display group
    }
}