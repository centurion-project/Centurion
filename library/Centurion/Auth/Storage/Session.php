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
 * @subpackage  Storage
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Auth
 * @subpackage  Storage
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Auth_Storage_Session extends Zend_Auth_Storage_Session
{
    /**
     * This allow with a config file, to change the cookie_domain of the session.
     * Set "session.domain" directive in application.ini to change this.
     *
     * @param string $namespace
     * @param string $member
     */
    public function __construct($namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT)
    {
        $cookieDomain = Centurion_Config_Manager::get('session.domain', $_SERVER['SERVER_NAME']);
        
        if ($cookieDomain !== null) {
            Zend_Session::setOptions(array('cookie_domain' => $cookieDomain));
        }
            
        parent::__construct($namespace, $member);
    }
}
