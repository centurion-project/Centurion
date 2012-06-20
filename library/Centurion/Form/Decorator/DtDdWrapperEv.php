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
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Form_Decorator_DtDdWrapperEv extends Zend_Form_Decorator_Abstract
{
    /**
     * Get element class name.
     *
     * @return string
     */
    public function getClass()
    {
        $class   = '';

        $decoratorClass = $this->getOption('class');
        if (!empty($decoratorClass)) {
            $class .= $decoratorClass;
        }

        return $class;
    }

    /**
     * Decorate content and/or element
     *
     * @param  string $content
     * @return string
     * @throws Zend_Form_Decorator_Exception when unimplemented
     */
    public function render($content)
    {
        $elementName = $this->getElement()->getName();

        return '<dt class="'. $this->getClass() .'"id="' . $elementName . '-label"><label>'.$this->getElement()->getAttrib('label').'</label></dt>'
               . '<dd class="'. $this->getElement()->getAttrib('class') .'"id="' . $elementName . '-element">' . $content . '</dd>';
    }
}
