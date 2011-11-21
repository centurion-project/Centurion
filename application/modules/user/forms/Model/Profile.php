<?php

class User_Form_Model_Profile extends Centurion_Form_Model_Abstract
{
    public function __construct($options = array())
    {
        $this->_model = Centurion_Db::getSingleton('user/profile');
        
        $this->_elementLabels = array(
            'nickname'           =>  'Nickname',
            'function'           =>  'Function',
            'about'              =>  'About',
            'website'            =>  'Website',
            'user_id'            =>  'User Parent',
        );
        
        $this->_exclude = array('created_at', 'updated_at', 'id', 'avatar_id');
        
        $this->setLegend($this->_translate('Edit User'));        
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
                     
        $avatar = new Media_Form_Model_Admin_File();
        
        $this->addReferenceSubForm($avatar, 'avatar');
    }
}