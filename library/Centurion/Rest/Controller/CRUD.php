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
 * @package     Centurion_Rest
 * @subpackage  Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */
/**
 * @category    Centurion
 * @package     Centurion_Rest
 * @subpackage  Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 * @deprecated
 */
class Centurion_Rest_Controller_CRUD extends Centurion_Rest_Controller
{
    const FILTER_RADIO = 'radio';
    const FILTER_CHECKBOX = 'checkbox';
    const FILTER_CHECKBOX_EXACT = 'checkbox_exact';
    const FILTER_TEXT = 'input';
    const FILTER_TEXT_EXACT = 'input_exact';
    const FILTER_DATE = 'date';
    const FILTER_RECORD = 'record';
    const FILTER_EXPR = 'expr';
    const FILTER_CUSTOM = 'custom';

    const FORMAT_LINK = 'link';
    const FORMAT_SELECT = 'select';

    /**
     * Model form class name.
     *
     * @var string
     */
    protected $_formClassName = null;

    /**
     * Display list.
     *
     * @var array
     */
    protected $_displays = array();
    /**
     * Extra display list that is not a field in the row but a special get.
     *
     * @var array
     */
    protected $_extras = array();
    /**
     * Filter list.
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * Default sort column
     *
     * @var string
     */
    protected $_sortCol = null;

    /**
     * Default sort order
     *
     * @var string
     */
    protected $_sortOrder = null;

    /**
     * Model table.
     *
     * @var Centurion_Db_Table_Model_Abstract
     */
    protected $_model = null;

    /**
     * List of cache's tagname to clean when a crud action is done
     *
     * @var array
     */
    protected $_cacheTagName = array();

    /**
     *
     */
    protected $_links = array();

    /**
     * Format special columns for easy switch or link
     *
     * @var array
     */
    protected $_format = array();

    /**
     *
     * @var unknown_type
     */
    protected $_extraParam = array();

    protected $_actions = array(array('label' => 'Delete', 'value' => 'delete'));

    protected $_select = null;

    protected $_sortColumnsList = array();

    /*protected $_firstRow = array(
        'label'         => null,
        'title'         => null,
        'subtitle'      => null,
        'cover'         => null,
        'sort'          => null,
        'sortAddRelated'=> null
    );*/

    public function resolveColFirstRow($select)
    {
        if (null !== $this->_firstRow['sortAddRelated']) {
            $select->addRelated($this->_firstRow['sortAddRelated']);
        }
        if (null === $this->_firstRow['sort'])
            return $this->_firstRow['title'];

        return $this->_firstRow['sort'];
    }

    public function firstRow($row)
    {
        return $this->view->renderToString('centurion/_first_row.phtml',
                                           array('extraParam' => $this->_extraParam,
                                                 'row'        => $row,
                                                 'first'      => $this->_firstRow,
                                                 'controller' => $this->_request->getControllerName(),
                                                 'module'     => $this->_request->getModuleName()));
    }

    public function init()
    {
        parent::init();

        $this->_links['Edit properties'] = $this->view->url(array_merge($this->_extraParam,
                                                                        array('controller'   =>  $this->_request->getControllerName(),
                                                                              'module'       =>  $this->_request->getModuleName(),
                                                                              'id'           =>  '###id###')));
        $this->_links['Delete'] = array('params' => array('class="zf-actions-delete"'),
                                        'url'    => $this->view->url(array_merge($this->_extraParam,
                                                                                 array('controller'   =>  $this->_request->getControllerName(),
                                                                                       'module'       =>  $this->_request->getModuleName(),
                                                                                       'id'           =>  '###id###'))));
        if (null !== $this->_firstRow['label']) {
            $this->_extras['firstRow'] = $this->_firstRow['label'];
            $this->_sortColumnsList = array_merge(array('firstRow' => null), $this->_sortColumnsList);
        }
    }

    public function preDispatch()
    {
        parent::preDispatch();

        if ($this->_hasParam('model'))
            $this->_model = Centurion_Db::getSingletonByClassName($this->_getParam('model'));

        $this->view->controller = $this->_request->getControllerName();
        $this->view->module = $this->_request->getModuleName();

        $id = $this->_getParam('id', 0);
        $form = $this->_getForm();
        if ($id > 0) {
            $action = $this->_helper->url->url(array_merge($this->_extraParam,
                                                           array('controller' => $this->_request->getControllerName(),
                                                                 'id'         => $id,
                                                                 'action'     => 'put',
                                                                 'module'     => $this->_request->getModuleName())), null, true);
            $object = $this->_getModel()->find($id)
                                        ->current();
            if (null === $object) {
                    throw new Zend_Controller_Dispatcher_Exception(sprintf('Invalid action specified (%s)', $id));
            }

            if (!$form->hasInstance())
                $form->setInstance($object);

            $form->addElement('hidden', '_method', array('value' => 'put'));
        } else {
            $action = $this->_helper->url->url(array_merge($this->_extraParam,
                                                       array('controller' => $this->_request->getControllerName(),
                                                             'module'     => $this->_request->getModuleName())));
            $form->removeElement('id');
        }

        $form->cleanForm();
        $form->setAction($action);
    }

