<?php

class Media_AdminMediaController extends Centurion_Controller_CRUD
{
    protected $_itemPerPage = 21;

    public function showImage($row)
    {
        return ''; //<img src="'.$row->getStaticUrl(array('cropcenterresize' => array('width' => 174, 'height' => 94))).'" height="94" width="174" alt="" class="picture">';
    }

    public function displayInfos($row)
    {
        $count = $row->countTimesUsed();
        if(!is_numeric($count)) {
            return null;
        }
        if(0 == $count) return $this->view->translate('Never used');
        if(1 == $count) return $this->view->translate('Used only once');
        return $this->view->translate('Used in %s places', $count);
    }

    public function displayDuplicates($row)
    {
        $count = $row->getTable()->select(true)->filter(array('sha1'=>$row->sha1))->count();
        return $count;
    }

    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');

        $this->view->displayDuplicates = !$this->_getParam('duplicates', null);

        $this->_model = Centurion_Db::getSingleton('media/file');
        $this->_formClassName = 'Media_Form_Model_Admin_File2';
        $this->_layout = 'media';

        $this->_displays = array(
           'image' => array(
                    'label' => $this->view->translate('Image'),
                    'type' => Centurion_Controller_CRUD::COLS_CALLBACK,
                    'callback' => array($this, 'showImage')
            ),
            'filename' => array(
                    'label' => '',
            ),
           'infos' => array(
                    'label' => $this->view->translate('Infos'),
                    'type' => Centurion_Controller_CRUD::COLS_CALLBACK,
                    'callback' => array($this, 'displayInfos')
            ),
           'duplicates' => array(
                    'label' => $this->view->translate('Duplicates'),
                    'type' => Centurion_Controller_CRUD::COLS_CALLBACK,
                    'callback' => array($this, 'displayDuplicates')
            ),
            'sha1'      => $this->view->translate('sha')
        );

        //TODO: behaviour => behavior

        $this->_filters = array(
                'filename'      =>  array('type' =>  self::FILTER_TYPE_TEXT,
                                      'behavior'=>  self::FILTER_BEHAVIOR_CONTAINS,
                                      'label'   =>  $this->view->translate('File name')),
                'tags' => array('type' =>  self::FILTER_TYPE_CHECKBOX,
                                      'behavior'=>  self::FILTER_BEHAVIOR_IN,
                                      'label'   =>  $this->view->translate('Tags')),
            );

        $this->view->placeholder('headling_1_content')->set($this->view->translate('Manage media'));

        parent::init();
    }

    public function getSelectFiltred()
    {
        $select = parent::getSelectFiltred();
        $select->reset(Centurion_Db_Table_Select::GROUP);
        $select->filter(array('!id'=>'88888888')); // don't list our empty pixel, @todo make a general rule for unlistable media
        if($this->_getParam('duplicates', null)) {
            $select->filter(array('sha1'=>$this->_getParam('duplicates')));
        }
        else {
            $select->group('sha1');
        }
        return $select;
    }

    protected function _getForm()
    {
        if (null == $this->_form) {
            parent::_getForm();
            $this->_form->setMethod('post');
        }

        return $this->_form;
    }
    public function deleteAction($rowset = null) {
        try {
            return parent::deleteAction($rowset);
        }
        catch(Zend_Db_Statement_Exception $e) {
            $this->view->error = $this->view->translate('Some media could not be deleted. They probably are in use somewhere else');
            return $this->_forward('index');
        }
    }
}
