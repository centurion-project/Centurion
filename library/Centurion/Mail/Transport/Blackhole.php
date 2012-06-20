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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 *
 * @category    Centurion
 * @package     Centurion_Mail
 * @subpackage  Centurion_Mail_Transport
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */

class Centurion_Mail_Transport_Blackhole  extends Zend_Mail_Transport_Abstract
{
    /**
     * This transport can be used to desactivate all mail in a website
     * 
     * @see Zend/Mail/Transport/Zend_Mail_Transport_Abstract::_sendMail()
     * @return bool true if the mail is sent. Here we always sent true.
     */
    protected function _sendMail()
    {
        //Do nothing
        return true;
    }
}
