<?php
class Admin_View_Helper_GridRowActions extends Zend_View_Helper_HtmlElement
{
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
            
            $row = $this->view->row;
            $url = preg_replace('`/___(.*)___/`e', "$" . "row->{'\\1'}", $url);

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
}
