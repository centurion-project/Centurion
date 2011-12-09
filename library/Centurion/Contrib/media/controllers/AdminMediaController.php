<?php

class Media_AdminMediaController extends Centurion_Controller_CRUD
{
    protected $_itemPerPage = 21;

    public function showImage($row)
    {
        return ''; //<img src="'.$row->getStaticUrl(array('cropcenterresize' => array('width' => 174, 'height' => 94))).'" height="94" width="174" alt="" class="picture">';
    }

    public function init()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
        $this->_helper->layout->setLayout('admin');

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
            )
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

    protected function _getForm()
    {
        if (null == $this->_form) {
            parent::_getForm();
            $this->_form->setMethod('post');
        }

        return $this->_form;
    }
}