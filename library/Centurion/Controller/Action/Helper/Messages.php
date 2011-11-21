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
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Controller_Action_Helper_Messages extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * FlashMessengers
     *
     * @var array
     */
    protected $_flashMessengers = array();

    /**
     * Levels
     *
     * @var array
     */
    protected $_levels = array('debug', 'error', 'info', 'success', 'warning');

    /**
     * Attempts to add a message to the request.
     *
     * @param string $level Level
     * @param string $message Message
     * @return Centurion_Controller_Action_Helper_Messages
     */
    public function messages($level, $message)
    {
        return $this->addMessage($level, $message);
    }

    /**
     * Retrieve all messages for a given level, if no level given all messages of all levels will be retrieved.
     *
     * @param string $level Level
     * @return array
     */
    public function getMessages($level = null)
    {
        if (null === $level) {
            $messages = array();
            foreach ($this->_levels as $level) {
                if ($messages = $this->_getMessages($level)) {
                    $messages[$level] = $messages;
                }
            }

            return $messages;
        }

        return $this->_getMessages($level);
    }

    /**
     * Attempts to add a message to the request.
     *
     * @param string $level Level
     * @param string $message Message
     * @return Centurion_Controller_Action_Helper_Messages
     */
    public function addMessage($level, $message)
    {
        $this->_getFlashMessenger($level)->addMessage($message);

        return $this;
    }

    /**
     * Clear all messages from the previous request & current namespace
     *
     * @return boolean True if messages were cleared, false if none existed
     */
    public function clearMessages($level)
    {
        return $this->_getFlashMessenger($level)->clearMessages();
    }

    /**
     * clear messages from the current request & current namespace
     *
     * @return boolean
     */
    public function clearCurrentMessages($level)
    {
        return $this->_getFlashMessenger($level)->clearCurrentMessages();
    }

    /**
     * Call a specific FlashMessenger with the level ($messages->info(), $messages->debug(), $messages->success()).
     *
     * @param string $method Method must be a valid level
     * @param array $args Arguments
     * @return array|Centurion_Controller_Action_Helper_Messages
     * @throws Centurion_Exception When the level does not exist
     */
    public function __call($method, $args)
    {
        if (!in_array($method, $this->_levels)) {
            throw new Centurion_Exception(sprintf("Method '%s' does not exist", $method));
        }

        if (count($args)) {
            return $this->addMessage($method, $args[0]);
        }

        return $this->_getMessages($method);
    }

    public function direct($level = null, $message = null)
    {
        if (null === $message && null === $level) {
            return $this;
        }

        return $this->messages($level, $message);
    }

    /**
     * Check if a specific level has messages
     *
     * @param string $level Level
     * @return boolean True if the level has messages otherwise false
     */
    public function hasMessages($level = null)
    {
        if (null === $level) {
            $messages = array();
            foreach ($this->_levels as $level) {
                if ($this->_hasMessages($level)) {
                    return true;
                }
            }

            return false;
        }

        return $this->_hasMessages($level);
    }

    /**
     * Retrieve all messages for a given level.
     *
     * @param string $level Level
     * @return array Results
     */
    protected function _getMessages($level)
    {
        $result = array();

        $flashMessenger = $this->_getFlashMessenger($level);

        if ($flashMessenger->hasMessages()) {
            $result = $flashMessenger->getMessages();
        }

        return $result;
    }

    /**
     * Check if a specific level has messages
     *
     * @param string $level Level
     * @return boolean True if the level has messages otherwise false
     */
    protected function _hasMessages($level)
    {
        return $this->_getFlashMessenger($level)->hasMessages();
    }

    /**
     * Retrieve a FlashMessenger for a given level.
     *
     * @param string $level Level
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    protected function _getFlashMessenger($level)
    {
        if (!isset($this->_flashMessengers[$level])) {
            $flashMessenger = clone Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $this->_flashMessengers[$level] = $flashMessenger->setNamespace($level);
        }

        return $this->_flashMessengers[$level];
    }
}