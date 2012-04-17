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
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View_Helper_Messages extends Zend_View_Helper_Abstract
{
    /**
     * @param null $level
     * @return $this
     */
    public function messages($level = null)
    {
        if (null !== $level) {
            return Zend_Controller_Action_HelperBroker::getStaticHelper('messages')->getMessages($level);
        }

        return $this;
    }

    public function hasMessages($level = null)
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('messages')->hasMessages($level);
    }

    public function render($level = null, $ordered = false, $attribs = false, $escape = true)
    {
        $render = '';

        if (null === $level) {
            $levels = $this->_levels;
        } else {
            $levels = array($level);
        }

        foreach($levels as $level) {
            if ($messages = $this->messages($level)) {
                $render .= $this->_renderMessage($messages, $level, $ordered,
                                                 $attribs, $escape);
            }
        }

        return $render;
    }

    /**
     * @param $level
     * @param $message
     * @return $this
     */
    public function addMessage($level, $message)
    {
        Zend_Controller_Action_HelperBroker::getStaticHelper('messages')->addMessage($level, $message);

        return $this;
    }

    protected function _renderMessage($messages, $level, $ordered, $attribs, $escape)
    {
        if (!is_array($messages)) {
            $messages = array($messages);
        }

        if (false === $attribs) {
            $attribs = array('class' => $level);
        }

        return $this->view->htmlList($messages, $ordered, $attribs, $escape);
    }
}
