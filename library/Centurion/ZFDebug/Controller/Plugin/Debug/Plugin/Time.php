<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   Centurion
 * @package    Centurion_ZFDebug
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: Time.php 72 2009-05-15 14:16:21Z gugakfugl $
 */

/**
 * @see Zend_Session
 */
require_once 'Zend/Session.php';

/**
 * @see Zend_Session_Namespace
 */
require_once 'Zend/Session/Namespace.php';

/**
 * @category   Centurion
 * @package    Centurion_ZFDebug
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class Centurion_ZFDebug_Controller_Plugin_Debug_Plugin_Time extends Zend_Controller_Plugin_Abstract implements Centurion_ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'time';

    /**
     * @var array
     */
    protected $_timer = array();

    /**
     * Creating time plugin
     * @return void
     */
    public function __construct()
    {
        Zend_Controller_Front::getInstance()->registerPlugin($this);
    }

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

    /**
     * Gets menu tab for the Debugbar
     *
     * @return string
     */
    public function getTab()
    {
        return round($this->_timer['postDispatch'],2) . ' ms';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
        $html = '<h4>Custom Timers</h4>';
        $html .= 'Controller: ' . round(($this->_timer['postDispatch']-$this->_timer['preDispatch']),2) .' ms<br />';
        if (isset($this->_timer['user']) && count($this->_timer['user'])) {
            foreach ($this->_timer['user'] as $name => $time) {
                $html .= ''.$name.': '. round($time,2).' ms<br />';
            }
        }

        if (!Zend_Session::isStarted()) {
            Zend_Session::start();
        }

        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this_module = $request->getModuleName();
        $this_controller = $request->getControllerName();
        $this_action = $request->getActionName();

        $timerNamespace = new Zend_Session_Namespace('Centurion_ZFDebug_Time',false);
        $timerNamespace->data[$this_module][$this_controller][$this_action][] = $this->_timer['postDispatch'];

        $html .= '<h4>Overall Timers</h4>';

        foreach($timerNamespace->data as $module => $controller)
        {
            if ($module == $this_module) {
                $module = '<strong>'.$module.'</strong>';
            }
            $html .= $module . '<br />';
            $html .= '<div class="pre">';
            foreach($controller as $con => $action)
            {
                if ($con == $this_controller) {
                    $con = '<strong>'.$con.'</strong>';
                }
                $html .= '    ' . $con . '<br />';
                $html .= '<div class="pre">';
                foreach ($action as $key => $data)
                {
                    if ($key == $this_action) {
                        $key = '<strong>'.$key.'</strong>';
                    }
                    $html .= '        ' . $key . '<br />';
                    $html .= '<div class="pre">';
                    $html .= '            Avg: ' . $this->_calcAvg($data) . ' ms / '.count($data).' requests<br />';
                    $html .= '            Min: ' . round(min($data), 2) . ' ms<br />';
                    $html .= '            Max: ' . round(max($data), 2) . ' ms<br />';
                    $html .= '</div>';
                }
                $html .= '</div>';
            }
            $html .= '</div>';
        }
        $html .= '<br />Reset timers by sending Centurion_ZFDebug_RESET as a GET/POST parameter';

        return $html;
    }

    /**
     * Sets a time mark identified with $name
     *
     * @param string $name
     */
    public function mark($name) {
        if (isset($this->_timer['user'][$name]))
            $this->_timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000-$this->_timer['user'][$name];
        else
            $this->_timer['user'][$name] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    #public function routeStartup(Zend_Controller_Request_Abstract $request) {
    #     $this->timer['routeStartup'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    #}

    #public function routeShutdown(Zend_Controller_Request_Abstract $request) {
    #     $this->timer['routeShutdown'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    #}

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $reset = Zend_Controller_Front::getInstance()->getRequest()->getParam('Centurion_ZFDebug_RESET');
        if (isset($reset)) {
            $timerNamespace = new Zend_Session_Namespace('Centurion_ZFDebug_Time',false);
            $timerNamespace->unsetAll();
        }
        
        $this->_timer['preDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    /**
     * Defined by Zend_Controller_Plugin_Abstract
     *
     * @param Zend_Controller_Request_Abstract
     * @return void
     */
    public function postDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_timer['postDispatch'] = (microtime(true)-$_SERVER['REQUEST_TIME'])*1000;
    }

    /**
     * Calculate average time from $array
     *
     * @param array $array
     * @param int $precision
     * @return float
     */
    protected function _calcAvg(array $array, $precision=2)
    {
        if (!is_array($array)) {
            return 'ERROR in method _calcAvg(): this is a not array';
        }

        foreach($array as $value)
            if (!is_numeric($value)) {
                return 'ERROR in method _calcAvg(): the array contains one or more non-numeric values';
            }

        $cuantos=count($array);
        return round(array_sum($array)/$cuantos,$precision);
    }
}