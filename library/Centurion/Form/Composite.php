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
class Centurion_Form_Composite extends Centurion_Form
{
    /**
     * Load the default decorators
     *
     * @return void
     */
    public function loadDefaultDecorators()
    {
        parent::loadDefaultDecorators();

        $this->cleanForm();
    }

    /**
     * Render form
     *
     * @param  Zend_View_Interface $view
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        foreach ($this->getElements() as $key => $element) {
            if (!$element->hasErrors()) {
                continue;
            }

            $element->setDecorators($this->defaultErrorElementDecorators);
        }

        return parent::render($view);
    }
}
