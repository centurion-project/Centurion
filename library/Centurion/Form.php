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
 * @package     Centurion_Form
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 */
class Centurion_Form extends Zend_Form implements Centurion_Traits_Traitsable
{
    const FIRST = 'first';
    const LAST = 'last';
    const BEFORE = 'before';
    const AFTER = 'after';

    /**
     * Clear the form?
     *
     * @var boolean
     */
    protected $_clear = false;

    protected $_defaultDisplayGroupClass = 'Centurion_Form_DisplayGroup';

    /**
     * Default element decorators.
     *
     * @var array
     */
    public $defaultElementDecorators = array(
        array('ViewScript', array('viewScript' => 'centurion/form/_element.phtml'))
    );

    /**
     * Default error element decorators.
     *
     * @var array
     */
    public $defaultErrorElementDecorators = array(
        array('ViewScript', array('viewScript' => 'centurion/form/_error-element.phtml'))
    );

    /**
     * Default form decorators.
     *
     * @var array
     */
    public $defaultFormDecorators = array(
        array('FormElements', array()),
        array('ViewScript', array('viewScript' => 'centurion/form/_form.phtml', 'class' => 'form', 'placement' => ''))
    );

    /**
     * Default subform decorators.
     *
     * @var array
     */
    public $defaultSubFormDecorators = array(
        array('FormElements', array())
//        array('ViewScript', array('viewScript' => 'centurion/form/_sub-form.phtml'))
    );

    /**
     * Default displayGroup decorators.
     *
     * @var array
     */
    public $defaultDisplayGroupDecorators = array(
        array('FormElements', array()),
        array('ViewScript', array('viewScript' => 'centurion/form/_dislpay-group.phtml', 'placement' => ''))
    );

    /**
     * Default no element decorator for file element, etc.
     *
     * @var array
     */
    public $defaultNoElementDecorator = array(
        array('ViewScript', array('viewScript' => 'centurion/form/_hidden.phtml'))
    );

    protected $_toolbarElems = array();

    protected $_traitQueue;

    public function getTraitQueue()
    {
        if (null == $this->_traitQueue)
            $this->_traitQueue = new Centurion_Traits_Queue();

        return $this->_traitQueue;
    }

    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @return void
     */
    public function __construct($options = null)
    {
        Centurion_Traits_Common::initTraits($this);

        parent::__construct($options);
    }

    public function __get($column)
    {
        return parent::__get($column);
    }


    public function __set($column, $value)
    {
        return parent::__set($column, $value);
    }

    public function __call($method, $args)
    {
        try {
            parent::__call($method, $args);
        } catch(Zend_Form_Exception $e) {

            list($found, $retVal) = Centurion_Traits_Common::checkTraitOverload($this, $method, $args);

            if ($found)
                return $retVal;

            throw $e;
        }
    }

    public function getToolbar()
    {
        Centurion_Signal::factory('on_form_get_toolbar')->send($this);
        return $this->_toolbarElems;
    }

    public function getFormId()
    {
        //Try to generate uniq ID
        return md5(get_class($this) . $this->getAttrib('formId'));
    }

    /**
     * @param $request
     * @return bool return true if current form has been posted in current request.
     */
    public function hasBeenPost($request)
    {
        if ($request->isPost()) {
            if ($this->getFormId() === $request->getParam('formId'))
                return true;
        }
        return false;
    }

    /**
     * @return bool return true if the current form have already been cleared
     */
    public function isClear()
    {
        return $this->_clear;
    }
    
    public function isAllowedContext($context, $resource = null)
    {
        return in_array($context, iterator_to_array($this->_traitQueue), true);
    }

    public function delegateGet($context, $column)
    {
        if (!$this->isAllowedContext($context, $column))
            throw new Centurion_Form_Exception(sprintf('Undefined property %s', $column));

        return $this->$column;
    }

