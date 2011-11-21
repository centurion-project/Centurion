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
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Tool_Project_Provider_Abstract extends Zend_Tool_Project_Provider_Abstract
{
    public function __construct()
    {
        if (!self::$_isInitialized) {
            Zend_Tool_Project_Context_Repository::getInstance()->addContextsFromDirectory(
                dirname(dirname(__FILE__)) . '/Context/Zf/', 'Centurion_Tool_Project_Context_Zf_'
            );
        }
        
        //parent::__construct();
    }
}