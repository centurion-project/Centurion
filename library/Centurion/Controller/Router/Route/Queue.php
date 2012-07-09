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
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Controller
 * @subpackage  Router
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Controller_Router_Route_Queue extends Centurion_Queue
{
    /**
     * @param array|Zend_Controller_Router_Route $data
     * @throws Centurion_Exception
     */
    public function push($data)
    {
        if ((is_array($data) && (!isset($data['name']) || !isset($data['route']))) || (!is_array($data) && !$data instanceof Zend_Controller_Router_Route)) {
            throw new Centurion_Exception('Parameter must be a instance of Zend_Controller_Router_Route');
        }
            
        parent::push($data);
    }
}
