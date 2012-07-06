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
class Admin_AdminNavigationController extends Centurion_Controller_Mptt implements Translation_Traits_Controller_CRUD_Interface
{
    protected $_rootNode = null;

    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');

        parent::preDispatch();
    }

    public function init()
    {
        $this->_model = Centurion_Db::getSingleton('core/navigation');

        $this->_formClass = 'Admin_Form_Model_Navigation';

        $this->setOptions(array(
                               'titleColumn'        =>  'title',
                               'publishColumn'      =>  'is_published',
                               'publishDateColumn'  =>  'published_at',
                          ));

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage navigation'));
        $this->view->placeholder('headling_1_add_button')->set($this->view->translate('navigation'));

        parent::init();
    }
    /*
    public function indexAction()
    {
        if ($rootId = $this->_getParam('navitation_id', null)) {
            $this->view->tree = $this->_model->fetchAll(array('id=?' => $rootId));
        }

        $this->view->root = $this->_model->getRootNodes()->order(array(Core_Traits_Mptt_Model_DbTable::MPTT_TREE, Core_Traits_Mptt_Model_DbTable::MPTT_LEFT))->fetchAll();

        $this->renderIfNotExists('mptt/index', null, true);
    }*/

    public function indexAction()
    {
        $this->view->type = $this->_getParam('type', 'normal');
        $this->view->types = Centurion_Config_Manager::get('admin.navigation.types');
        
        if (($this->_getParam('id') != null) && (!isset($this->view->tree))) {
            $result = $this->_model->findOneById(str_replace('page-', '', $this->_getParam('id')));
            
            if ($result != null)
                $this->view->tree = $result->getChildren();
        }
        
        $this->view->deepLevel = 3;
        
        parent::indexAction();
    }

    public function editAction()
    {
        $row = $this->_helper->getObjectOr404($this->_model, array('id' => $this->_getParam('id')));
        $types = Centurion_Config_Manager::get('admin.navigation.types');

        if ($row->proxy !== null) {
            foreach ($types as $type) {
                if (isset($type['className']) && $row->proxy->getTable() instanceof $type['className']) {
                    if (isset($type['urlEdit'])) {
                        $type['urlEdit']['_next'] = $this->view->url(array('action' => 'index'));
                        $type['urlEdit']['id'] = $row->proxy->id;
                        $this->_redirect($this->view->url($type['urlEdit']));
                        die();
                    }
                }
            }
        }

        parent::editAction();
    }

    public function addProxyAction()
    {
        //TODO: check Content type and id with config
        $proxyPk = $this->_getParam('proxy_pk');
        $proxyModel = $this->_getParam('proxy_model');

        list($contentType, ) = Centurion_db::getSingleton('core/content_type')->getOrCreate(array('name' => $proxyModel));

        $instance = Centurion_Db::getSingletonByClassName($this->_getParam('proxy_model'))->findOneById($proxyPk);

        if (!($instance instanceof Core_Model_DbTable_Row_Navigable_Interface)) {
            throw new Centurion_Controller_Action_Exception('Not an navigable object');
        }

        $navigation = $this->_model->createRow();
        list($navigation, $created) = $this->_model->getOrCreate(array('proxy_model' => $contentType->id, 'proxy_pk' => $proxyPk));
        
        if ($proxyPk) {
            $navigation->is_visible = $instance->isPublished();
            $navigation->save();
            if (isset($this->_rootNode))
                $navigation->moveTo($this->_rootNode, Core_Traits_Mptt_Model_DbTable::POSITION_LAST_CHILD);
        }
        
        $this->_redirect($this->view->url(array('controller' => $this->getRequest()->getControllerName(), 'module' => $this->getRequest()->getModuleName(), 'action' => 'index', 'proxy_pk' => null, 'proxy_model' => null), 'default'));
        die();
    }
}
