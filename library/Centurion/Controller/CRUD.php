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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */

class Centurion_Controller_CRUD extends Centurion_Controller_AGL
{
    const PARAM_FORM_VALUES = 'crud_form_values';

    /**
     *
     * @var Centurion_Form_Model_Abstract
     */
    protected $_form = null;

    /**
     * @var bool
     */
    protected $_useTicket = true;

    /**
     * @var string The name of class to instantiate
     */
    protected $_formClassName = null;

    protected $_formViewScript;

    protected $_rowActions;

    protected $_cacheTagName = array();

    protected $_toolbarActions = array();

    protected $_sortable = false;

    protected $_showCheckbox = true;
    
    public function init()
    {
        $this->view->infos = array();
        $this->view->errors = array();
        $this->view->formViewScript = array();

//        $this->getHelper('ContextAutoSwitch')->direct();
//        $this->_request->setParams($this->getHelper('params')->direct());

        $this->_toolbarActions['delete'] = $this->view->translate('Delete');


        $this->_rowActions = array('Edit'   => array('url'           => array(array('id' => '___id___', 'action' => 'get', 'saving' => null)),
                                                     'cls'           => 'ui-icon-pencil',
                                                    ),
                                   'Delete' => array('url'           => array(array('id' => '___id___', 'action' => 'delete', 'saving' => null)),
                                                     'cls'           => 'ui-icon-close',
                                                     'clickCallback' => "return confirm('" . $this->view->translate('Are you sure? This operation can not be undone') . "');",
                                                     )
        );

        $this->view->sortable = $this->isSortable();

        if ($this->isSortable() && $this->_defaultOrder == null)
            $this->_defaultOrder = 'order asc';

        $this->_formViewScript = sprintf('%s/form.phtml', $this->_request->getControllerName());
        
        parent::init();
        
        if ($this->_getParam('saving') == 'done') {
            $this->view->infos[] = $this->view->translate('Saving has been done.');
        }
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

    public function putAction()
    {
        if ($this->getRequest()->isPost()) {
            $id = $this->_getParam('id');
            $model = $this->_getModel();
            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            $object = $model->find($id)->current();
            Centurion_Db_Table_Abstract::restoreFiltersStatus();
            $posts = $this->_request->getPost();
            $this->_getForm()->setInstance($object);
            
            $this->_processValues($posts);
        }
    }

    public function postAction()
    {
        if ($this->getRequest()->isPost()) {
            $posts = $this->_request->getPost();
            $_form = $this->_getForm();
            $_form->removeElement('id');
            //To simulate prePopulate before validate value fixx  #6317
            Centurion_Signal::factory('post_form_pre_validate')->send($_form, array($posts));
            $this->_processValues($posts);
        }
    }

    public function generateList()
    {
        if ($this->_getParam('sorting', false) == true) {
            $this->_itemPerPage = 88888888;

            $this->getSelectFiltred()->reset(Centurion_Db_Table_SELECT::ORDER)->order('order asc');
        }
        
        return parent::generateList();
    }

    /**
     * @return bool
     */
    public function isSortable()
    {
        if ($this->_getModel() instanceof Core_Traits_Order_Model_DbTable_Interface) {
            return true;
        }

        return $this->_sortable;
    }

    public function orderAction($rowset = null)
    {
        if ($this->isSortable()) {
            $order = 0;
            foreach ($rowset as $row) {
                $row->order = $order++;
                $row->save();
            }

            $this->_helper->json(array('message' => 'ok'));
        }
    }

    public function deleteAction($rowset = null)
    {
        if (null === $rowset) {
            $id = array($this->_getParam('id', null));
            $rowset = $this->_getModel()->find($id);
        }
        
        if ($this->_useTicket && !$this->view->ticket()->isValid()) {
            $this->view->errors[] = $this->view->translate('Invalid ticket');
            return $this->_forward('index', null, null, array('errors' => array()));
        }
        
        if (!count($rowset)) {
            throw new Zend_Controller_Action_Exception(sprintf('Object with type %s and id(s) %s not found',
                                                               get_class($this->_getModel()),
                                                               implode(', ', $id)),
                                                       404);
        }

        foreach ($rowset as $key => $row) {
            if ($row->isReadOnly()) {
                //Todo: find why $row is in read only state sometimes !!!
                $row->setReadOnly(false);
            }
            $this->_preDelete($row);
            $row->delete();
            $this->_postDelete($row);
        }

        $this->_cleanCache();

        if ($this->_hasParam('_next', false)) {
            $url = urldecode($this->_getParam('_next', null));
            return $this->_response->setRedirect($url);
        }

        $this->getHelper('redirector')->gotoRoute(array_merge(array(
            'controller' => $this->_request->getControllerName(),
            'module'     => $this->_request->getModuleName(),
            'action'         => 'index'
        ), $this->_extraParam), null, true);
    }

    public function debug($str, $reset = false)
    {
        if ($reset) {
            $this->_debug = '';
        }
        $this->_debug .= $str;
    }

    protected function _getSelect()
    {
        if (null === $this->_select) {
            $this->_select = $this->_getModel()->select(true);
        }

        return $this->_select;
    }

    /**
     * @return Centurion_Form_Model_Abstract
     */
    protected function _getForm()
    {
        return $this->getForm();
    }

    /**
     * @return Centurion_Form_Model_Abstract|null
     * @throws Centurion_Controller_Action_Exception
     */
    public function getForm()
    {
        if (null === $this->_form) {
            if (!$this->_formClassName || !is_string($this->_formClassName))
                throw new Centurion_Controller_Action_Exception("Empty or invalid property _formClassName");

            $this->_form = new $this->_formClassName(array('method' => Centurion_Form::METHOD_POST));
            if(method_exists($this->_form, 'setDateFormat')){ //For form whom not inherits of Centurion_Form_Model
                $this->_form->setDateFormat($this->_dateFormatIso, $this->_timeFormatIso);
            }
            $this->_form->cleanForm();
            $action = $this->_helper->url->url(array_merge($this->_extraParam,
                                                           array('controller' => $this->_request->getControllerName(),
                                                                 'action'     => 'post',
                                                                 'module'     => $this->_request->getModuleName())), null, true);

            $this->_form->setAction($action);
            if ($next = $this->_getParam('_next', null)) {
                $this->_form->addElement('hidden', '_next', array('value' => $next));
            }

            $this->_postInitForm();
        }

        return $this->_form;
    }

    protected function _postGet()
    {
        $this->_getParams();

        if ($this->view->form instanceof Centurion_Form_Model_Abstract && null !== ($instance = $this->view->form->getInstance())) {
            if ($this->_order === 'DESC') {
                $functionNext = 'getNextBy';
                $functionPrevious = 'getPreviousBy';
                $functionNextCount = 'getNextCountBy';
                $functionPreviousCount = 'getPreviousCountBy';
            } else {
                $functionNext = 'getPreviousBy';
                $functionPrevious = 'getNextBy';
                $functionNextCount = 'getPreviousCountBy';
                $functionPreviousCount = 'getNextCountBy';
            }

            $sort = $this->_sort;

            if ($sort == null) {
                $info = $instance->getTable()->info();
                $sort = current($info['primary']);
            } else {
                if (is_array($this->_displays[$sort]) && isset($this->_displays[$sort]['column'])) {
                    $sort = $this->_displays[$sort]['column'];
                }
            }

            $select = $this->getSelectFiltred()->reset(Zend_Db_Select::ORDER);

            $this->view->previous = $instance->{$functionPrevious . $sort}(null, (clone $select));
            $this->view->next = $instance->{$functionNext . $sort}(null, (clone $select));
            $this->view->totalCount = $select->count();
            $this->view->currentCount = $instance->{$functionPreviousCount}($sort, null, (clone $select)) + 1;
        }
    }

    public function switchAction()
    {
        $name = $this->_getParam('name');

        try {
            if (preg_match('`(.*)_([^_]*)`', $name, $matches)) {
                list(, $name, $id) = $matches;

                if (!isset($this->_displays[$name])) {
                    throw new Exception('Wrong request');
                }
                if (!isset($this->_displays[$name]['type']) || $this->_displays[$name]['type'] !== self::COL_TYPE_ONOFF) {
                    throw new Exception('Not onoff column');
                }

                $object = $this->_getModel()->findOneById($id);
                $this->_preSwitch($object, $name);

                if (!isset($this->_displays[$name]['column']))
                    $column = $name;
                else
                    $column = $this->_displays[$name]['column'];

                if (!isset($object->{$column})) {
                    throw new Exception('Column is not in the row');
                }

                $value = (int) $this->_getParam('value');
                if ($value == 1) {
                    $value = 0;
                } else {
                    $value = 1;
                }

                $object->{$column} =  $value;
                $object->save();

                $this->_postSwitch($object);
                $this->_cleanCache();

                $this->_helper->json(array('statut' => 200, 'value' => $object->{$column}));
            } else {
                throw new Exception('Not valid name');
            }
        } catch (Exception $e) {
            $this->_helper->json(array('statut' => 400, 'message' => 'not valid request', 'exception' => $e->getMessage()));
        }
    }

    protected function _postInitForm()
    {
        $id = $this->_getParam('id', null);
        if (null !== $id) {
            Centurion_Db_Table_Abstract::setFiltersStatus(false);
            $form = $this->_getForm();
            $object = $this->_getModel()->find($id)
                                        ->current();
            $action = $this->_helper->url->url(array_merge($this->_extraParam,
                                                           array('controller' => $this->_request->getControllerName(),
                                                                 'id'         => $id,
                                                                 'action'     => 'put',
                                                                 'saving' => null,
                                                                 'module'     => $this->_request->getModuleName())
                                                           ), null, true);
            Centurion_Db_Table_Abstract::restoreFiltersStatus();
            $form->setAction($action);
            if (!$form->hasInstance()) {
                $form->setInstance($object);
            }
        }
    }

    protected function _preGet()
    {

    }

    protected function _preSwitch($row)
    {
    }

    protected function _postSwitch($row)
    {
    }

    protected function _preDelete($row)
    {
    }

    protected function _postDelete($row)
    {
    }

    protected function _preSave()
    {
    }

    protected function _postSave()
    {
    }

    public function _preRenderForm()
    {
        Centurion_Traits_Common::checkTraitOverload($this, '_preRenderForm', array(), false);
    }

    protected function _renderForm($form)
    {
        $this->view->form = $form;
        $this->view->formViewScript[] = 'grid/_form.phtml';

        $this->_preRenderForm();

        $script = substr($this->view->selectScript(array($this->_formViewScript, 'grid/form.phtml')), 0, -6);
        //$script = substr($this->view->selectScript(array(sprintf('%s/form.phtml', $this->_request->getControllerName()), 'grid/form.phtml')), 0, -6);
        $this->render($script, true, true);
    }

    public function newAction()
    {
        $form = $this->_getForm();

        $form->populate($this->_request->getParam(self::PARAM_FORM_VALUES, array()));

        $form->removeElement('id');

        $this->_renderForm($form);
    }

    public function getAction()
    {
        $this->_preGet();

        $form = $this->_getForm();
        if (!$form->hasInstance()) {
                throw new Zend_Controller_Dispatcher_Exception(sprintf('Invalid id specified'));
            }

        $this->_postGet();

        $this->_renderForm($form);
    }

    public function bashAction()
    {
        $action = $this->_getParam('event', 'index');

        if (!method_exists($this, $action . 'Action')) {
            $this->_forward('index');
        }

        $permission = sprintf('%s_%s_%s', $this->getRequest()->getModuleName(),
            $this->getRequest()->getControllerName(),
            strtolower($action));

        $this->_helper->aclCheck($permission);

//        if (!$this->_helper->getHelper('ticket')->isValid()) {
//            return $this->_forward('index', null, null, array('errors' => array($this->view->translate('Invalid ticket'))));
//        }
        
        $rowset = array();
        $ids = (array) $this->_getParam('rowId');
        
        foreach ($ids as $id) {
            $rowset[] = $this->_getModel()->findOneById($id);
        }

        $this->{$action . 'Action'}($rowset);
    }

    protected function _cleanCache()
    {
        Centurion_Signal::factory('clean_cache')->send($this, array(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_merge($this->_cacheTagName,
                                      array(sprintf('CRUD_%s', $this->_getModel()->info(Centurion_Db_Table_Abstract::NAME))))));
    }

    protected function _processValues($values)
    {
        if ($this->getRequest()->isPost() && $this->_getForm()->isValid($values)) {
            $this->_preSave();
            $object = $this->_getForm()->save();
            $this->_postSave();

            $this->_cleanCache();
            $url = null;

            if ($this->_hasParam('_next', false))
                $url = urldecode($this->_getParam('_next', null));
            else if (isset($values['_save']) || isset($values['_saveBig']))
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'action'     => 'index',
                                'id'         => null,
                );
            else if (isset($values['_addanother']))
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'action'     => 'new',
                                'id'         => null,
                );
            else if (isset($values['_continueto']))
                $url = $this->_request->getParam('_continueurl', null);
            else
                $params = array('module'     => $this->_request->getModuleName(),
                                'controller' => $this->_request->getControllerName(),
                                'action'     => 'get',
                                'id'         => $object->id,
                                'saving'     => 'done');

            if (null === $url) {
                $params['saving'] = 'done';
                
                $url = $this->_helper->url->url(array_merge($this->_extraParam, $params), null, true);
            }

            if (!$this->getRequest()->isXmlHttpRequest()) {
                if (method_exists($this->_getForm(), 'getInstance')) {
                    $instance = $this->_getForm()->getInstance();
                    $url = str_replace(array('___pk___', '___model___'), array($instance->id, get_class($instance->getTable())), $url);
                }

                return $this->_response->setRedirect($url);
            }
        } else {
            if (null != ($element = $this->_getForm()->getElement('_XSRF')) && $element->hasErrors()) {
                $this->view->errors[] = $this->view->translate('Form hasn\'t been save. Maybe you have waiting to much time. Try again.');
            } else {
                $this->view->errors[] = $this->view->translate('An error occur when validating the form. See below.');
            }
        }

