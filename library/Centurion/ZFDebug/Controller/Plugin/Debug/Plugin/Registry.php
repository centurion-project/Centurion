<?php
/**
 * ZFDebug Zend Additions
 *
 * @category   Centurion
 * @package    Centurion_ZFDebug
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 * @version    $Id: $
 */

/**
 * @category   Centurion
 * @package    Centurion_ZFDebug
 * @subpackage Plugin
 * @copyright  Copyright (c) 2008-2009 ZF Debug Bar Team (http://code.google.com/p/zfdebug)
 * @license    http://code.google.com/p/zfdebug/wiki/License     New BSD License
 */
class Centurion_ZFDebug_Controller_Plugin_Debug_Plugin_Registry extends Centurion_ZFDebug_Controller_Plugin_Debug_Plugin implements Centurion_ZFDebug_Controller_Plugin_Debug_Plugin_Interface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $_identifier = 'registry';

    /**
     * Contains Zend_Registry
     *
     * @var Zend_Registry
     */
    protected $_registry;

    /**
     * Create Centurion_ZFDebug_Controller_Plugin_Debug_Plugin_Registry
     *
     * @return void
     */
    public function __construct()
    {
        $this->_registry = Zend_Registry::getInstance();
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
        return ' Registry (' . $this->_registry->count() . ')';
    }

    /**
     * Gets content panel for the Debugbar
     *
     * @return string
     */
    public function getPanel()
    {
    	$html = '<h4>Registered Instances</h4>';
    	$this->_registry->ksort();

        $html .= $this->_cleanData($this->_registry);
        return $html;
    }

}