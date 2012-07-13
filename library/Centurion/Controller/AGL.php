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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */

/**
 * This class only works with models which contains a pk id
 */
class Centurion_Controller_AGL extends Centurion_Controller_Action
{
    /**
     * @todo : description
     */
    const PARAM_FORM_VALUES = 'crud_form_values';

    /**
     * Const used to create filters
     */
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

    /**
     * Const used to define the filters behavior
     */
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

    /**
     * Default column type
     */
    const COLS_ROW_COL = 'row_col';

    /**
     * Call a function of the row to display the content of the column
     * @todo: exemple
     */
    const COLS_ROW_FUNCTION = 'row_function';

    /**
     * Call a callback function (anywhere) to display the content of the column (in grid)
     *@todo : exemple
     */
    const COLS_CALLBACK = 'callback';

    /**
     * Display a column preview with the permalink of the row
     * @todo : exemple
     */
    const COLS_TYPE_PREVIEW = 'preview';

    /**
     * Can be used to display an alternative first column
     *
     * Exemple :
     * 'columnname'      =>  array(
     *      'type'  => self::COL_TYPE_FIRSTCOL,
     *      'label' => $this->view->translate('Name'),
     *      'param' => array(
     *          'title' => 'name',                      // Column used to display the first line
     *          'cover' => null,                        // Column used to display the pictur
     *          'subtitle' => null,                     // Column used to display the second line
     *      ),
     * )
     */
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

    /**
     * Can be used on a bool database column to switch in the grid the value
     * @todo : exemple
     */
    const COLS_FILTER_ONOFF = 'onoff';

    /**
     * @deprecated : seems to be unused
     */
    const COLS_FILTER_DATE = 'date';

    /**
     * Select object used to get the content of the list
     * @var Centurion_Db_Table_Select
     */
    protected $_select = null;

    /**
     * An array which contains all the filters of the grid
     *
     * @var array
     * @todo : exemple
     */
    protected $_filters = array();

    /**
     * @var array
     * @todo : exemple
     */
    protected $_extraParam = array();

    /**
     * Item per page if pagination is active
     * @var int
     */
    protected $_itemPerPage = 30;

    /**
     * The default order to display row in grid
     *
     * Exemple : $this->_defaultOrder = 'name ASC'
     *
     * @var null
     */
    protected $_defaultOrder = null;

    /**
     * Enable/disable the filter system
     * @var bool
     */
    protected $_hasFiltred = false;

    /**
     * Enable/disable session to keep actives filters
     * @var bool
     */
    protected $_useSession = true;
    
    /**
     * Layout pour rendre la vue (media/grid/...)
     * @var string
     */
    protected $_layout = 'grid';

    /**
     * @todo : description
     * @var string
     */
    protected $_sort = null;

    /**
     * Order to display row in grid
     * Only used if $_sort != null and $_sort is a column displayed in the grid ($_display)
     *
     * Default value : Zend_Db_Select::SQL_ASC
     *
     * @var null
     */
    protected $_order = null;

    /**
     * @todo : description
     * @var Zend_Paginator
     */
    protected $_paginator = null;

    /**
     * @todo : description
     * @var Admin_Form_Filter
     */
    protected $_filter = null;

    /**
     * @todo : description
     * @var string
     */
    protected $_debug = '';

    /**
     * @todo : description
     * @var array
     */
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

    /**
     * @todo : description
     * @var array
     */
    protected $_rowActions = array();

    /**
     * Current model
     * @var Centurion_Db_Table_Abstract|string
     */
    protected $_model = null;

    /**
     * Display checkbox on each row in the grid
     * @var bool
     */
    protected $_showCheckbox = false;

    /**
     * @todo : description
     * @var null|string
     */
    protected $_dateFormat = null;
    protected $_dateFormatIso = null;
    protected $_timeFormatIso = null;

    /**
     * Getter $_sort
     *
     * @return string|null
     */
    public function getSort()
    {
        return $this->_sort;
    }

    /***
     * Setter $_displays
     *
     * @param $model array
     */
    public function setDisplays($displays)
    {
        $this->_displays = $displays;
        return $this;
    }

