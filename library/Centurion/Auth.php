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
 * @package     Centurion_Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Auth extends Zend_Auth
{
    /**
     * User identity
     *
     * @var Auth_Model_DbTable_Row_User
     */
    protected $_identity = null;

    /**
     * Returns an instance of Centurion_Auth
     *
     * Singleton pattern implementation.
     *
     * @return Centurion_Auth Provides a fluent interface
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Retrieve user object with session data.
     *
     * @return Auth_Model_DbTable_Row_User
     */
    public function getIdentity()
    {
        if ($this->hasIdentity() === true) {
            if (null === $this->_identity) {
                if (is_array(parent::getIdentity())) {
                    $this->getStorage()->write((object) parent::getIdentity());
                }
                
                $this->_identity = Centurion_Db::getSingleton('auth/user')->findOneById(parent::getIdentity()->id);
            }
        } else {
            if (null === $this->_identity) {
                $this->_identity = Centurion_Db::getSingleton('auth/user')->findOneByUsername('anonymous');
            }
        }

        return $this->_identity;
    }

    /**
     * Retrieve user object with session data.
     *
     * shortcut for Centurion_Auth::getInstance()->getIdentity();
     */
    public static function getCurrent()
    {
        return self::getInstance()->getIdentity();
    }

    /**
     * Proxy method to retrieve profile of current user.
     */
    public function getProfile()
    {
        return $this->getIdentity()->getProfile();
    }
    
    public function clearIdentity()
    {
        $this->_identity = null;
        parent::clearIdentity();
    }
}
