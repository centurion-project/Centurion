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
class Centurion_View_Helper_Cui_MakeSaveButton extends Zend_View_Helper_Abstract
{
    public function Cui_MakeSaveButton(Centurion_Form $form)
    {
        $label = 'Save';
        
        if (null !== $form->getTranslator())
            $label = $form->getTranslator()->_($label);
        $form->addElement('button', '_saveBig', array('type' => 'submit', 'label' => $label));
        
        if (null === $form->getDisplayGroup('_header')) {
            $this->view->getHelper('GridForm')->makeHeader($form, array('_saveBig'));
        } else {
            $form->addInDisplayGroup('_saveBig', '_header');
        }
        
        $save = $form->getElement('_saveBig');
        
        $save->addDecorator('HtmlTag', array('tag' => 'div', 'class' => 'ui-button-big'));
        $save->setAttrib('class', 'ui-button ui-button-bg-white-gradient ui-button-text-red ui-button-text-only');
        $save->setAttrib('class1', null);
        $save->setAttrib('class2', 'ui-button-text');
        $save->setAttrib('role', 'submit');
        
        return $form;
    }
}
