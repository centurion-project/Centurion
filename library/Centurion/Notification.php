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
 * @package     Centurion_Notification
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Notification
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Notification {
    
    const FRONT = 'front-office';
    const BACK = 'back-office';
    
    public static function get($name){
        if (empty($name))
            throw new Centurion_Exception('namespace for stack is required');
            
        $notificationSession = new Zend_Session_Namespace('centurionNotification');
        if (!isset($notificationSession->$name)){
            $notificationSession->$name = new Centurion_Stack();
        }

        return $notificationSession->$name;
    }
}
