<?php

class Centurion_View_Helper_FormInfo extends Zend_View_Helper_FormElement
{
    public function formInfo($name, $value = null, $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        $cls = 'form-info ';
        $attribs['class'] = $cls . (isset($attribs['class']) ?  $attribs['class'] : '');
        if ($escape)
            $value = $this->view->escape($value);

        return $value;
    }
}