        $this->view->form = $this->_getForm();

        $script = substr($this->view->selectScript(array(sprintf('%s/form.phtml', $this->_request->getControllerName()),
                                         'grid/form.phtml')), 0, -6);

        $this->render($script, true, true);
    }
    

    /**
     * 
     * Generate Csv Function
     * @param int $itemPerPage
     * @param int $page
     */
    
    public function generateCsvAction($itemPerPage = 0, $page = 0)
    {
        Centurion_Traits_Common::checkTraitOverload($this, 'indexAction', array(), false);

        $this->_getParams();

        $this->_itemPerPage = $itemPerPage;
        $this->_page = $page;
        
        //create headers array 
        $headers = array();
        
        $select = $this->_getSelect();
        $modelTable = $select->getTable();
        $naturals = $modelTable->info(Centurion_Db_Table_Abstract::COLS);
        
        $tabKeyUnset = array();
        foreach ($this->_displays as $key => $options) {    
            if (is_array($options) && $options['type'] !== self::COLS_ROW_COL)
                $tabKeyUnset[] =  $key;
            else
                $headers[] = $options;
        }
        
        $select = $this->generateList();
        
        //unset useless columns
        foreach ($select as $key => $row) {
            unset($row['checkbox']);
            unset($row['row']);
            foreach ($tabKeyUnset as $keyUnset => $rowUnset)
            {
                unset($row[$rowUnset]);
                
            }
            $rowSet[] = $row;
        }        

        //generate csv
       if (null !== $rowSet) {
        $this->getHelper('Csv')->direct($rowSet, $headers);

       }
        
    }
}
