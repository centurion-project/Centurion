<?php

class Cms_FlatpageController extends Centurion_Controller_Action
{
    public function getAction()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
        $flatpageRow = $this->_helper->getObjectOr404('cms/flatpage', array('id'                    =>  $this->_getParam('id'),
                                                                            'is_published'          =>  1,
                                                                            'published_at__lt'      =>  new Zend_Db_Expr('NOW()')));
        Centurion_Db_Table_Abstract::restoreFiltersStatus();

        Centurion_Signal::factory('pre_display_rte')->send($this, array($flatpageRow));

        Centurion_Cache_TagManager::addTag($flatpageRow);
        
        $navRow = Centurion_Db::getSingleton('core/navigation')->findOneByProxy($flatpageRow);
        if (null !== $navRow) {
            $navigation = $this->view->navigation()->getContainer();
            $this->view->currentNavigation = $navigation->findOneById($navRow->id);
        }

        return $this->renderToResponse($flatpageRow->flatpage_template->view_script, array('flatpageRow' => $flatpageRow));
    }

    public function getBySlugAction()
    {
        $flatpageRow = $this->_helper->getObjectOr404('cms/flatpage', array('id'                    =>  $this->_getParam('id'),
                                                                            'is_published'          =>  1,
                                                                            'published_at__lt'      =>  new Zend_Db_Expr('NOW()')));

        Centurion_Signal::factory('pre_display_rte')->send($flatpageRow, array($flatpageRow));

        Centurion_Cache_TagManager::addTag($flatpageRow);
        return $this->renderToResponse($flatpageRow->flatpage_template->view_script, array('currentFlatpageRow' => $flatpageRow));
    }
}