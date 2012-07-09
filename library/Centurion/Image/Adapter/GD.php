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
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Image_Adapter_GD extends Centurion_Image_Adapter_Abstract
{   
    /**
    * Stores function names for each image type
    */
    protected $_imgLoaders = array(
        'image/jpeg'  => 'imagecreatefromjpeg',
        'image/jpg'  => 'imagecreatefromjpeg',
        'image/pjpeg' => 'imagecreatefromjpeg',
        'image/png'   => 'imagecreatefrompng',
        'image/x-png'   => 'imagecreatefrompng',
        'image/gif'   => 'imagecreatefromgif',
    );
    
    /**
    * Stores function names for each image type
    */
    protected $_imgCreators = array(
        'image/jpeg'  => 'imagejpeg',
        'image/jpg'   => 'imagejpeg',
        'image/pjpeg' => 'imagejpeg',
        'image/png'   => 'imagepng',
        'image/x-png' => 'imagepng',
        'image/gif'   => 'imagegif',
    );
    
    /**
    * List of accepted image types based on MIME
    * descriptions that this adapter supports
    */
    protected $_imgTypes = array(
        'image/jpeg',
        'image/jpg',
        'image/pjpeg',
        'image/png',
        'image/gif',
        'image/x-png'
    );
    
    public function __construct($options = array(), $scale = true, $inflate = true, $quality = self::DEFAULT_QUALITY)
    {
        if (!extension_loaded('gd')) {
          throw new Centurion_Exception ('GD not enabled. Check your php.ini file.');
        }
        
        parent::__construct($options, $scale, $inflate, $quality);
    }
    
    /**
     * @param $image string
     * @return Centurion_Image_Adapter_GD
     */
    public function open($image)
    {
        $information = @getimagesize($image);
        if (!$information) {
            throw new Centurion_Exception(sprintf('Could not load image %s', $image));
        }
        
        if (!in_array($information['mime'], $this->_imgTypes)) {
            throw new Centurion_Exception(sprintf('Image MIME type %s not supported', $information['mime']));
        }
        
        $loader = $this->_imgLoaders[$information['mime']];
        if (!function_exists($loader)) {
            throw new Centurion_Exception(sprintf('Function %s not available. Please enable the GD extension.', $loader));
        }
        
        $this->_source = $loader($image);
        if (null === $this->_source) {
            throw new Centurion_Exception(sprintf('Could not load the file %s', $image));
        }
        
        if (function_exists('imageantialias'))
            imageantialias($this->_source, true);
        
        $this->_sourceWidth = $information[0];
        $this->_sourceHeight = $information[1];
        $this->_sourceMime = $information['mime'];
        
        $this->_sourceSrc = $image;
        
        return $this;
    }
    
    
    public function load($image, $mime)
    {
        if (!in_array($mime, $this->_imgTypes)) {
            throw new Centurion_Exception(sprintf('Image MIME type %s not supported', $mime));
        }
        
        $this->_source = imagecreatefromstring($image);
         if (function_exists('imageantialias'))
            imageantialias($this->_source, true);
        $this->_reloadSize();
        $this->_sourceMime = $mime;
        $this->_generateThumb();
        
        return $this;
    }
    
    public function save($dest, $targetMime = null)
    {
        $dirname = dirname($dest);
        
        if (!file_exists($dirname)) {
            throw new Centurion_Exception(sprintf('Directory %s does not exist', $dirname));
        }
        
        if (!is_writable($dirname)) {
            throw new Centurion_Exception(sprintf('File %s is not writable', $dirname));
        }
        
        if (!$this->_hasBeenModified && $this->_sourceSrc !== null) {
            return copy($this->_sourceSrc, $dest);
        }
        
        if (null !== $targetMime) {
            $creator = $this->_imgCreators[$targetMime];
        } else {
            $creator = $this->_imgCreators[$this->getMime()];
        }
        
        if (!function_exists($creator)) {
            throw new Centurion_Exception(sprintf('TargetMime %s is not valid', $targetMime));
        }
        
        if ('imagejpeg' === $creator) {
            return imagejpeg($this->_thumb, $dest, $this->_quality);
        }
        
        if ('imagepng' === $creator) {
            $quality = (int) round(($this->_quality / 100) * 9);
            
            return imagepng($this->_thumb, $dest, $quality);
        }
        
        return $creator($this->_thumb, $dest);
    }
    
    public function effect()
    {
        $args = func_get_args();
        $args[0] = constant($args[0]);
        array_unshift($args, $this->_source);
        call_user_func_array('imagefilter', $args);
        $this->_thumb = $this->_source;
        
        return $this;
    }
    
    public function __toString()
    {
        $creator = $this->_imgCreators[$this->getMime()];
        ob_start();
        $creator($this->_thumb);
        
        return ob_get_clean();
    }
    
    protected function _newThumb($width, $height)
    {
        if (function_exists('imagecreatetruecolor')) {
            $this->_thumb = imagecreatetruecolor($width, $height);
        } else {
            $this->_thumb = imagecreate($width, $height);
        }
        
        return $this->_preserveAlpha();
    }
    
    protected function _copyToThumb($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
    {
        imagecopyresampled($this->_thumb,
                           $this->_source,
                           $dst_x, $dst_y,
                           $src_x,
                           $src_y,
                           $dst_w,
                           $dst_h,
                           $src_w,
                           $src_h);
        
        return $this;
    }

    /**
     * @return Centurion_Image_Adapter_GD
     */
    protected function _reloadSize()
    {
        $this->_sourceWidth = imagesx($this->_source);
        $this->_sourceHeight = imagesy($this->_source);
        
        return $this;
    }
    
    protected function _preserveAlpha()
    {
        if ($this->_sourceMime == 'image/png' && $this->_options['preserveAlpha'] === true) {
            $result = imagealphablending($this->_thumb, false);
            
            $colorTransparent = imagecolorallocatealpha($this->_thumb,
                                                        $this->_options['alphaMaskColor'][0],
                                                        $this->_options['alphaMaskColor'][1],
                                                        $this->_options['alphaMaskColor'][2], 0);
            
            imagefill($this->_thumb, 0, 0, $colorTransparent);
            imagesavealpha($this->_thumb, true);
        }
        
        // preserve transparency in GIFs... this is usually pretty rough tho
        if ($this->_sourceMime == 'image/gif' && $this->_options['preserveTransparency'] === true) {
            $colorTransparent = imagecolorallocate($this->_thumb,
                                                   $this->_options['transparencyMaskColor'][0],
                                                   $this->_options['transparencyMaskColor'][1],
                                                   $this->_options['transparencyMaskColor'][2]);
            
            imagecolortransparent($this->_thumb, $colorTransparent);
            imagetruecolortopalette($this->_thumb, true, 256);
        }
        
        return $this;
    }
}
