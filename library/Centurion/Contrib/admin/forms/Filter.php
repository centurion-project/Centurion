<?php
class Admin_Form_Filter extends Centurion_Form
{
    protected $_filters = null;

    public $defaultFormDecorators = array(
            array('Description', array('tag' => 'h2')),
            'FormElements',
            array('HtmlTag', array('tag' => 'div', 'class' => 'box grid-filter')),
            array('form', array('id' => 'grid-filter-form')),
        );

     public $defaultElementDecorators = array(
            array('ViewHelper', array('class' => 'test')),
            array('HtmlTag', array('tag' => 'div', 'class' => 'in')),
            array('Label', array('tag' => 'h3', 'optionalPrefix' => '<span class="ui-icon ui-icon-triangle-1-s"></span>', 'escape' => false, 'separator' => '')),
            array('Description', array('tag' => 'p', 'class' => 'description')),
            array('Errors', array('tag' => 'ul', 'class' => 'errors')),
            array(array('ElementContainer' => 'HtmlTag'), array('tag' => 'div', 'class' => 'filter')),
        );

     public $defaultSubFormDecorators = array(
            array('Description', array('tag' => 'h3')),
            'FormElements',
        );

     public $defaultDisplayGroupDecorators = array(
            array('Description', array('tag' => '','class' => '')),
            array('HtmlTag', array('tag' => 'span', 'class' => 'ui-icon ui-icon-triangle-1-s', 'placement' => 'PREPEND')),
            array(array('DescriptionContainer' => 'HtmlTag'), array('tag' => 'h3')),
            'FormElements',
            array(array('DisplayContainer' => 'HtmlTag'), array('tag' => 'div', 'class' => 'filter')),
        );

    public $defaultElementDecoratorsInDisplayGroup = array(
            'ViewHelper',
            array('Label', array('placement' => null)),
            array('HtmlTag', array('tag' => 'div', 'class' => 'in')),

            array(array('ElementContainer' => 'HtmlTag'), array('tag' => 'div')),
        );
    public function __construct($options = null)
    {
        parent::__construct($options);
    }

    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'grid-filter-form');
    }

    public function setFilters($filters)
    {
        $this->cleanForm();

        foreach ($filters as $key => &$filterData) {
            if (!is_array($filterData))
                $filterData = array('label' => $filterData);
                
            if (isset($filterData['label']))
                $label = $filterData['label'];
            else
                $label = '';

            $checkboxType = 'multiCheckbox';
            $element = null;

            if (!isset($filterData['type']))
                $filterData['type'] = Centurion_Controller_CRUD::FILTER_TYPE_TEXT;

            if (!isset($filterData['column']))
                $filterData['column'] = $key;

            if (!isset($filterData['behavior']))
                $filterData['behavior'] = Centurion_Controller_CRUD::FILTER_BEHAVIOR_CONTAINS;

            switch ($filterData['type']) {
                case Centurion_Controller_CRUD::FILTER_TYPE_RADIO:
                    $checkboxType = 'radio';
                case Centurion_Controller_CRUD::FILTER_TYPE_SELECT:
                    $checkboxType = 'select';
                case Centurion_Controller_CRUD::FILTER_TYPE_CHECKBOX:
                    $element = $this->createElement($checkboxType, $key, array('label' => $label));
                    //TODO: for the array_flip, maybe we should change the definition of data in CRUD_Controller
                    $element->addMultiOptions($filterData['data']);
                    $element->setSeparator('');
                    if ($checkboxType === 'multiCheckbox')
                        $element->setIsArray(true);
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_TEXT:
                case Centurion_Controller_CRUD::FILTER_TYPE_NUMERIC:
                    $element = $this->createElement('text', $key, array('label' => $label));
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_DATE:
                    $element = $this->createElement('text', $key, array('label' => $label, 'class' => 'datepicker'));
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATE:
                    $form = new self();
                    $element = $form->createElement('text', 'gt', array('class' => 'datepicker', 'belongsTo' => $key, 'label' => $this->_translate('From')));
                    $form->addElement($element, 'gt');

                    $element = $form->createElement('text', 'lt', array('class' => 'datepicker', 'belongsTo' => $key, 'label' => $this->_translate('To')));
                    $form->addElement($element, 'lt');

                    $element = null;
                    $form->setDescription($label);

                    $this->addSubForm($form, $key);
                    $this->getSubForm($key)->setDecorators($this->defaultDisplayGroupDecorators);

                    $form->getElement('lt')->setDecorators($this->defaultElementDecoratorsInDisplayGroup);
                    $form->getElement('gt')->setDecorators($this->defaultElementDecoratorsInDisplayGroup);
//                    $this->addDisplayGroup(array('lt', 'gt'), $key, array('description' => $label));
                    break;
            }

            if (null !== $element)  {
                $this->addElement($element, $key);
            }
        }

        $this->_filters = $filters;
    }



    public function getSqlFilter()
    {
        $sqlFilter = array();

        foreach ($this->getElements() as $key => $element) {
            $value = $element->getValue();
            if ($element->hasErrors()||!isset($this->_filters[$key])||(is_string($value) && trim($value) === '')||(is_array($value) && count($value) == 0)||$value === null) {
                continue;
            }

            switch ($this->_filters[$key]['behavior']) {
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_CONTAINS:
                    $tabs = explode(' ', $value);
                    foreach ($tabs as $value) {
                        $sqlFilter[] = array($this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_CONTAINS, '%' . $value . '%');
                    }
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_IN:
                    $sqlFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_IN] = $value;
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_LIKE:
                    $sqlFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_CONTAINS] = $value;
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_EXACT:
                    $sqlFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_EXACT] = $value;
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_RECORD:
                    //TODO:
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_CALLBACK:
                    //TODO:
                    break;
				case Centurion_Controller_CRUD::FILTER_BEHAVIOR_GREATER:
					$sqlFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_GREATER] = $value;
					break;
				case Centurion_Controller_CRUD::FILTER_BEHAVIOR_LESS:
					$sqlFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_LESS] = $value;
					break;
                 case Centurion_Controller_CRUD::FILTER_BEHAVIOR_NOTHING:
                 default:
                    break;
            }
        }

        foreach($this->getSubForms() as $key => $subform) {
            if (!isset($this->_filters[$key])) {
                continue;
            }
            switch ($this->_filters[$key]['behavior']) {
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_BETWEEN:
                    $sqlSubFilter = array();
                    $lt = $subform->getElement('lt')->getValue();
                    $gt = $subform->getElement('gt')->getValue();

                    if (trim($lt) !== '') {
                        $sqlSubFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_LESS_EQUAL] = $lt;
                    }
                    if (trim($gt) !== '') {
                        $sqlSubFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_GREATER_EQUAL] = $gt;
                    }
                    break;
                default;
                    $sqlSubFilter = $subform->getSqlFilter();
                    break;
            }
            $sqlFilter = array_merge($sqlFilter, $sqlSubFilter);
        }

        return $sqlFilter;
    }
}