    /**
     * Getter $_displays
     *
     * @return array
     */
    public function getDisplays()
    {
        return $this->_displays;
    }

    /***
     * Setter $_sort
     *
     * @param $_sort string|null
     */
    public function setSort($sort)
    {
        $this->_sort = $sort;
        return $this;
    }

    /**
     * Getter $_order
     *
     * @return string|null
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /***
     * Setter $_order
     *
     * @param $_order string|null
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Getter $_hasFiltred
     *
     * @return bool
     */
    public function getHasFiltred()
    {
        return $this->_hasFiltred;
    }

    /***
     * Setter $_hasFiltred
     *
     * @param $bool bool
     */
    public function setHasFiltred($bool)
    {
        $this->_hasFiltred = $bool;
        return $this;
    }
    
    /**
     * Class contructor
     *
     * @param Zend_Controller_Request_Abstract $request
     * @param Zend_Controller_Response_Abstract $response
     * @param array $invokeArgs
     * @return void
     */
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

    /**
     * @todo : description
     *
     * @return void
     */
    public function init()
    {
        //@TODo Why 2 call to direct() ?
        $this->getHelper('ContextAutoSwitch')->direct(array('index', 'list'));
        $this->getHelper('ContextAutoSwitch')->direct();

        //@TODO: why call to setParams. Params should already have been populated by frontController
        $this->_request->setParams($this->getHelper('params')->direct());
        
        parent::init();
    }

    /**
     * 
     * Return the paginator of an $select object
     * @param $select
     * @return Zend_Paginator
     */
    public function getPaginator($select)
    {
        if (null === $this->_paginator) {
            $adapter = new Zend_Paginator_Adapter_DbTableSelect($select);
            $this->_paginator = new Zend_Paginator($adapter);
            if ($this->_itemPerPage > 0) {
                $this->_paginator->setItemCountPerPage($this->_itemPerPage);
            }
            $this->_paginator->setCurrentPageNumber($this->_page);
        }

        return $this->_paginator;
    }

    /**
     * This function can be used in a controller who extends Centurion_Controller_AGL OR Centurion_Controller_CRUD
     * to modify the current select object.
     * 
     * @return Centurion_Db_Table_Select
     * @deprecated use public function getSelect instead
     */
    protected function _getSelect()
    {
        return $this->getSelect();
    }

    /**
     *
     * @return Centurion_Db_Table_Select
     */
    public function getSelect()
    {
        if (null === $this->_select) {
            $this->_select = $this->getModel()->select(true);
        }

        return $this->_select;
    }

    /**
     * Getter for the model
     *
     * @return Centurion_Db_Table_Abstract|null|string
     * @throws Exception
     */
    protected function _getModel()
    {
        return $this->getModel();
    }

    /**
    * Getter $_model
    *
    * @return Centurion_Db_Table_Abstract|null
    * @throws Exception
    */
    public function getModel() {
        if (is_string($this->_model)) {
            $this->_model = Centurion_Db::getSingleton($this->_model);
        } else if (null === $this->_model) {
            throw new Centurion_Controller_Action_Exception('No model given');
        }

        return $this->_model;
    }

    /**
     * @param string|Centurion_Db_Table_Abstract $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->_model = $model;
        return $this;
    }

    /**
     *
     * Enter description here ...
     * @todo test
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

            $useDefaultSort = true;

            if (null !== $this->_sort && isset($this->_displays[$this->_sort])) {
                if (!isset($this->_displays[$this->_sort]['sortable']) || $this->_displays[$this->_sort]['sortable'] == false) {
                    $useDefaultSort = false;
                }
            }

            if (!$useDefaultSort) {
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

                // GROUP BY pour �viter les doublons de row � cause des JOIN
                //TODO : id en dur, utiliser la pk du model
                if (!isset($this->_noUseGroupe))
                    $select->group($select->getTable()->info('name') . '.id');
            }
        }

        return $this->_select;
    }

    /**
     * Delete all the filter stored in session
     *
     * @return void
     */
    public function resetFilter()
    {
        $session = $this->getSession();
        $session->unsetAll();
    }

