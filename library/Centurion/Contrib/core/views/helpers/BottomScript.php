<?php

class Centurion_View_Helper_BottomScript extends Zend_View_Helper_HeadScript
{
    protected $_regKey = 'Centurion_View_Helper_BottomScript';
    
    /**
     * Return headScript object
     *
     * Returns headScript helper object; optionally, allows specifying a script
     * or script file to include.
     *
     * @param  string $mode Script or file
     * @param  string $spec Script/url
     * @param  string $placement Append, prepend, or set
     * @param  array $attrs Array of script attributes
     * @param  string $type Script type and/or array of script attributes
     * @return Zend_View_Helper_HeadScript
     */
    public function bottomScript($mode = Zend_View_Helper_HeadScript::FILE, $spec = null, $placement = 'APPEND', array $attrs = array(), $type = 'text/javascript')
    {
        return parent::headScript($mode, $spec, $placement, $attrs, $type);
    }
}
