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
 * @package     Centurion_Signal
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Signal
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
abstract class Centurion_Signal_Abstract extends Centurion_Collection
{
    const PUSH = 'push';
    const UNSHIFT = 'unshift';
    /**
     *
     * @var Centurion_Collection
     */
    protected $_classnameCollection = null;

    /**
     *
     * @var Centurion_Collection
     */
    protected $_objectCollection = null;

    public function __construct($data = array())
    {
        $this->_classnameCollection = new Centurion_Collection();
        $this->_objectCollection = new Centurion_Collection();

        parent::__construct($data);
    }

    public function connect($receiver, $sender = null, $behavior = Centurion_Signal::BEHAVIOR_CONTINUE, $method = Centurion_Signal_Abstract::PUSH)
    {
        if (!is_array($sender)) {
            $receiver = array(Centurion_Signal::RECEIVER => $receiver, Centurion_Signal::BEHAVIOR => $behavior);
        }

        if (is_object($sender)) {
            if ($method === Centurion_Signal_Abstract::UNSHIFT) {
                $this->_setupObjectContainer($sender)->unshift($receiver);
            }else {
                $this->_setupObjectContainer($sender)->push($receiver);
            }

            return $this;
        } elseif(is_string($sender)) {
            if ($method === Centurion_Signal_Abstract::UNSHIFT) {
                $this->_classnameCollection->unshift(array($sender => $receiver));
            } else {
                $this->_classnameCollection->push(array($sender => $receiver));
            }

            return $this;
        } elseif (is_array($sender)) {
            foreach($sender as $val) {
                $this->connect($receiver, $val, $method);
            }
            return $this;
        }

        return $this->push($receiver);
    }

    public function send($sender = null, $args = array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }

        if (null !== $sender) {
            $args = array_merge(array($this, $sender), $args);
        }

        $receiver = $this->_getReceivers($sender);

        foreach ($receiver as $key => $handler) {
            $behavior = $handler[Centurion_Signal::BEHAVIOR];
            $handler = $handler[Centurion_Signal::RECEIVER];

            $signalValue = call_user_func_array($handler, $args);
            if (Centurion_Signal::BEHAVIOR_CAN_STOP == $behavior && Centurion_Signal::BEHAVIOR_STOP_PROPAGATION == $signalValue
                || Centurion_Signal::BEHAVIOR_STOP_PROPAGATION == $behavior) {
                return $this;
            }
        }

        return $this;
    }
    /**
     * @param object $sender
     * @return array
     */
    protected function _getReceivers($sender = null)
    {
        $receiver = $this->getData();

        if (null !== $sender) {
            $receiver = array_merge($receiver, $this->_setupObjectContainer($sender)->getData());

            foreach($this->_classnameCollection as $key => $val) {
                $className = key($val);

                if ($sender instanceof $className) {
                    $receiver[] = current($val);
                }
            }
        }

        return $receiver;
    }

    /**
     * 
     * @param mixed $sender
     * @return Centurion_Collection
     */
    protected function _setupObjectContainer($sender)
    {
        $id = Centurion_Inflector::id($sender);

        if (!isset($this->_objectCollection->{$id})
            || !(is_array($this->_objectCollection->{$id})
            || $this->_objectCollection->{$id} instanceof ArrayAccess)) {
            $this->_objectCollection->{$id} = new Centurion_Collection();
        }

        return $this->_objectCollection->{$id};
    }
}
