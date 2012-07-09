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
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
class Admin_Form_Filter extends Centurion_Form
{
    protected $_filters = null;
    
    /**
     * 
     * Enter description here ...
     * @var Centurion_Db_Table_Abstract
     */
    protected $_table = null;

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

    public function init()
    {
        parent::init();
        $this->setAttrib('id', 'grid-filter-form');
    }

    public function setTable($table = null)
    {
        $this->_table = $table;
    }
    
    public function setFilters($filters)
    {
        $this->cleanForm();

        foreach ($filters as $key => &$filterData) {
            if (!is_array($filterData)) {
                $filterData = array('label' => $filterData);
                if ($this->_table !== null) {
                    $reference = $this->_table->info(Centurion_Db_Table_Abstract::REFERENCE_MAP);
                    if (isset($reference[$key])) {
                        $refTable = Centurion_Db::getSingletonByClassName($reference[$key]['refTableClass']);
                        $rowset = $refTable->all();
                        $data = array();
                        
                        foreach ($rowset as $row) {
                            $data[] = (string) $row;
                        }
                        
                        $filterData['type'] = Centurion_Controller_CRUD::FILTER_TYPE_CHECKBOX;
                        $filterData['data'] = $data;
                        
                    }
                }
            }
                
            if (isset($filterData['label']))
                $label = $filterData['label'];
            else
                $label = '';

            $checkboxType = 'multiCheckbox';
            $element = null;

            if (!isset($filterData['type'])) {
                $filterData['type'] = Centurion_Controller_CRUD::FILTER_TYPE_TEXT;
            }

            if (!isset($filterData['column'])) {
                $filterData['column'] = $key;
            }

            if (!isset($filterData['behavior'])) {
                $filterData['behavior'] = Centurion_Controller_CRUD::FILTER_BEHAVIOR_CONTAINS;
            }

            switch ($filterData['type']) {
                case Centurion_Controller_CRUD::FILTER_TYPE_RADIO:
                    $checkboxType = 'radio';
                case Centurion_Controller_CRUD::FILTER_TYPE_SELECT:
                    if ($checkboxType === 'multiCheckbox')
                        $checkboxType = 'select';
                case Centurion_Controller_CRUD::FILTER_TYPE_CHECKBOX:
                    $element = $this->createElement($checkboxType, $key, array('label' => $label));

                    if (!isset($filterData['data'])) {
                        $manyDependentTables = $this->_table->info('manyDependentTables');
                        if (isset($manyDependentTables[$key])) {
                            $refRowSet = Centurion_Db::getSingletonByClassName($manyDependentTables[$key]['refTableClass'])->fetchAll();
                            $filterData['data'] = array();
                            foreach ($refRowSet as $refRow) {
                                //TODO: this doesn't work with multiple primary key
                                $filterData['data'][$refRow->pk] = $refRow->__toString();
                            }
                            asort($filterData['data']);
                            //Add before a joker to disable this filter
                            $filterData['data'] = array('' => $this->_translate('All')) + $filterData['data'];
                        }
                    }
                    else{
                        //To allow rowset in the option "data" and not force developper to pass an array
                        if($filterData['data'] instanceof Centurion_Db_Table_Rowset_Abstract){
                            $_tmpData = array();
                            foreach($filterData['data'] as $row)
                                $_tmpData[$row->pk] = (string) $row;

                            asort($_tmpData);
                            //Add before a joker to disable this filter
                            $filterData['data'] = array('' => $this->_translate('All')) + $_tmpData;
                        }
                    }

                    $element->addMultiOptions($filterData['data']);
                    $element->setSeparator('');
                    if ($checkboxType === 'multiCheckbox') {
                        $element->setIsArray(true);
                    }
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_TEXT:
                case Centurion_Controller_CRUD::FILTER_TYPE_NUMERIC:
                    $element = $this->createElement('text', $key, array('label' => $label));
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_DATE:
                    $element = $this->createElement('text', $key, array('label' => $label, 'class' => 'datepicker'));
                    break;
                case Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATE:
                case Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATETIME:
                    $form = new self();
                    
                    if ($filterData['type'] == Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATETIME) {
                        $class = 'field-datetimepicker';
                    } else {
                        $class = 'datepicker';
                    }
                        
                    $element = $form->createElement('text', 'gt', array('class' => $class, 'belongsTo' => $key, 'label' => $this->_translate('From'), 'value' => '26/08/11 03:00'));
                    $form->addElement($element, 'gt');

                    $element = $form->createElement('text', 'lt', array('class' => $class, 'belongsTo' => $key, 'label' => $this->_translate('To'), 'value' => '26/08/11 03:00'));
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
                    call_user_func_array($this->_filters[$key]['callback'], array($value, &$sqlFilter));
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
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_CALLBACK:
                    $sqlSubFilter = array();
                    call_user_func_array($this->_filters[$key]['callback'], array($subform->getValues(), &$sqlFilter));
                    //TODO:
                    break;
                case Centurion_Controller_CRUD::FILTER_BEHAVIOR_BETWEEN:
                    $sqlSubFilter = array();
                    $lt = $subform->getElement('lt')->getValue();
                    $gt = $subform->getElement('gt')->getValue();
                    
                    if ($this->_filters[$key]['type'] == Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATE) {
                        $format = Zend_Locale_Data::getContent(null, 'date', array('gregorian', 'short'));
                    } else if ($this->_filters[$key]['type'] == Centurion_Controller_CRUD::FILTER_TYPE_BETWEEN_DATETIME) {
                        $format = Zend_Locale_Data::getContent(null, 'datetime', array('gregorian', 'short'));
                    }
                        
                    if (trim($lt) !== '') {
                        $lt = new Zend_Date($lt, $format);
                        $sqlSubFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_LESS_EQUAL] = $lt->toString(Centurion_Date::MYSQL_DATETIME);
                    }
                    
                    if (trim($gt) !== '') {
                        $gt = new Zend_Date($gt, $format);
                        $sqlSubFilter[$this->_filters[$key]['column'] . Centurion_Db_Table_Select::RULES_SEPARATOR . Centurion_Db_Table_Select::OPERATOR_GREATER_EQUAL] = $gt->toString(Centurion_Date::MYSQL_DATETIME);
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
