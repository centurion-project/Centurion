<?php

class User_Bootstrap extends Centurion_Application_Module_Bootstrap
{
    protected function _initSignals()
    {
        Centurion_Signal::factory('post_login')->connect(array($this, 'registerProfile'));
    }

    /**
     * Register a profile automatically if it not exists.
     *
     * @param string $result result row of authentication
     * @return void
     */
    public function registerProfile($signal)
    {
        if (!Centurion_Auth::getInstance()->getProfile()) {
            $identity = Centurion_Auth::getInstance()->getIdentity();
            Centurion_Db::getSingleton('user/profile')->insert(array('nickname' =>  $identity->username,
                                                                     'user_id'  =>  $identity->id));
        }
    }
}