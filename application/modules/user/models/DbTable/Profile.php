<?php
class User_Model_DbTable_Profile extends Centurion_Db_Table_Abstract
{
    protected $_name = 'user_profile';
    
    protected $_primary = 'id';
    
    protected $_rowClass = 'User_Model_DbTable_Row_Profile';
    
    protected $_referenceMap = array(
        'user'   =>  array(
            'columns'       => 'user_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Auth_Model_DbTable_User',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::RESTRICT
        ),
        'avatar'   =>  array(
            'columns'       => 'avatar_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Media_Model_DbTable_File',
            'onDelete'      => self::SET_NULL
        )
    );
    
    protected $_meta = array('verboseName'   => 'profile',
                             'verbosePlural' => 'profiles');
}
