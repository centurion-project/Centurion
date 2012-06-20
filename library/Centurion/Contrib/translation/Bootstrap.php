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
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Translation_Bootstrap extends Centurion_Application_Module_Bootstrap
{
    protected function _initTranslation()
    {
        $bootstrap = $this->getApplication();

        $bootstrap->bootstrap('translate');
        $translator = $bootstrap->getResource('translate');

        if (method_exists($this, 'connect'))
            $translator->connect();

        Zend_Validate_Abstract::setDefaultTranslator($translator);
    }

    protected function _initHelper()
    {
        $this->bootstrap('FrontController');
        //TODO: Activate or unactivate it by a config.
        Zend_Controller_Action_HelperBroker::addHelper(new Translation_Controller_Action_Helper_ManageLanguageParam());
    }

    /*protected function _initRoute()
    {
        return;
        $this->bootstrap('FrontController');

        $application = $this->getApplication();
        $application->bootstrap('router');
        $router = $application->getResource('router');

        $languageRowset = Centurion_Db::getSingleton('translation/language')->fetchAll();
        $localeArray = array();
        foreach($languageRowset as $rs) {
            $localeArray[] = $rs->locale;
        }

        $routeLang = new Zend_Controller_Router_Route('/:language', array(), array('language' => '(' . implode('|', $localeArray) . ')'));
        $routeLangName = 'language';
        $router->addRoute('language', $routeLang);

        // @todo : change to avoid using registry
        if (Zend_Registry::isRegistered('Centurion_Route_Queue'))
            $routeQueue = Zend_Registry::get('Centurion_Route_Queue');
        else
            $routeQueue = new Centurion_Controller_Router_Route_Queue();

        $routeQueue->push(array('name' => $routeLangName, 'route' => $routeLang));
        Zend_Registry::set('Centurion_Route_Queue', $routeQueue);

        //$router->getRouteQueue()->push($routeLang);
    }*/

    protected function _initRoute()
   {
       $this->bootstrap('FrontController');

       $application = $this->getApplication();
       $application->bootstrap('router');
       $router = $application->getResource('router');

       //Zend_Debug::dump($router->getRoute('home')->getRoute('language'));
       //Zend_Debug::dump(array_keys($router->getRoutes()));
       //die();
       $routeLang = $router->getRoute('language_unchained');
       $routeLangName = 'language';

       //@todo : change to avoid using registry
       if (Zend_Registry::isRegistered('Centurion_Route_Queue'))
           $routeQueue = Zend_Registry::get('Centurion_Route_Queue');
       else
           $routeQueue = new Centurion_Controller_Router_Route_Queue();

       $routeQueue->push(array('name' => $routeLangName, 'route' => $routeLang));
       Zend_Registry::set('Centurion_Route_Queue', $routeQueue);

       //$router->getRouteQueue()->push($routeLang);
   }
}
