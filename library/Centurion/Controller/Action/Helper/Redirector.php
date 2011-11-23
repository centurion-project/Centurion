<?php

class Centurion_Controller_Action_Helper_Redirector extends Zend_Controller_Action_Helper_Redirector
{
    /**
     * Whether or not _redirect() should attempt to prepend the base URL to the
     * passed URL (if it's a relative URL)
     * 
     * /!\ overload the Zend default behavior
     * 
     * @var boolean
     */
    protected $_prependBase = false;
}
