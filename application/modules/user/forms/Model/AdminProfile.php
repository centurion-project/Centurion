<?php

class User_Form_Model_AdminProfile extends Centurion_Form_Model_Abstract
{
    public function __construct($options = array())
    {
        $this->_model = Centurion_Db::getSingleton('user/profile');
        
        $this->_exclude = array('user_id', 'created_at', 'updated_at', 'id', 'avatar_id');        
        
        $this->_elementLabels = array(
            'nickname'           =>  'Nickname',
            'about'              =>  'About',
            'website'            =>  'Website',
            'facebook_pageid'    =>  'Facebook Page ID'
        );
                
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
             
        $avatar = new Media_Form_Model_Admin_File();
        
        $this->addReferenceSubForm($avatar, 'avatar');
        
        $user = new Auth_Form_Model_User();
        
        $this->addReferenceSubForm($user, 'user');
    }
}