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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Centurion_Controller_Mptt extends Centurion_Controller_Action
{
    const POSITION_BEFORE = 'before';
    const POSITION_AFTER = 'after';
    const POSITION_INSIDE = 'inside';
    const POSITION_LAST = 'last';

    /**
     * Model form.
     *
     * @var Centurion_Form_Model_Abstract
     */
    protected $_form = null;

    /**
     * DbTable.
     *
     * @var Centurion_Db_Table_Abstract
     */
    protected $_model = null;

    /**
     * Form class.
     *
     * @var string
     */
    protected $_formClass = null;

    /**
     * Select.
     *
     * @var Centurion_Db_Table_Select
     */
    protected $_select = null;

    /**
     * Title column for creation.
     *
     * @var string
     */
    protected $_titleColumn = null;

    /**
     * Publish column.
     *
     * @var string
     */
    protected $_publishColumn = null;

    /**
     * Date column.
     *
     * @var string
     */
    protected $_publishDateColumn = null;

    /**
     * Form metadata and attributes
     * @var array
     */
    protected $_attribs = array();

    protected $_cacheTagName = array();

    protected $_recursiveDelete = true;

    
    /**
     * @var array extra param to pass to each request
     */
    protected $_extraParam = array();

    public function init()
    {
        $this->view->infos = array();
        $this->view->errors = array();
        
        parent::init();

        $this->view->formViewScript = array();
        $this->getHelper('ContextAutoSwitch')->direct(array('index', 'create', 'delete'));

        $this->getRequest()->setParams($this->getHelper('params')->direct());

        if ($this->getRequest()->getParam('saving') == 'done') {
            $this->view->infos[] = $this->view->translate('Saving has been done.');
            $this->getRequest()->setParam('saving', null);
        }
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $this->view->assign(array(
            'controllerName'    =>  $this->getRequest()->getControllerName(),
            'actionName'        =>  $this->getRequest()->getActionName(),
            'moduleName'        =>  $this->getRequest()->getModuleName(),
            'titleColumn'       =>  $this->_titleColumn,
            'publishColumn'     =>  $this->_publishColumn,
            'publishDateColumn' =>  $this->_publishDateColumn,
            'parentColumn'      =>  Core_Traits_Mptt_Model_DbTable::MPTT_PARENT,
            'attribs'           =>  $this->getAttribs()
        ));
    }

    public function indexAction()
    {
        //$treeAttribute = $this->_model->getAttribute(constant(sprintf('%s::MPTT_TREE', get_class($this->_model))));
        if (!isset($this->view->tree)) {
            $this->view->tree = $this->_model->getRootNodes()
                                                                 ->order(array(Core_Traits_Mptt_Model_DbTable::MPTT_TREE, Core_Traits_Mptt_Model_DbTable::MPTT_LEFT))->fetchAll();
        }
        
        if ($this->_getParam('format') == 'json') {
            $this->getHelper('AjaxContext')->initContext();
            $this->getHelper('layout')->disableLayout();
        }
        
        $this->renderIfNotExists('mptt/index', null, true);
    }

    public function getModel()
    {
        return $this->_model;
    }

    protected function _recursiveDelete($row)
    {
        $children = $row->getDescendants();
        if (null != $children) {
            foreach ($children as $child) {
                $child->setReadOnly(false);
                $child->delete();
            }
        }
    }
    public function deleteAction()
    {
        $row = $this->_helper->getObjectOr404($this->_model, array(
            'id'    =>  $this->_getParam('id')
        ));

        $this->_preDelete($row);

        $row->setReadOnly(false);
        $row->delete();

        $this->_postDelete($row);

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->getHelper('redirector')->setPrependBase(false)->gotoSimple('index', $this->getRequest()->getControllerName(), $this->getRequest()->getModuleName(), $this->_request->getParams());
        } else {
            $this->getHelper('AjaxContext')->initContext();
            $this->getHelper('layout')->disableLayout();
            $this->getHelper('ViewRenderer')->setNoRender();
        }
        $this->renderIfNotExists('mptt/delete', null, true);
    }

    public function getAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $row = $this->_helper->getObjectOr404($this->_model, array('id' => $this->_getParam('id')));

        $this->view->form = $this->_getForm(array(), $row);
        $this->_processValues();

        $this->_formViewScript = 'mptt/edit.phtml';
        $this->_renderForm($this->view->form);
    }

    public function newAction()
    {
        $this->_forward('create');
//        $form = $this->_getForm();
//
//        $form->populate($this->_request->getParam(Centurion_Controller_CRUD::PARAM_FORM_VALUES, array()));
//        $this->createAction();
//
//        $this->render('create');
    }

    public function createAction()
    {
        $this->view->form = $this->_getForm();
        $this->view->form->populate($this->_request->getParam(Centurion_Controller_CRUD::PARAM_FORM_VALUES, array()));
        $this->_processValues();

        $this->_formViewScript = 'mptt/create.phtml';
        $this->_renderForm($this->view->form);
    }

    public function moveAction()
    {
        $this->getHelper('AjaxContext')->initContext();
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();

        $row = $this->_helper->getObjectOr404($this->_model, array('id' => $this->_getParam('id')));

        $refererFlatpageRow = $this->_helper->getObjectOr404($this->_model, array('id' => $this->_getParam('referer')));

        switch ($type = $this->_getParam('type', self::POSITION_BEFORE)) {
            case self::POSITION_BEFORE:
                $row->moveTo($refererFlatpageRow, Core_Traits_Mptt_Model_DbTable::POSITION_LEFT);
                break;
            case self::POSITION_AFTER:
                $row->moveTo($refererFlatpageRow, Core_Traits_Mptt_Model_DbTable::POSITION_RIGHT);
                break;
            case self::POSITION_INSIDE:
            case self::POSITION_LAST:
               $row->moveTo($refererFlatpageRow, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD);

//                $row->setParentId($refererFlatpageRow->pk);
//                $row->save();
                break;
            default:
                $row->moveTo();
                //$this->forward404(sprintf('Type %s is unknown', $type));
        }
    }


    /**
     * Import from Centurion_Controller_CRUD to call trait to overload form rendering
     */
    public function _preRenderForm()
    {
        Centurion_Traits_Common::checkTraitOverload($this, '_preRenderForm', array(), false);
    }

    /**
     * Variant of renderIfNotExists, used by create/new and edit/get
     * to support Traits for CRUD Controller in MPTT controlelr (They can overload the view to use)
     * @param string $action : default view to use if the action does not exist
     *
     * @todo use renderToResponse instead
     */
    protected function _renderForm($form)
    {
        $this->view->form = $form;
        $this->view->formViewScript[] = 'grid/_form.phtml';

        $this->_preRenderForm();

        $script = substr($this->view->selectScript(array($this->_formViewScript, 'grid/form.phtml')), 0, -6);
        //$script = substr($this->view->selectScript(array(sprintf('%s/form.phtml', $this->_request->getControllerName()), 'grid/form.phtml')), 0, -6);
        $this->render($script, true, true);
    }

    /**
     * Set controller state from options array
     *
     * @param  array $options
     * @return Centurion_Controller_Mptt
     */
    public function setOptions(array $options)
    {
        if (isset($options['titleColumn'])) {
            $this->_titleColumn = $options['titleColumn'];
            unset($options['titleColumn']);
        }

        if (isset($options['publishColumn'])) {
            $this->_publishColumn = $options['publishColumn'];
            unset($options['publishColumn']);
        }

        if (isset($options['publishDateColumn'])) {
            $this->_publishDateColumn = $options['publishDateColumn'];
            unset($options['publishDateColumn']);
        }

        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->setAttrib($key, $value);
            }
        }

        return $this;
    }

    /**
     * Set controller state from config object
     *
     * @param  Zend_Config $config
     * @return Centurion_Controller_Mptt
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set controller attribute
     *
     * @param  string $key
     * @param  mixed $value
     * @return Centurion_Controller_Mptt
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;

        return $this;
    }

    /**
     * Add multiple controller attributes at once
     *
     * @param  array $attribs
     * @return Centurion_Controller_Mptt
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }

        return $this;
    }

    /**
     * Set multiple controller attributes at once
     *
     * Overwrites any previously set attributes.
     *
     * @param  array $attribs
     * @return Centurion_Controller_Mptt
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();

        return $this->addAttribs($attribs);
    }

    /**
     * Retrieve a single controller attribute
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    /**
     * Retrieve all controller attributes/metadata
     *
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Remove attribute
     *
     * @param  string $key
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (isset($this->_attribs[$key])) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all controller attributes
     *
     * @return Zend_Form
     */
    public function clearAttribs()
    {
        $this->_attribs = array();

        return $this;
    }

    /**
     * Process the values submit by the request.
     *
     * @param string $url Set the url in success
     * @return Centurion_Controller_Mptt
     */
    protected function _processValues($url = null)
    {
        if ($this->getRequest()->isPost()) {
            $values = $this->getRequest()->getPost();

            if ($this->_getForm()->isValid($values)) {
                $this->_preSave();
                $row = $this->_getForm()->save();
                $this->_postSave();

                if (null === $url) {
                    if ($this->_hasParam('next', false)) {
                        $url = $this->_getParam('next', null);
                    }

                    if (null === $url) {
                        if (isset($values['_save']) || isset($values['_saveBig'])) {
                            $params = array(
                                'module'     => $this->getRequest()->getModuleName(),
                                'controller' => $this->getRequest()->getControllerName(),
                                'action'     => 'index'
                            );
                        } else if (isset($values['_addanother'])) {
                            $params = array(
                                'module'     => $this->getRequest()->getModuleName(),
                                'controller' => $this->getRequest()->getControllerName(),
                                'action'     => 'create'
                            );
                        } else {
                            $params = array(
                                'module'     => $this->getRequest()->getModuleName(),
                                'controller' => $this->getRequest()->getControllerName(),
                                'action'     => 'edit',
                                'id'         => $row->id
                            );
                        }

                        $params['saving'] = 'done';

                        $url = $this->getHelper('url')->url(array_merge($this->_extraParam, $params), 'default', true);
                    }
                }

                if (!$this->getRequest()->isXmlHttpRequest()) {
                    return $this->getHelper('redirector')->setPrependBase(false)->gotoUrl($url);
                }
            } else {
                $this->_getForm()->populate($values);
            }
        }

        return $this;
    }

    /**
     * Retrieve the form attached to the controller.
     *
     * @param Centurion_Db_Table_Row_Abstract $instance
     * @return Centurion_Form_Model_Abstract
     */
    protected function _getForm($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        if (null === $this->_form) {
            if (!isset($options['method'])) {
                $options['method'] = Centurion_Form::METHOD_POST;
            }

            $this->_form = new $this->_formClass($options, $instance);
            $this->_form->cleanForm();
        }

        return $this->_form;
    }

    protected function _cleanCache()
    {
        $tags = array_merge($this->_cacheTagName,
                                      array(sprintf('CRUD_%s', $this->getModel()->info(Centurion_Db_Table_Abstract::NAME))));
        Centurion_Signal::factory('clean_cache')->send($this, array(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, $tags));
    }

    /**
     * @return Centurion_Db_Table_Select
     */
    protected function _getSelect()
    {
        if (null === $this->_select) {
            $this->_select = $this->_model->select(true, true, false);
        }

        return $this->_select;
    }

    public function switchAction()
    {
        $name = $this->_getParam('name');

        try {
            if (preg_match('`(.*)-([^-]*)$`', $name, $matches)) {
                list(, $name, $id) = $matches;

                if ($this->_publishColumn === null || $name !== $this->_publishColumn) {
                    throw new Exception('Wrong request');
                }

                $object = $this->getModel()->find($id)->current();

                $object->{$name} =  !((int)$this->_getParam('value'));
                $object->save();

                $this->_cleanCache();

                $this->_helper->json(array('statut' => 200, 'value' => $object->{$name}));
            } else {
                throw new Exception('Not valid name');
            }
        } catch (Exception $e) {
            $this->_helper->json(array('statut' => 400, 'message' => 'not valid request', 'exception' => $e->getTrace()));
        }
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

    protected function _preSave()
    {
    }

    protected function _postSave()
    {
    }
}
