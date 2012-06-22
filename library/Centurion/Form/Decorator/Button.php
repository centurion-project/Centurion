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
 * @category    Centurion
 * @package     Centurion_Form
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Form_Decorator_Button extends Zend_Form_Decorator_ViewHelper
{
    /**
     * Render a label
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        $element->setAttrib('escape', false);
        $element->setLabel($content);
        $view = $element->getView();
        
        if (null == $view) {
            return $content;
        }
        
        $value         = $this->getValue($element);
        $attribs       = $this->getElementAttribs();
        $attribs['content'] = $content;
        $name          = $element->getFullyQualifiedName();
        $attribs['id'] = $element->getId();
        
        return $view->formButton($name, $value, $attribs, $element->options);
    }
}
