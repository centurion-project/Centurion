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
 * @package     Centurion_Contrib
 * @subpackage  Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Media_Model_Adapter
{
    /**
     *
     * @param string $key
     * @param array $options
     * @return Media_Model_Adapter_Abstract
     */
    public static function factory($key, $options = null)
    {
        $className = sprintf('Media_Model_Adapter_%s', ucfirst($key));
        if (!class_exists($className)) {
            throw new Centurion_Exception(sprintf('classname \'%s\' does not exist', $className));
        }

        $instance = new $className();

        if (null !== $options) {
            //$instance->setOptions($options);
        }

        return $instance;
    }
}
