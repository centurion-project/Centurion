<?php
/**
 * @class Centurion_Controller_Router_Route_Callback
 * Route class intherits of Centurion_Controller_Router_Route
 * to allows developper to define a method to call when the route is matched
 * to execute some opetations during route matching (like to change the locale)
 *
 * The callback can accept 4 parameters :
 *  - the result of the match
 *  - the path matched
 *  - if the math is partial
 *  - parts of the route
 *
 * @package Multisite
 * @author Richard Déloge, rd@octaveoctave.com
 * @copyright Octave & Octave
 *
 * @todo : add an option to call the method before, after the match, or replace the match.
 */
class Centurion_Controller_Router_Route_Callback extends Centurion_Controller_Router_Route{
    /**
     * Callback to call when the route is matched
     * @var Callback
     */
    protected $_callback = array();

    /**
     * Overloaded constructor to register the callback
     * @param $route
     * @param array $defaults
     * @param array $reqs
     * @param null|Zend_Translate $translator
     * @param null $locale
     * @param array $callback
     */
    public function __construct(
            $route, $defaults = array(),
            $reqs = array(),
            Zend_Translate $translator = null,
            $locale = null,
            $callback = array())
    {
        $this->_callback = $callback;
        parent::__construct($route, $defaults, $reqs, $translator, $locale);
    }

    /**
     * Instantiates route based on passed Zend_Config structure
     * Overloaded getInstance to register the callback from config :
     *  The route config must has a new option "callback", which contains two elements :
     *      - class  : the class which contain the method to call
     *      - method : the public static method to call
     *
     * @param Zend_Config $config Configuration object
     */
    public static function getInstance(Zend_Config $config){
        $reqs = ($config->reqs instanceof Zend_Config) ? $config->reqs->toArray() : array();
        $defs = ($config->defaults instanceof Zend_Config) ? $config->defaults->toArray() : array();
        $callback = ($config->callback instanceof Zend_Config) ? $config->callback->toArray() : array();

        return new self($config->route, $defs, $reqs, null, null, $callback);
    }


    /**
     * Overload to call the callback if the route is matched
     * @param $path
     * @param bool $partial
     * @return array|false
     * @throws Exception
     *
     * @todo : add an option to call the method before, after the match, or replace the match.
     */
    public function match($path, $partial = false){
        //Test the route
        $res = parent::match($path, $partial);

        if(!empty($this->_callback) && $res){
            //Check if the callback is valid
            if(!isset($this->_callback['class']) || !isset($this->_callback['method'])){
                //Not throw an exception because the Error controller needs that route matching is finished
                error_log('Error in Centurion_Controller_Router_Route_Callback, no method setted');
            }

            if(!is_callable(array($this->_callback['class'], $this->_callback['method']))){
                //Not throw an exception because the Error controller needs that route matching is finished
                error_log('Error in Centurion_Controller_Router_Route_Callback, the method is not callable');
            }

            //Call the callback and pass it the result and route params
            call_user_func_array(
                array(
                        $this->_callback['class'],
                        $this->_callback['method']
                ),
                array(
                    $res,
                    $path,
                    $partial,
                    $this->_parts
                )
            );
        }

        return $res;
    }
}