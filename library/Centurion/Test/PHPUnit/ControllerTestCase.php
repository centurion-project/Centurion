<?php

class Centurion_Test_PHPUnit_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase 
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        if (null == $this->bootstrap) {
            // Assign and instantiate in one step:
            $this->bootstrap = new Centurion_Application(
                'testing',
                APPLICATION_PATH . '/configs/'
            );
        }
        
        parent::__construct($name, $data, $dataName);
    }
    
	/**
     * Retrieve front controller instance
     *
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_frontController) {
            $this->_frontController = Centurion_Controller_Front::getInstance();
        }
        return $this->_frontController;
    }
    
    public function tearDown()
    {
        global $application;
        
        Centurion_Controller_Front::getInstance()->resetInstance();
        $this->resetRequest();
        $this->resetResponse();
        
        $this->request->setPost(array());
        $this->request->setQuery(array());
        
        
        Centurion_Signal::unregister();
        Centurion_Auth::getInstance()->clearIdentity();
        Centurion_Loader_PluginLoader::cleanCache();
        return $application->getBootstrap()->bootstrap('cachemanager');
    }
    
    public function logInAsAdmin()
    {
        $user = Centurion_Db::getSingleton('auth/user')->findOneById(1);
        if ($user == null) {
            throw new PHPUnit_Framework_Exception('Can not log as admin. User does not exists');
        }
        Centurion_Auth::getInstance()->clearIdentity();
        Centurion_Auth::getInstance()->getStorage()->write($user);
    }
    
    public function logInAsAnnonymous()
    {
        $user = Centurion_Db::getSingleton('auth/user')->findOneById(2);
        if ($user == null) {
            throw new PHPUnit_Framework_Exception('Can not log as annonymous. User does not exists');
        }
        Centurion_Auth::getInstance()->clearIdentity();
        Centurion_Auth::getInstance()->getStorage()->write($user);
    }
}