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
 * @package     Centurion_Rest
 * @subpackage  Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */
/**
 * @category    Centurion
 * @package     Centurion_Rest
 * @subpackage  Controller
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 */
class Centurion_Rest_Controller_API extends Centurion_Rest_Controller
{
    public function init()
    {
        parent::init();

        $this->getHelper('ContextAutoSwitch')->direct();

        $this->_request->setParams($this->getHelper('params')->direct());
    }

    public function preDispatch()
    {
        $this->getHelper('layout')->disableLayout();
    }

    public function indexAction()
    {
    }

    public function getAction()
    {
    }

    public function postAction()
    {
    }

    public function putAction()
    {
    }

    public function deleteAction()
    {
    }
}
