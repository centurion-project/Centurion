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
 * @package     Centurion_Image
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Image
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Image
{
    protected static $_adapterOptions = array(
        'preserveAlpha'         =>  true,
        'alphaMaskColor'        =>  array(255, 255, 255),
        'preserveTransparency'  =>  true,
        'transparencyMaskColor' =>  array(0, 0, 0),
        'resizeUp'              =>  true
    );
    
    /**
     * @param string $adapterClass
     * @param array $adapterOptions
     * @return Centurion_Image_Adapter_Abstract
     */
    public static function factory($adapterClass = null, $adapterOptions = null)
    {
        if (null === $adapterClass) {
            if (extension_loaded('gd')) {
                $adapterClass = 'Centurion_Image_Adapter_GD';
            } else {
                $adapterClass = 'Centurion_Image_Adapter_ImageMagick';
            }
        }
        
        if (null === $adapterOptions) {
            $adapterOptions = self::$_adapterOptions;
        }
        
        return new $adapterClass($adapterOptions);
    }
}
