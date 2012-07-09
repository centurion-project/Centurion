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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Admin_View_Helper_Ticket extends Zend_View_Helper_Abstract
{
    /**
     * @param null $actionUrl
     * @param null $mktime
     * @return $this
     */
    public function ticket($actionUrl = null, $mktime = null)
    {
        return $this;
    }

    protected function _getActionUrl($actionUrl)
    {
        if (null === $actionUrl) {
            $actionUrl = Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
        }

        if (is_array($actionUrl)) {
            if (isset($actionUrl['ticket']))
                unset($actionUrl['ticket']);
            $actionUrl = $this->view->url($actionUrl);
        }
        
        return $actionUrl;
    }

    public function getkey($actionUrl = null, $mktime = null)
    {
        $actionUrl = $this->_getActionUrl($actionUrl);

        if ($mktime == null) {
            $lifetime = Centurion_Config_Manager::get('ticket.lifetime');
            list($lifetimeValue, $lifetimeUnit) = sscanf($lifetime, '%d%s');

            $mktime = new Zend_Date();

            switch ($lifetimeUnit) {
                case 'j':
                case 'd':
                    $mktime->setHour(0);
                case 'h':
                    $mktime->setMinute(0);
                case 'm':
                default:
                    $mktime->setSecond(0);
            }
        }

        if ($mktime instanceof Zend_Date)
            $date = $mktime->toString('YYYYMMdd-HH:mm');
        else
            $date = date('Ymd-H:i', $mktime);
        
        $salt = Centurion_Config_Manager::get('ticket.salt');

        $ticket = md5($salt . $actionUrl . $date);
        
        return $ticket;
    }

    public function isValid($ticket = null, $actionUrl = null, $lifeTime = null)
    {
        if (null === $ticket) {
            $ticket = Zend_Controller_Front::getInstance()->getRequest()->getParam('ticket');
            if (null === $ticket) {
                return false;
            }
        }

        $actionUrl = $this->_getActionUrl($actionUrl);

        if (null === $lifeTime) {
            $lifeTime = Centurion_Config_Manager::get('ticket.lifetime');
        }

        list($lifetimeValue, $lifetimeUnit) = sscanf($lifeTime, '%d%s');

        $lifetimeUnit = strtolower($lifetimeUnit);
        $date = new Zend_Date();
        switch($lifetimeUnit) {
            case 'j':
            case 'd':
                $date->setHour(0);
            case 'h':
                $date->setMinute(0);
            case 'm':
            default:
                $date->setSecond(0);
        }

        for ($i = 0; $i < $lifetimeValue; $i++) {
            if ($ticket === $this->getKey($actionUrl, $date)) {
                return true;
            }
            
            switch($lifetimeUnit) {
                case 'j':
                    $date->subDay(1);
                    break;
                case 'h':
                    $date->subHour(1);
                    break;
                case 'm':
                default:
                    $date->subMinute(1);
                    break;
            }
        }

        return false;
    }
}