    public function delegateSet($context, $column, $value)
    {
        if (!$this->isAllowedContext($context, $column))
            throw new Centurion_Form_Exception(sprintf('Undefined property %s', $column));

        return $this->$column = $value;
    }

    public function delegateCall($context, $method, $args = array())
    {
        if (!$this->isAllowedContext($context, $method))
            throw new Centurion_Form_Exception(sprintf('Undefined method %s', $method));

        return call_user_func_array(array($this, $method), $args);
    }

    /**
     * Populate form
     *
     * Proxies to {@link setDefaults()}
     *
     * @param  array $values
     * @return Zend_Form
     */
    public function populate(array $values)
    {
        Centurion_Signal::factory('pre_populate')->send($this, array($values));

        return parent::populate($values);
    }

    /**
     * Retrieve plugin loader for given type
     *
     * $type may be one of:
     * - decorator
     * - element
     *
     * If a plugin loader does not exist for the given type, defaults are
     * created.
     *
     * @param  string $type
     * @return Zend_Loader_PluginLoader_Interface
     */
    public function getPluginLoader($type = null)
    {
        $type = strtoupper($type);
        if (! isset($this->_loaders[$type])) {
            switch ($type) {
                case self::DECORATOR:
                    $prefixSegment = 'Form_Decorator';
                    $pathSegment = 'Form/Decorator';
                    break;
                case self::ELEMENT:
                    $prefixSegment = 'Form_Element';
                    $pathSegment = 'Form/Element';
                    break;
                default:
                    throw new Centurion_Form_Exception(sprintf('Invalid type "%s" provided to getPluginLoader()', $type));
            }
            $this->_loaders[$type] = new Centurion_Loader_PluginLoader(array(
                'Centurion_' . $prefixSegment . '_' => 'Centurion/' . $pathSegment . '/',
                'Zend_' . $prefixSegment . '_'      => 'Zend/' . $pathSegment . '/'
            ));
        }
        return $this->_loaders[$type];
    }

    /**
     * @return array The order of element during the render process
     */
    public function getElementOrder()
    {
        return $this->_order;
    }

    public function getValuesForUrl()
    {
        return $this->_flatArray($this->getValues());
    }

    //TODO: find a real name for this function
    protected function _flatArray($array, $prefixKey = null)
    {
        $newValues = array();
        foreach($array as $key => $val) {

            $newPrefix = $key;
            if ($prefixKey !== null) {
                $newPrefix = $prefixKey.'[' . $newPrefix . ']';
            }

            if (is_string($val)) {
                $newValues[$newPrefix] = $val;
            } else if (is_array($val)) {
                $newValues = array_merge($newValues, $this->_flatArray($val, $newPrefix));
            }
        }

        return $newValues;
    }

    /**
     * Move the position of an element with a pivot element.
     *
     * @param   string          $elementName
     * @param   string          $action
     * @param   string          $pivotElementName OPTIONAL
     * @return  $this
     */
    public function moveElement($elementName, $action, $pivotElementName = null)
    {
        $elementName = (string) $elementName;
        if (!array_key_exists($elementName, $this->_order)) {
            throw new Centurion_Form_Exception(sprintf('Element "%s" does not exist.', $elementName));
        }

        unset($this->_order[$elementName]);

        if (null !== $pivotElementName) {
            $pivotElementName = (string) $pivotElementName;

            if (!array_key_exists($pivotElementName, $this->_order)) {
                throw new Centurion_Form_Exception(sprintf('$pivotElementName "%s" does not exist.', $pivotElementName));
            }

            $pivotPosition = array_search($pivotElementName, array_keys($this->_order));
        }

        switch ($action) {
            case self::FIRST:
                $this->_order = array_merge(array($elementName => null), $this->_order);
                break;
            case self::LAST:
                $this->_order = array_merge($this->_order, array($elementName => count($this->_order)));
                break;
            case self::BEFORE:
                if (null === $pivotElementName) {
                    throw new Centurion_Form_Exception(sprintf('Unable to move element "%s" without a relative $element.', $elementName));
                }

                $pivot = $pivotPosition ? $pivotPosition - 1 : 0;

                $this->_order = array_merge(array_slice($this->_order, 0, $pivot), array($elementName   => null),
                                            array_slice($this->_order, $pivot));
                break;
            case self::AFTER:
                if (null === $pivotElementName) {
                    throw new Centurion_Form_Exception(sprintf('Unable to move element "%s" without a relative element.', $elementName));
                }

                $this->_order = array_merge(array_slice($this->_order, 0, $pivotPosition + 1), array($elementName   => null),
                                            array_slice($this->_order, $pivotPosition + 1));
                break;
            default:
                throw new Centurion_Form_Exception(sprintf('Unknown move operation for element "%s".', $elementName));
        }

        return $this;
    }

