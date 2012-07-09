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
 * @package     Centurion_Test
 * @subpackage  Centurion_Test_PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Test
 * @subpackage  Centurion_Test_PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Test_PHPUnit_ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase 
{
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        if (null == $this->bootstrap) {
            // Assign and instantiate in one step:
            $this->bootstrap = new Centurion_Application(
                APPLICATION_ENV,
                Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/configs/', APPLICATION_ENV, true)
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

    public function is404($url)
    {
        $this->resetResponse();
        $this->resetRequest();

        try {
            $this->dispatch($url);

            $exceptions = $this->getResponse()->getException();
            if (!isset($exceptions[0]) || 404 !== $exceptions[0]->getCode()) {
                return false;
            }
        } catch (Centurion_Controller_Action_Exception $e) {
            if ($e->getCode() !== 404) {
                return false;
            }
        }

        return true;
    }

    public function is200($url)
    {
        $this->resetResponse();
        $this->resetRequest();

        try {
            $this->dispatch($url);
            if ($this->getResponse()->getHttpResponseCode() != 200) {
                return false;
            }
        } catch (Centurion_Controller_Action_Exception $e) {
            return false;
        }

        return true;
    }

    public function assert200($url) {
        if (!$this->is200($url)) {
            $this->fail('The action not raised the 200 code');
        }
    }

    public function assertNot200($url) {
        if ($this->is200($url)) {
            $this->fail('The action raised the 200 code. It should not');
        }
    }

    public function assert404($url)
    {
        if (!$this->is404($url)) {
            $this->fail('The action not raised the 404 exception');
        }
    }

    public function assertNot404($url)
    {
        if ($this->is404($url)) {
            $this->fail('The action raised 404 exception. It should not.');
        }
    }

    /**
     * Method to change of locale in follow tests to test the translation mechanism
     * @param string $locale
     */
    protected function _switchLocale($locale){
        //Switch to required language
        Zend_Registry::get('Zend_Translate')->setLocale($locale);
        Zend_Locale::setDefault($locale);
        Zend_Registry::set('Zend_Locale', $locale);
        Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $locale);
        Zend_Controller_Front::getInstance()->getRouter()->setParam('language', $locale);
        Zend_Controller_Front::getInstance()->getRouter()->setGlobalParam('language', $locale);
    }
}
