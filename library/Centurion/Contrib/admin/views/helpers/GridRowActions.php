<?php
class Admin_View_Helper_GridRowActions extends Zend_View_Helper_HtmlElement
{
    protected $_row = null;

    public function GridRowActions($label, $options)
    {   
        $url = '';
        $cls = '';
        $clickCallback = '';
        	
        $linkAttribs = array('title' => $label);
        
        if (isset($options['url'])) {
            if (is_array($options['url'])) {
                $url = call_user_func_array(array($this->view, 'url'), $options['url']);
            } else {
                $url = $options['url'];
            }
            
            $this->_row = $this->view->row;
            $url = preg_replace_callback('`/___(.*)___(/|\?|$)`', array(&$this, '_replaceRowCallback'), $url);
            $this->_row = null;

            $url = $url . '?ticket=' . $this->view->ticket()->getKey($url);
        }

        if (isset($options['clickCallback'])) {
            $linkAttribs = array_merge($linkAttribs, array('onclick' => $options['clickCallback']));
        }        
        
        $linkAttribs = $this->_htmlAttribs($linkAttribs);
        
        if (isset($options['cls']))
            $cls = $options['cls'];
            
        return sprintf('<li><a href="%s" class="help" %s><span class="ui-icon %s">%s</span></a></li>', $url, $linkAttribs, $cls, $label); 
    }
    
    protected function _replaceRowCallback($match)
    {   
        if(!$this->_row) {
            throw new InvalidArgumentException('no row was specified when replacing pattern');
        }
        $field = $match[1];
        $endChar = $match[2];
        if(!$field) {
            throw new InvalidArgumentException('no row identifier was specified when replacing pattern');
        }
        return '/' . $this->_row->{$field} . $endChar;
    }
}
