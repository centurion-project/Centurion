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
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
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