    /**
     * Clean form decorators.
     *
     * @return $this
     */
    public function cleanForm()
    {
        if (!$this->_clear){
            $this->_clear = true;

            $this->setDecorators($this->defaultFormDecorators)
                 ->cleanElements()
                 ->cleanSubForms()
                 ->cleanDisplayGroups();
        }

        $this->postCleanForm();

        return $this;
    }

    /**
     * Change the state of setDisableTranslator on each elements and subforms of a form
     *
     * @param string
     * @return void
     */
    public function setRecursivlyDisableTranslator($status)
    {
        foreach ($this->getElements() as $element) {
            $element->setDisableTranslator($status);
        }
        foreach ($this->getSubForms() as $subform) {
            $subform->setDisableTranslator($status);
        }
    }

    /**
     * Enable translation before isValid and disable it after
     *
     * @param array
     * @return bool
     */
    public function isValid($values)
    {
        Centurion_Signal::factory('on_form_validation')->send($this, array($values));
        $this->setRecursivlyDisableTranslator(false);
        $valid = parent::isValid($values);
        $this->setRecursivlyDisableTranslator(true);
        
        return $valid;
    }
    
    /**
     * Clean displayGroups.
     *
     * @return $this
     */
    public function cleanDisplayGroups()
    {
        $this->setDisplayGroupDecorators($this->defaultDisplayGroupDecorators);

        return $this;
    }

    /**
     * Add a new element
     *
     * $element may be either a string element type, or an object of type
     * Zend_Form_Element. If a string element type is provided, $name must be
     * provided, and $options may be optionally provided for configuring the
     * element.
     *
     * If a Zend_Form_Element is provided, $name may be optionally provided,
     * and any provided $options will be ignored.
     *
     * @param  string|Zend_Form_Element $element
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return $this
     */
    public function addElement($element, $name = null, $options = null)
    {
        if (is_object($element)) {
            if ($element instanceof Zend_Form_Element) {
                $element->setDisableTranslator(true);
            }
        } else {
            if (null === $options) {
                $options = array();
            }
            $options['disableTranslator'] = true;
        }
        parent::addElement($element, $name, $options);

        if ($this->_clear) {
            if (null !== $name && (null === $options || !isset($options['decorators']))) {
                $this->cleanElement($this->_elements[$name]);
            }
        }

        return $this;
    }

    /**
     * @param Zend_Form $form
     * @param string $name
     * @param null $order
     * @return $this
     */
    public function addSubForm(Zend_Form $form, $name, $order = null)
    {
        parent::addSubForm($form, $name, $order);

        if ($this->_clear) {
            if (null !== $name && !empty($name) && !$form->isClear())
                $this->cleanSubForm($this->getSubForm($name));
        }

        return $this;
    }
    
