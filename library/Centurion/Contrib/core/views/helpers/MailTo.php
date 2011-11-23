<?php
class Centurion_View_Helper_MailTo extends Zend_View_Helper_HtmlElement
{
    
    protected function _crypt($email)
    {
        $crypt = '';
        foreach ((array) str_split($email) as $char) {
            $crypt .= sprintf("&#%d", ord($char));
        }

        return $crypt;
    }    
    
    protected function _htmlAttribs($attribs) {
        
        $attribs = (array) $attribs;
        
        if (isset($attribs['href']))
            unset($attribs['href']);
        
        return parent::_htmlAttribs($attribs);
    }
    
    public function mailTo($email, $content = null, $attribs = array())
    {
        $email = $this->_crypt($email);
        
        $attribsString = $this->_htmlAttribs($attribs);
        
        return sprintf('<a href="mailto:%s" %s>%s</a>', $email, $attribsString, ($content ? $content : $email));
    }
    
    
}