    /**
     * Action to do before the function generateList
     *
     * @return void
     */
    protected function _preGenerateList()
    {
        
    }
    
    /**
    * Action to do after the function generateList
    *
    * @return void
    */
    protected function _postGenerateList($paginator)
    {
        
    }

    /**
     * @todo : description
     * @return mixed
     * @throws Centurion_Exception
     */
    public function generateList()
    {
        $select = $this->getSelectFiltred();

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
            $params += $this->getFilter()->getValuesForUrl();

            if (!isset($col['sortable']) || $col['sortable'] !== false) {
                $link = $this->view->url($params);
            }else {
                $link = null;
            }

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
                //TODO : id en dur, utiliser la pk du model
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

                    //TODO : id en dur, utiliser la pk du model
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
                            } else {
                                $date = new Zend_Date($value, Centurion_Date::MYSQL_DATETIME);
                                $value = $date->toString(Zend_Date::DATE_MEDIUM);
                            }
                        }
                        if (isset($filter['filterClass']) && class_exists(isset($filter['filterClass']))) {
                            $filter = new $filter['filterClass']($filter['options']);
                            
                            $value = $filter->filter($value);
                        }
                    }
                }

                $temp[$key] = $value;
            }
            $temp['row'] = $row;
            //TODO : id en dur, utiliser la pk du model
            $result[$row->id] = $temp;
        }
        $this->_result = $result;

        return $this->_result;
    }

    /**
     * Generate the filter Form object
     *
     * @return Admin_Form_Filter|null
     */
    public function getFilter()
    {
        if (null === $this->_filter) {
            $this->_filter = new Admin_Form_Filter(array('table' => $this->getModel(), 'filters' => $this->_filters));
            $this->_filter->setIsArray(true);
            $this->_filter->setElementsBelongTo('filter');
            $this->_filter->setAction($this->view->url(array('page' => null)));
            $this->_filter->setMethod('GET');
            $this->_filter->setDescription($this->view->translate('Filters'));
            $this->_filter->addElement('submit', 'submit', array(
                                                                'label' => $this->view->translate('Submit'), 
                                                                'decorators' => array(
                                                                    'ViewHelper', array(
                                                                        'HtmlTag',  array(
                                                                            'tag' => 'div', 
                                                                            'class' => 'submit ui-button-tiny-squared')
                                                                        )
                                                                    )
                                                                )
            );
        }
        return $this->_filter;
    }

    /**
     * Set params to the view
     *
     * @return void
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


    public function getSession()
    {
        return new Zend_Session_Namespace(sprintf('crud_%s_%s', $this->_request->getModuleName(), $this->_request->getControllerName()));
    }

    /**
    * Get all current params
    * In order we use :
    *  - params set in the url
    *  - params set in session (if session are enable)
    *
    * If params are set in url save their values in session
    *
    * @return void
    */
    protected function _getParams()
    {
        $this->_page = $this->_getParam('page', null);
        $this->_sort = $this->_getParam('sort', null);
        $this->_order = $this->_getParam('order', null);
        
        if ($this->_useSession) {
            
            $session = $this->getSession();
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

    /**
     * Generate & display the grid with the filter form, toolbar action & pagination
     *
     * @return void
     */
    public function indexAction()
    {
        Centurion_Traits_Common::checkTraitOverload($this, 'indexAction', array(), false);

        // Get all the params used to generate the list
        $this->_getParams();

        $this->generateList();
        $this->_setViewParams();

        $this->renderIfNotExists('grid/list', null, true);
    }

    /**
     * Verify if the view file exists in a views directory
     * else we use the file name passed in attribute to do the render
     *
     * @return void
     * @todo use renderToResponse instead
     */
    public function renderIfNotExists($action = null, $name = null, $noController = false)
    {
        $dirs = $this->view->getScriptPaths();
        $renderScript = false;
        $viewSuffix = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer')->getViewSuffix();
        $viewFile = $this->getRequest()->getControllerName()
                  . DIRECTORY_SEPARATOR
                  . $this->getRequest()->getActionName()
                  . '.' . $viewSuffix;
        
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
