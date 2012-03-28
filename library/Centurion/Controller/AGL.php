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
 * @package     Centurion_Controller
 * @subpackage  AGL
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  AGL
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_Controller_AGL extends Centurion_Controller_Action
{
    const PARAM_FORM_VALUES = 'crud_form_values';

    const FILTER_TYPE_RADIO = 'radio';
    const FILTER_TYPE_CHECKBOX = 'checkbox';
    const FILTER_TYPE_SELECT = 'select';
    const FILTER_TYPE_TEXT = 'text';
    const FILTER_TYPE_NUMERIC = 'numeric';
    const FILTER_TYPE_DATE = 'date';
    const FILTER_TYPE_SUBFILTER = 'subfilter';
    const FILTER_TYPE_BETWEEN_DATE = 'between_date';
    const FILTER_TYPE_BETWEEN_DATETIME = 'between_datetime';
    const FILTER_TYPE_BETWEEN_NUMERIC = 'between_numeric';
    const FILTER_TYPE_BETWEEN_TEXT = 'between_text';

    // const FILTER_TYPE_SLIDER = 'slider';
    // const FILTER_TYPE_SLIDER = 'slider';

    const FILTER_BEHAVIOR_LIKE = 'like';
    const FILTER_BEHAVIOR_CONTAINS = 'contains';
    const FILTER_BEHAVIOR_IN = 'in';
    const FILTER_BEHAVIOR_EXACT = 'exact';
    const FILTER_BEHAVIOR_RECORD = 'record';
    const FILTER_BEHAVIOR_CALLBACK = 'callback';
    const FILTER_BEHAVIOR_BETWEEN = 'between';
    const FILTER_BEHAVIOR_NOTHING = 'nothing';
	const FILTER_BEHAVIOR_GREATER = 'gt';
	const FILTER_BEHAVIOR_LESS = 'lt';

    const COLS_ROW_COL = 'row_col';
    const COLS_ROW_FUNCTION = 'row_function';
    const COLS_CALLBACK = 'callback';

    const COLS_TYPE_PREVIEW = 'preview';
    const COL_TYPE_FIRSTCOL = 'fisrtcol';

    /**
     *
     * @deprecated
     */
    const COL_TYPE_ONOFF = 'onoff';
    /**
     *
     * @deprecated
     */
    const COL_DISPLAY_DATE = 'date';

    const COLS_FILTER_ONOFF = 'onoff';
    const COLS_FILTER_DATE = 'date';

    /**
     *
     * Enter description here ...
     * @var Centurion_Db_Table_Select
     */
    protected $_select = null;
    protected $_filters = array();
    protected $_extraParam = array();

    /**
     * Enter description here ...
     * @var int
     */
    protected $_itemPerPage = 30;
    protected $_defaultOrder = null;

    protected $_hasFiltred = false;

    protected $_useSession = true;
    
    /**
     * Layout pour rendre la vue (media/grid/...)
     * @var string
     */
    protected $_layout = 'grid';

    /**
     *
     * @var string
     */
    protected $_sort = null;

    /**
     *
     * @var string
     */
    protected $_order = null;

    /**
     *
     * @var Zend_Paginator
     */
    protected $_paginator = null;

    /**
     *
     * @var Admin_Form_Filter
     */
    protected $_filter = null;

    /**
     *
     * @var string
     */
    protected $_debug = '';

    protected $_toolbarActions = array();

    /**
     * Full schema :
     *
     * array(
     *  'key' => array(
     *          'label' => 'label already translated',
     *          'sort'  => 'col'|array('object', 'callback function to sort'), (if unset, the key will be used as col)
     *          'sortable' => 'true|false', (if unset true will be used as value)
     *          'type'  => self::COLS_ROW_COL,
     *          'column'=> 'my_column',
     *          'filters' => array(
     *              'filter1_const' => array('options'),
     *              'filter2_const' => array('options')
     *          )
     *      ),
     * ...
     *
     * Short schema
     * array (
     * 	'my_column' => 'label already translated',
     * )
     *
     * )
     * @var array[string]mixed
     */
    protected $_displays = array();

    protected $_rowActions = array();

    /**
     *
     * @var Centurion_Db_Table_Abstract|string
     */
    protected $_model = null;
    
    protected $_showCheckbox = false;

    protected $_dateFormat = null;
    protected $_dateFormatIso = null;
    protected $_timeFormatIso = null;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        if (null == $this->_dateFormat) {
            $this->_dateFormatIso = Zend_Locale_Data::getContent(null, 'date', array('gregorian', 'short'));
            $this->_dateFormat = Centurion_Locale_Format::convertIsoToDatepickerFormat($this->_dateFormatIso);
        } else {
            $this->_dateFormatIso = Centurion_Locale_Format::convertDatepickerToIsoFormat($this->_dateFormat);
        }
        
        $this->_timeFormatIso = Zend_Locale_Data::getContent(null, 'time', array('gregorian', 'short'));

        parent::__construct($request, $response, $invokeArgs);
        
        $this->view->dateFormat = $this->_dateFormat;
    }

    public function init()
    {
        $this->getHelper('ContextAutoSwitch')->direct(array('index', 'list'));
        $this->getHelper('ContextAutoSwitch')->direct();

        $this->_request->setParams($this->getHelper('params')->direct());
        
        parent::init();
    }

    /**
     * 
     * Return the paginator of an $select object
     * @param $select
     * @return Zend_Paginator
     */
    public function getPaginator($select = null)
    {
        if (null === $this->_paginator) {
            $adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
            $this->_paginator = new Zend_Paginator($adapter);
            if ($this->_itemPerPage > 0)
                $this->_paginator->setItemCountPerPage($this->_itemPerPage);
            $this->_paginator->setCurrentPageNumber($this->_page);
        }
        return $this->_paginator;
    }

    /**
     *
     * @return Centurion_Db_Table_Select
     */
    protected function _getSelect()
    {
        if (null === $this->_select) {
            $this->_select = $this->_getModel()->select(true);
        }

        return $this->_select;
    }

    protected function _getModel()
    {
        if (is_string($this->_model)) {
            $this->_model = Centurion_Db::getSingleton($this->_model);
        } elseif (null === $this->_model) {
            throw new Exception('No model given');
        }
        return $this->_model;
    }

    /**
     *
     * Enter description here ...
     * @return Centurion_Db_Table_Select
     */
    public function getSelectFiltred()
    {
        if (false === $this->_hasFiltred) {
            $this->_hasFiltred = true;
            $select = $this->_getSelect();
            $modelTable = $select->getTable();

            $naturals = $modelTable->info(Centurion_Db_Table_Abstract::COLS);
            $queryCols = $select->getColumnsName();
            $referenceMap = $modelTable->info(Centurion_Db_Table_Abstract::REFERENCE_MAP);
            $modelTableName = $modelTable->info(Centurion_Db_Table_Abstract::NAME);

            foreach ($this->_displays as $key => $options) {
                if (is_array($options) && isset($options['type']) && $options['type'] !== self::COLS_ROW_COL) {
                    continue;
                }

                if (is_array($options) && isset($options['column'])) {
                    $col = $options['column'];
                } else {
                    $col = $key;
                }

                if (in_array($col, $naturals)) {
                    if (!$select->isInQuery($col, $modelTableName)) {
                        $select->columns(new Zend_Db_Expr(sprintf(sprintf('%s.%s',
                                                       $modelTable->getAdapter()->quoteIdentifier($modelTableName),
                                                       $modelTable->getAdapter()->quoteIdentifier($col)))));
                    }
                } else {
                    try {
                        if (!in_array($col, $queryCols) && !isset($referenceMap[$col])) {
                            $select->columns(new Zend_Db_Expr(sprintf('%s AS %s',
                                                               $select->addRelated($col),
                                                               $modelTable->getAdapter()->quoteIdentifier($col))));
                        }
                    } catch (Centurion_Db_Exception $e) {

                    }
                }
            }
            
            if (null !== $this->_sort && isset($this->_displays[$this->_sort])) {
                $options = $this->_displays[$this->_sort];
                if ($this->_order !== Zend_Db_Select::SQL_DESC && $this->_order !== Zend_Db_Select::SQL_ASC) {
                        $this->_order = Zend_Db_Select::SQL_ASC;
                }

                if (is_array($options) && isset($options['sort']) && is_array($options['sort'])) {
                    // call function to sort COLS_CALLBACK
                    call_user_func($options['sort'], $select, $this->_order);
                } else if (is_array($options) && isset($options['sort']) && $options['sort'] instanceof Zend_Db_Expr) {
                     $select->order(new Zend_Db_Expr($options['sort'] . ' ' . $this->_order));
                } else {
                    if (is_string($options) || !isset($options['column'])) {
                        $sort = $this->_sort;
                    } else {
                        $sort = $options['column'];
                    }

                    if (strpos($sort, Centurion_Db_Table_Select::RULES_SEPARATOR)) {
                        $sort = $select->addRelated($sort);
                        $select->order(new Zend_Db_Expr($sort . ' ' . $this->_order));
                    } else {
                        $select->order($sort . ' ' . $this->_order);
                    }
                }
            } else {
                if (isset($this->_defaultOrder) && $this->_defaultOrder !== null) {
                    $select->order($this->_defaultOrder);
                }
            }

            $filter = $this->getFilter();
            if ($filter->isValid($this->_request->getParams())) {
                $select->filter($filter->getSqlFilter());
                
                if (!isset($this->_noUseGroupe))
                    $select->group($select->getTable()->info('name') . '.id');
            }
        }

        return $this->_select;
    }

    public function resetFilter()
    {
        $session = new Zend_Session_Namespace(sprintf('crud_%s_%s', $this->_request->getModuleName(), $this->_request->getControllerName()));
        $session->unsetAll();
    }

    protected function _preGenerateList()
    {
        
    }
    
    protected function _postGenerateList($paginator)
    {
        
    }
    
    public function generateList()
    {
        $select = $this->getSelectFiltred();
        $filter = $this->getFilter();

        //TODO: move all of this in a view helper (except pagination, )
        $headCol = array();
        foreach ($this->_displays as $key => $col) {
            if (is_string($col)) {
                $label = $col;
            } else if (is_array($col) && isset($col['label'])) {
                $label = $col['label'];
            } else {
                throw new Centurion_Exception(sprintf('Col %s have no label', $key));
            }

            $params = array('page' => 0, 'sort' => $key, 'order' => ($this->_sort !== $key || $this->_order === Zend_Db_Select::SQL_DESC)?Zend_Db_Select::SQL_ASC:Zend_Db_Select::SQL_DESC);
            $params += $this->_filter->getValuesForUrl();

            if (!isset($col['sortable']) || $col['sortable'] !== false)
                $link = $this->view->url($params);
            else
                $link = null;

            $headCol[$key] = array('label' => $label, 'link' => $link);
        }

        $this->_headCol = $headCol;

        $this->_preGenerateList();
        
        $paginator = $this->getPaginator($select);

        $this->_postGenerateList($paginator);
        
        $result = array();
        foreach ($paginator as $row) {
            $temp = array();
            if ($this->_showCheckbox) {
                $temp['checkbox'] = '<input type="checkbox" value="'.$row->id.'" name="rowId[]">';
            }
            
            foreach ($this->_displays as $key => $options) {
                if (is_string($options) || !isset($options['type'])) {
                    $value = $row->{$key};
                }else if ($options['type'] === self::COLS_ROW_COL) {
                    if (isset($options['col'])) {
                        $col = $options['col'];
                    } else {
                        $col = $key;
                    }
                    $value = $row->{$col};
                } else if ($options['type'] === self::COLS_ROW_FUNCTION) {
                    if (isset($options['function']))
                        $function = $options['function'];
                    else
                        $function = $key;
                    $value = $row->{$function}();
                } else if ($options['type'] === self::COLS_CALLBACK) {
                    if (!isset($options['callback']))
                        $options['callback'] = array($this, $key);
                    $params = array_merge(array($row), (isset($options['callbackParams']) ? (array) $options['callbackParams'] : array()));
                    $value = call_user_func_array($options['callback'], $params);
                } else if ($options['type'] === self::COLS_TYPE_PREVIEW) {
                    $value = '<a href="'.$this->view->url(array('object' => $row), $options['param']['route']).'">'.$this->view->translate('Preview').'</a>';
                } else if ($options['type'] === self::COL_TYPE_FIRSTCOL) {
                    $value = $this->view->renderToString('centurion/_first_col.phtml',
                                           array('extraParam' => $this->_extraParam,
                                                 'row'        => $row,
                                                 'first'      => $options['param'],
                                                 'controller' => $this->_request->getControllerName(),
                                                 'module'     => $this->_request->getModuleName()));
                } else if ($options['type'] === self::COL_TYPE_ONOFF) {
                    if (isset($options['onoffLabel']) && count($options['onoffLabel']) === 2) {
                        $onLabel = $options['onoffLabel'][0];
                        $offLabel = $options['onoffLabel'][1];
                    } else {
                        $onLabel = $this->view->translate('On');
                        $offLabel = $this->view->translate('Off');
                    }

                    if (isset($options['column'])) {
                        $column = $options['column'];
                    } else {
                        $column = $key;
                    }

                    $element = new Zend_Form_Element_Select(array('disableTranslator' => true, 'name' => $key . '_' . $row->id, 'class' => 'field-switcher'));
                    $element->addMultiOption('1', $onLabel);
                    $element->addMultiOption('0', $offLabel);
                    $element->removeDecorator('Label');

                    $element->setValue($row->{$column});

                    $value = $element->render();
                } else {
                    //TODO: more explicit (add type, ...)
                    throw new Centurion_Exception(sprintf('I don\'t now what to do with the col %s', $key));
                }

                if (is_array($options) && isset($options['filters'])) {
                    $options['filters'] = (array) $options['filters'];
                    //TODO: allow to use real filter (instance of Zend_Filter_Interface)
                    foreach ($options['filters'] as $filter) {
                        if ($filter === self::COL_DISPLAY_DATE) {
                            if ($value == '0000-00-00 00:00:00' || $value == null) {
                                $value = '';
                            }else {
                                $date = new Zend_Date($value, Centurion_Date::MYSQL_DATETIME);
                                $value = $date->toString(Zend_Date::DATE_MEDIUM);
                            }
                        }
                    }
                }

                $temp[$key] = $value;
            }
            $temp['row'] = $row;
            $result[$row->id] = $temp;
        }
        $this->_result = $result;

        return $this->_result;
    }

    /**
     * Generate the filter Form object
     */
    public function getFilter()
    {
        if (null === $this->_filter) {
            $this->_filter = new Admin_Form_Filter(array('table' => $this->_getModel(), 'filters' => $this->_filters));
            $this->_filter->setIsArray(true);
            $this->_filter->setElementsBelongTo('filter');
            $this->_filter->setAction($this->view->url(array('page' => null)));
            $this->_filter->setMethod('GET');
            $this->_filter->setDescription($this->view->translate('Filters'));
            $this->_filter->addElement('submit', 'submit', array('label' => $this->view->translate('Submit'), 'decorators' => array('ViewHelper', array('HtmlTag', array('tag' => 'div', 'class' => 'submit ui-button-tiny-squared')))));
        }
        return $this->_filter;
    }

    /**
     * Set params to the view
     */
    protected function _setViewParams()
    {
        $this->view->controller = $this->_request->getControllerName();
        $this->view->result = $this->_result;
        $this->view->order = $this->_order;
        $this->view->sort = $this->_sort;
        $this->view->headCol = $this->_headCol;
        $this->view->filter = $this->getFilter();
        $this->view->debug = $this->_debug;
        $this->view->errors = $this->_getParam('errors');
        $this->view->paginator = $this->_paginator;
        $this->view->layout = $this->_layout;
        $this->view->toolbarActions = $this->_toolbarActions;
        $this->view->rowActions = $this->_rowActions;
        $this->view->showCheckbox = $this->_showCheckbox;
    }


    protected function _getParams()
    {
        
        $this->_page = $this->_getParam('page', null);
        $this->_sort = $this->_getParam('sort', null);
        $this->_order = $this->_getParam('order', null);
        
        if ($this->_useSession) {
            $session = new Zend_Session_Namespace(sprintf('crud_%s_%s', $this->_request->getModuleName(), $this->_request->getControllerName()));
            if ($this->_order === null && isset($session->order)) {
                $this->_order = $session->order;
            }
    
            if ($this->_page === null && isset($session->page)) {
                $this->_page = $session->page;
            }
    
            if ($this->_sort === null && isset($session->sort)) {
                $this->_sort = $session->sort;
            }
    
            $params = $this->_request->getParam('filter', array());
    
            if (!isset($params['submit']) && isset($session->filter)) {
                $this->_request->setParam('filter', $session->filter);
                $params = $session->filter;
            }
    
            unset($params['filter']['submit']);
    
            $session->page = $this->_page;
            $session->sort = $this->_sort;
            $session->order = $this->_order;
            $session->filter = $params;
        }
    }

    public function indexAction()
    {
        Centurion_Traits_Common::checkTraitOverload($this, 'indexAction', array(), false);

        $this->_getParams();

        $this->generateList();
        $this->_setViewParams();

        $this->renderIfNotExists('grid/list', null, true);
    }

    /**
     * @todo use renderToResponse instead
     */
    public function renderIfNotExists($action = null, $name = null, $noController = false)
    {
        $dirs = $this->view->getScriptPaths();
        $renderScript = false;
        $viewFile = $this->getRequest()->getControllerName()
                  . DIRECTORY_SEPARATOR
                  . $this->getRequest()->getActionName()
                  . '.' . $this->viewSuffix;
                  
        foreach ($dirs as $dir) {
            if (is_readable($dir . $viewFile)) {
                $renderScript = true;
                break;
            }
        }

        if (!$renderScript) {
            $this->_helper->viewRenderer->setNoRender(true);
            $this->render($action, $name, $noController);
        }
    }
}
