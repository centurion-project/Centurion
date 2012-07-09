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
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @todo        allow to use a specific lifetime
 * @todo        pass the lifetime in url
 * @todo        add testunit
 */

/**
 * This class allow you to protect an access to a page/action with a token.
 * Anyone who has the url will access to the page.
 * It's perfect for given a preview to an article that is currently not published.
 *
 * How to use it:
 *
 * In a controller, by exemple in admin, use this code to generate a url with ticket:
 * <code>
 * //$myPage as a $page that we want to generate a valid ticket
 *
 * $url = $this->view->url(array('id' => $), 'show_page');
 * $urlWithTicket = $this->_helper->ticket($url);
 * </code>
 *
 * In the front controller use this code:
 * <code>
 * public function show()
 * {
 *
 *     $id = $this->getRequest()->getParam('id');
 *     $page = $this->_getPageFromDb($id);
 *
 *     if (!$page->isOnline()) {
 *         if (!$this->_helper->ticket()->isValid()) {
 *             throw new Exception('You can not access this page');
 *         }
 *     }
 *
 *     //If we are here, it is only if the page is online, or the visitor have the full url with ticket
 * }
 * </code>
 */
class Centurion_Controller_Action_Helper_Ticket extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @param string|array|null $actionUrl the url
     * @param null|int|Zend_Date $mktime the time to use for generate the key. It is usefull for saving time if you generate many key at the same time.
     * @return string the final url with a valid ticket.
     */
    public function direct($actionUrl = null, $mktime = null)
    {
        $actionUrl = $this->_getActionUrl($actionUrl);
        return $actionUrl . '?ticket=' . $this->getkey($actionUrl, $mktime);
    }

    /**
     * Return the url of a given action
     * @todo if is_string, remove the ticket from the request
     * @param $actionUrl
     * @return array
     */
    protected function _getActionUrl($actionUrl)
    {
        if (null === $actionUrl) {
            $actionUrl = $this->getRequest()->getParams();
        }

        if (is_array($actionUrl)) {
            //We remove the ticket from current request param
            //If we don't it will false the ticket generation
            if (isset($actionUrl['ticket'])) {
                unset($actionUrl['ticket']);
            }

            //We rebuild the url without ticket param
            $actionUrl = $this->getActionController()->view->url($actionUrl, null);
        }

        return $actionUrl;
    }

    /**
     * @return Zend_Date current date, with unit reset depending of current lifetime.
     * @todo lifetime should be a parameter
     */
    protected function _getDate()
    {
        $lifetime = Centurion_Config_Manager::get('ticket.lifetime');
        list(, $lifetimeUnit) = sscanf($lifetime, '%d%s');

        $date = new Zend_Date();

        //We reset all unit that are below the ticket lifetime
        //Ex: if the lifetime if 1d, we reset hours, minute, second
        switch ($lifetimeUnit) {
            case 'j':
            case 'd':
                $date->setHour(0);
            case 'h':
                $date->setMinute(0);
            case 'm':
            default:
                $date->setSecond(0);
        }

        return $date;
    }

    /**
     * @param string|array|null $actionUrl the url
     * @param null|int|Zend_Date $mktime the time to use for generate the key. It is usefull for saving time if you generate many key at the same time.
     * @return string
     */
    public function getkey($actionUrl = null, $mktime = null)
    {
        //We generate the url
        $actionUrl = $this->_getActionUrl($actionUrl);

        if ($mktime == null) {
            $mktime = $this->_getDate();
        }

        if ($mktime instanceof Zend_Date) {
            $date = $mktime->toString('MMddYYYY-HH:mm');
        } else {
            $date = date('mdY-H:i', $mktime);
        }

        $salt = Centurion_Config_Manager::get('ticket.salt');

        //Just a simple hash with salt and the current date
        return sha1($salt . $actionUrl . $date);
    }

    /**
     * Test if a given ticket is valid for a given url and a given lifetime.
     *
     * @param null|string $ticket the ticket to test
     * @param string|array|null $actionUrl the url
     * @param null $lifeTime
     * @return bool if valid or not
     * @TODO test $ticket before. it's a sha1 so only hexa and 40 string lenght
     */
    public function isValid($ticket = null, $actionUrl = null, $lifeTime = null)
    {
        if (null === $ticket) {
            $ticket = $this->getRequest()->getParam('ticket');

            //If not ticket in request, it could only be false
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
        $date = $this->_getDate();

        //In order to test if current ticket is valid, we will generate all ticket from now to maximum lifetime
        //and test if we find the current
        for ($i = 0; $i < $lifetimeValue; $i++) {
            $key = $this->getKey($actionUrl, $date);

            if ($ticket === $key) {
                return true;
            }

            //We decrement the date by 1 unit
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