    public function getAction()
    {
        $this->_preGet();


        $this->view->form = $this->_getForm();
        $this->view->extraParam = $this->_extraParam;

        $this->_postGet();


        $this->renderIfNotExists('centurion/get', null, true);
    }

    public function putAction()
    {
        $id = $this->_getParam('id');
        $object = $this->_getModel()->find($id)->current();
        $posts = $this->_request->getPost();
        $this->_getForm()->setInstance($object);
        $this->_processValues($posts);
    }

    public function postAction()
    {
        $posts = $this->_request->getPost();
        $this->_getForm()->removeElement('id');
        $this->_processValues($posts);
    }

    public function deleteAction($rowset = null)
    {
        if (null === $rowset) {
            $id = array($this->_getParam('id', 0));
            $rowset = $this->_getModel()->find($id);
        }

        if (!$rowset->count()) {
            throw new Zend_Controller_Action_Exception(sprintf('Object with type %s and id(s) %s not found',
                                                               get_class($this->_getModel()),
                                                               implode(', ', $id)),
                                                       404);
        }

        foreach ($rowset as $key => $row) {
            $this->_preDelete($row);
            $row->delete();
            $this->_postDelete($row);
        }

        $this->_cleanCache();
        $this->getHelper('redirector')->gotoRoute(array_merge(array(
            'controller' => $this->_request->getControllerName(),
            'module'     => $this->_request->getModuleName(),
            'id'         => 'index'
        ), $this->_extraParam), null, true);
    }

    public function newAction()
    {
        $this->_forward('get');
    }

    public function switchAction()
    {
        $id = $this->_getParam('id');
        $object = $this->_getModel()->find($id)->current();
        $this->_preSwitch($object);

        $object->{$this->_getParam('column')} = (int) !$object->{$this->_getParam('column')};

        $object->save();

        $this->_postSwitch($object);
        $this->_cleanCache();
        $this->_helper->json(array('message' => 'ok', 'newValue' => $object->{$this->_getParam('column')}));
    }

    public function listAction()
    {
        if ($this->_request->isXmlHttpRequest()) {
            $pageId = $this->_getParam('page', 1);
            $limit = $this->_getParam('limit', 10);
            $sortCol = $this->_getParam('sortCol', 1);
            $sortOrder = $this->_getParam('sortOrder', 'asc');
            $whereParam = $this->_getParam('where', '');
            $modelTable = $this->_getModel();
            $modelTableName = $modelTable->info(Centurion_Db_Table_Abstract::NAME);

            $where = $this->_getSelect();

            if (get_magic_quotes_gpc())
                $whereParam = stripslashes($whereParam);
            $whereParam = (array) json_decode($whereParam);


            foreach ($whereParam as $param => $values) {
                if (isset($this->_filters[$param]['type']) && $this->_filters[$param]['type'] === self::FILTER_EXPR) {
                    foreach ($values as $key => $value) {
                        $where->filter($this->_filters[$param]['data'][$value]['expr']);
                    }

                    unset($whereParam[$param]);
                }

                if (isset($this->_filters[$param]) && $this->_filters[$param]['type'] === self::FILTER_CUSTOM && is_callable($this->_filters[$param]['callback'])) {
                    $where = call_user_func($this->_filters[$param]['callback'], $where, $values, $param);

                    unset($whereParam[$param]);
                }

                if (isset($this->_filters[$param]['method']) && is_callable($this->_filters[$param]['method'])) {
                    call_user_func_array($this->_filters[$param]['method'], array($where, $values));

                    unset($whereParam[$param]);
                }
            }


            $where->filter($whereParam);

            $cols = array();
            $naturals = $modelTable->info(Centurion_Db_Table_Abstract::COLS);
            $queryCols = $where->getColumnsName();
            foreach ($this->_displays as $col => $value) {
                if (is_array($value)) {
                    if ($value['expr'] instanceof Zend_Db_Expr) {
                        $cols[] = $value['expr'];
                    }
                } else {
                    if (in_array($col, $naturals)) {
                        if (!$where->isInQuery($col, $modelTableName)) {
                            $cols[] = new Zend_Db_Expr(sprintf(sprintf('%s.%s',
                                                           $modelTable->getAdapter()->quoteIdentifier($modelTableName),
                                                           $modelTable->getAdapter()->quoteIdentifier($col))));
                        }
                    } else {
                        if (!in_array($col, $queryCols)) {
                            $cols[] = new Zend_Db_Expr(sprintf('%s AS %s',
                                                               $where->addRelated($col),
                                                               $modelTable->getAdapter()->quoteIdentifier($col)));
                        }
                    }
                }
            }

            $where->columns($cols);

            if (in_array($sortCol, array_keys($this->_extras)) && method_exists($this, ('resolveCol'.ucfirst($sortCol)))) {
                $sortCol = $this->{'resolveCol' . ucfirst($sortCol)}($where);
                $where->order(sprintf('%s %s', $sortCol, $sortOrder));
            } else {
                if ($sortCol !== 1 && (in_array($sortCol, array_keys($this->_displays)))) {
                    $where->order(sprintf('%s %s', $sortCol, $sortOrder));
                }
            }

            $this->_preList();

            $adapter = new Zend_Paginator_Adapter_DbTableSelect($where);
            $paginator = new Zend_Paginator($adapter);
            $paginator->setCurrentPageNumber($pageId)
                      ->setItemCountPerPage($limit);

            $result = array('pageId'    => $paginator->getCurrentPageNumber(),
                            'nbPages'   => $paginator->count(),
                            'nbRows'    => $paginator->getTotalItemCount(),
                            'rows'      => array());

            foreach ($paginator->getCurrentItems() as $modelRow) {
                $tempArray = $modelRow->toArray();
                foreach($this->_extras as $key => $extra) {
                    if (method_exists($this, $key)) {
                        $tempArray[$key] = $this->{$key}($modelRow);
                    } elseif (method_exists($modelRow, $key)) {
                        $tempArray[$key] = call_user_func(array($modelRow, $key));
                    } else {
                        $tempArray[$key] = $modelRow->{$key};
                    }
                }

                array_push($result['rows'], array('id'      => $modelRow->pk,
                                                  'cell'    => $tempArray));
            }

            $result = $this->_postList($result);

            $json = Zend_Json::encode($result);
            $this->getResponse()->appendBody($json);
        }
    }