    /**
     * Remove given elements or displayGroups from $_order var
     * The elements still are in the form (and so will be used for isValid() ...)
     * But will not be dislpay as an element of the form, but in displayGroup or ...
     *
     * @see Zend_Form::addDisplayGroup()
     * @param array[int]Zend_Form_Element_Abstract $elements
     */
    public function _removeFromOrder($elements)
    {
        $group = array();
        foreach ($elements as $element) {
            if (isset($this->_elements[$element])) {
                $add = $this->getElement($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
                    $group[] = $add;
                }
            }
            if (isset($this->_displayGroups[$element])) {
                $add = $this->getDisplayGroup($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
                    $group[] = $add;
                }
            }
            if (isset($this->_subForms[$element])) {
                $add = $this->getSubForm($element);
                if (null !== $add) {
                    unset($this->_order[$element]);
                    $group[] = $add;
                }
            }
        }

        return $group;
    }

    /**
     * Add a display group
     *
     * Groups named elements for display purposes.
     *
     * If a referenced element does not yet exist in the form, it is omitted.
     *
     * @param  array $elements
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return $this
     * @throws Zend_Form_Exception if no valid elements provided
     */
    public function addDisplayGroup(array $elements, $name, $options = null)
    {
        $group = $this->_removeFromOrder($elements);

        if (empty($group)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('No valid elements specified for display group');
        }

        $name = (string) $name;

        if (is_array($options)) {
            $options['elements'] = $group;
        } elseif ($options instanceof Zend_Config) {
            $options = $options->toArray();
            $options['elements'] = $group;
        } else {
            $options = array('elements' => $group);
        }

        if (isset($options['displayGroupClass'])) {
            $class = $options['displayGroupClass'];
            unset($options['displayGroupClass']);
        } else {
            $class = $this->getDefaultDisplayGroupClass();
        }

        if (!class_exists($class)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($class);
        }
        $this->_displayGroups[$name] = new $class(
            $name,
            $this->getPluginLoader(self::DECORATOR),
            $options
        );

        if (!empty($this->_displayGroupPrefixPaths)) {
            $this->_displayGroups[$name]->addPrefixPaths($this->_displayGroupPrefixPaths);
        }

        $this->_order[$name] = $this->_displayGroups[$name]->getOrder();
        $this->_orderUpdated = true;

        if (!isset($options['decorators'])) {
            $this->_displayGroups[$name]->setDecorators($this->defaultDisplayGroupDecorators);
        }

        return $this;
    }

    /**
     * Add element in a displayGroup if the displaygroup doesn't exists create it
     *
     * @param $elements
     * @param $displayGroupName
     */
    public function addInDisplayGroup($elements, $displayGroupName, $options = null)
    {
        if (!is_array($elements)) {
            $elements = (array) $elements;
        }

        if (!isset($this->_displayGroups[$displayGroupName])) {
            $this->addDisplayGroup($elements, $displayGroupName, $options);
        } else {
            $group = $this->_removeFromOrder($elements);
            $this->getDisplayGroup($displayGroupName)->addElements($group);
            if (null !== $options)
                $this->getDisplayGroup($displayGroupName)->setOptions($options);
        }
    }