    public function indexAction()
    {
        $this->view->params = $this->_getAllParams();
        $this->view->cols = $this->_getModel()->info('cols');
        $this->view->displays = $this->_displays;
        $this->view->extras = $this->_extras;
        $this->view->filters = $this->_filters;
        $this->view->sortCol = ($this->_sortCol ? $this->_sortCol : null);
        $this->view->sortOrder = ($this->_sortOrder ? $this->_sortOrder : null);
        $this->view->format = $this->_format;
        $this->view->actions = $this->_actions;
        $this->view->links = $this->_links;
        $this->view->extraParam = $this->_extraParam;
        $this->view->sortColumnsList = $this->_sortColumnsList;

        $this->renderIfNotExists('centurion/index', null, true);
    }

    public function actionAction()
    {
        if ($this->getRequest()->isPost()) {
            $method = sprintf('%sAction', $this->getRequest()->getPost('actions'));

            if (!method_exists($this, $method))
                return $this->getHelper('redirector')->gotoRoute(array('action' => 'index',
                                                                       'controller' => $this->_request->getControllerName(),
                                                                       'module' => $this->_request->getModuleName()), null, true);

            $id = $this->_getParam('id', 0);
            $rowset = $this->_getModel()->find($id);

            call_user_func_array(array($this, $method), array($rowset));
        }
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
    protected function _cleanCache()
    {
        Centurion_Signal::factory('clean_cache')->send($this, array(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_merge($this->_cacheTagName,
                                      array(sprintf('CRUD_%s', $this->_getModel()->info(Centurion_Db_Table_Abstract::NAME))))));
    }

    protected function _processValues($values)
    {
        if ($this->_getForm()->isValid($values)) {
            $this->_preSave();
            $object = $this->_getForm()->save();
            $this->_postSave();

            $this->_cleanCache();
            $url = null;

            if (isset($values['_save']))
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'id'         => 'index');
            else if (isset($values['_addanother']))
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'id'         => 'new');
            else if (isset($values['_continueto']))
                $url = $this->_request->getParam('continueurl', null);
            else if ($this->_hasParam('next', false))
                $url = $this->_getParam('next', null);
            else
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'id'         => $object->id);

            if (null === $url) {
                $url = $this->_helper->url->url(array_merge($this->_extraParam, $params), null, true);
            }

            if (!$this->getRequest()->isXmlHttpRequest()) {
                $this->_response->setRedirect($url);
                return;
            }
        }

        $this->_getForm()->populate($values);
        $this->_forward('get');
        //Force the run, whereas we will loose the form if we wait for another dispatch loop.
        $this->run();
    }

    /**
     * @return Centurion_Form
     */
    protected function _getForm()
    {
        if (null === $this->_form) {
            $this->_form = new $this->_formClassName(array('method' => Centurion_Form::METHOD_POST));
        }

        return $this->_form;
    }

    /**
     *
     * @return Centurion_Db_Table_Abstract
     */
    protected function _getModel()
    {
        if (null === $this->_model) {
            $this->_model = $this->_getForm()->getModel();
        }

        return $this->_model;
    }

    /**
     * @return Centurion_Db_Table_Select
     */
    protected function _getSelect()
    {
        if (null === $this->_select) {
            $this->_select = $this->_getModel()->select(true);
        }

        return $this->_select;
    }

    protected function _preGet()
    {
    }

    protected function _postGet()
    {
    }

    protected function _preDelete($row)
    {
    }

    protected function _postDelete($row)
    {
    }

    protected function _preSwitch($row)
    {
    }

    protected function _postSwitch($row)
    {
    }

    protected function _preList()
    {
    }

    protected function _postList($result)
    {
        return $result;
    }

    protected function _preSave()
    {
    }

    protected function _postSave()
    {
    }
}