    /**
     * Clean decorators for an element.
     *
     * @param Zend_Form_Element $element
     * @return $this
     */
    public function cleanElement(Zend_Form_Element $element)
    {
        if (!$element instanceof Zend_Form_Element_File && !$element instanceof Zend_Form_Element_Captcha) {
            $decorators = $this->defaultNoElementDecorator;

            if (!($element instanceof Zend_Form_Element_Hidden)) {
                $decorators = $this->defaultElementDecorators;
            }

            if ($element instanceof Zend_Form_Element_Checkbox) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-checkbox');
            }
            if ($element instanceof Zend_Form_Element_MultiCheckbox) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-checkbox');
            }
            if ($element instanceof Zend_Form_Element_Radio) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-radio');
            }
            if ($element instanceof Zend_Form_Element_Text || $element instanceof Zend_Form_Element_Password) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-text');
            }

            if ($element instanceof Zend_Form_Element_Multiselect) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-multiselect');
            } elseif ($element instanceof Zend_Form_Element_Select) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-select');
            }

            if ($element instanceof Zend_Form_Element_Textarea) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-textarea');
            }

            if ($element instanceof Zend_Form_Element_File) {
                $element->setAttrib('class', $element->getAttrib('class') . ' field-file');
            }

            $element->setDecorators($decorators);

            if ($element instanceof Zend_Form_Element_Button) {
                $element->setDecorators(array(array('ViewScript', array('viewScript' => 'centurion/form/_button.phtml'))));
            }
        }

        return $this;
    }

    /**
     * Clean all elements.
     *
     * @return $this
     */
    public function cleanElements()
    {
        foreach ($this->getElements() as $key => $element) {
            $this->cleanElement($element);
        }

        return $this;
    }

    /**
     * @param Centurion_Form $form
     * @return $this
     */
    public function cleanSubForm(Centurion_Form $form)
    {
        $form->setAttrib('class', 'subform')
             ->setIsArray(true)
             ->cleanForm()
             ->setDecorators($this->defaultSubFormDecorators)
             ->postCleanAsSubform();


        $form->removeElement('_XSRF');

        return $this;
    }


    /**
     * Clean all subforms
     *
     * @return $this
     */
    public function cleanSubForms()
    {
        foreach ($this->getSubForms() as $key => $form) {
            $this->cleanSubForm($form);
        }

        return $this;
    }

    /**
     * Render error for a form.
     *
     * @return $this
     */
    public function renderError()
    {
        foreach ($this->getElements() as $key => $element) {
            if ($element instanceof Zend_Form_Element_File
                || $element instanceof Zend_Form_Element_Captcha
                || !$element->hasErrors()) {
                continue;
            }

            //$element->setDecorators($this->defaultErrorElementDecorators);
        }

        return $this;
    }

    /**
     * Create an element
     *
     * Acts as a factory for creating elements. Elements created with this
     * method will not be attached to the form, but will contain element
     * settings as specified in the form object (including plugin loader
     * prefix paths, default decorators, etc.).
     *
     * @param  string $type
     * @param  string $name
     * @param  array|Zend_Config $options
     * @return Zend_Form_Element
     */
    public function createElement($type, $name, $options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (null === $options)  {
            $options = array();
        }

        if (!isset($options['disableTranslator']))
            $options['disableTranslator'] = true;

        return parent::createElement($type, $name, $options);
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null == $this->getElement('formId')){
            $this->addElement('hidden', 'formId', array('value' => $this->getFormId()));

            //fix #6328 (hidden element bordered in dom)
            $this->getElement('formId')->setDecorators(array('ViewHelper'));
        }

        Centurion_Signal::factory('on_form_rendering')->send($this);
        
        if ($this->_clear) {
            $this->renderError();
            foreach ($this->getSubForms() as $key => $form) {
                $form->renderError();
            }
        }
        $this->setDisableTranslator(true);
        return parent::render($view);
    }

    /**
     * Remove an element from the $this->_order array.
     *
     * @param string $name
     */
    public function removeOrder($name)
    {
        if (isset($this->_order[$name])) {
            unset($this->_order[$name]);
        }
    }

    /**
     * Translate a messageId using the translator.
     *
     * @param string $messageId
     * @return string The messageId translated if a translator exists otherwise the default value of the parameter
     */
    protected function _translate($messageId)
    {
        if (null === $this->_translator) {
            $translator = self::getDefaultTranslator();
        } else {
            $translator = $this->_translator;
        }

        if (null !== $translator) {
            $messageId = $translator->translate($messageId);

            if (func_num_args() === 1) {
                return $messageId;
            }

            $args = func_get_args();
            array_shift($args);

            return vsprintf($messageId, $args);
        }

        return $messageId;
    }

    public function postCleanForm()
    {
    }

    public function postCleanAsSubform()
    {
    }
}
