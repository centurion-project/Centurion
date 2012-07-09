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
abstract class Centurion_Image_Adapter_Abstract
{
    const DEFAULT_QUALITY = 95;

    const TOP    = 2;
    const LEFT   = 8;
    const RIGHT  = 4;
    const BOTTOM = 1;

    /**
     * Store if current image has been modified. It avoid to recompress an image that is same as original
     * @var bool
     */
    protected $_hasBeenModified = false;
    
    protected $_thumbWidth = null;

    protected $_thumbHeight = null;

    protected $_sourceWidth = null;

    protected $_sourceHeight = null;

    protected $_sourceMime = null;

    protected $_maxWidth = null;

    protected $_maxHeight = null;

    protected $_minWidth = null;

    protected $_minHeight = null;

    protected $_scale = null;

    protected $_inflate = null;

    protected $_quality = null;

    protected $_source = null;

    protected $_thumb = null;

    protected $_options = null;
    
    protected $_sourceSrc = null;

    /**
     * @param string $image
     * @return Centurion_Image_Adapter_Abstract
     */
    abstract public function open($image);

    /**
     *
     * @param string $dest
     * @param string $targetMime
     * @return Centurion_Image_Adapter_Abstract
     */
    abstract public function save($dest, $targetMime = null);

    /**
     *
     * @param string $image
     * @param string $mime
     */
    abstract public function load($image, $mime);

    public function __construct($options = array(), $scale = true, $inflate = true, $quality = self::DEFAULT_QUALITY)
    {
        $this->_scale = $scale;
        $this->_inflate = $inflate;
        $this->_quality = $quality;
        $this->_options = $options;
    }

    /**
     *
     * @param int $cropWidth
     * @param int $cropHeight
     * @return Centurion_Image_Adapter_Abstract
     */
    public function cropFromCenter($cropWidth, $cropHeight = null)
    {
        if ($cropHeight === null) {
            $cropHeight = $cropWidth;
        }

        $cropWidth = ($this->_sourceWidth < $cropWidth) ? $this->_sourceWidth:$cropWidth;
        $cropHeight = ($this->_sourceHeight < $cropHeight) ? $this->_sourceHeight:$cropHeight;

        $cropX = intval(($this->_sourceWidth - $cropWidth) / 2);
        $cropY = intval(($this->_sourceHeight - $cropHeight) / 2);

        return $this->crop($cropX, $cropY, $cropWidth, $cropHeight);
    }

    /**
     * Crop a picture from a given edge
     * @param int $cropWidth
     * @param int $cropHeight
     * @param int $edge use constant
     * @return Centurion_Image_Adapter_Abstract
     */
    public function cropFromEdge($cropWidth, $cropHeight = null, $edge = 0)
    {
        if ($cropHeight === null) {
            $cropHeight = $cropWidth;
        }

        $cropWidth = ($this->_sourceWidth < $cropWidth) ? $this->_sourceWidth:$cropWidth;
        $cropHeight = ($this->_sourceHeight < $cropHeight) ? $this->_sourceHeight:$cropHeight;

        $cropX = intval(($this->_sourceWidth - $cropWidth) / 2);
        $cropY = intval(($this->_sourceHeight - $cropHeight) / 2);

        $top = (int) $edge | 12;
        if (14 == $top) {
            $cropY = 0;
        } elseif (13 == $top) {
            $cropY = $this->_sourceHeight - $cropHeight;
        }

        $left = ($edge >> 2);
        if (2 == $left) {
            $cropX = 0;
        } elseif (1 == $left) {
            $cropX = $this->_sourceWidth - $cropWidth;
        }

        return $this->crop($cropX, $cropY, $cropWidth, $cropHeight);
    }

    /**
     *
     * @param int $startX
     * @param int $startY
     * @param int $cropWidth
     * @param int $cropHeight
     * @return Centurion_Image_Adapter_Abstract
     */
    public function crop($startX, $startY, $cropWidth, $cropHeight)
    {
        // do some calculations
        $cropWidth  = ($this->_sourceWidth < $cropWidth) ? $this->_sourceWidth:$cropWidth;
        $cropHeight = ($this->_sourceHeight < $cropHeight) ? $this->_sourceHeight:$cropHeight;

        // ensure everything's in bounds
        if (($startX + $cropWidth) > $this->_sourceWidth) {
            $startX = ($this->_sourceWidth - $cropWidth);
        }

        if (($startY + $cropHeight) > $this->_sourceHeight) {
            $startY = ($this->_sourceHeight - $cropHeight);
        }

        if ($startX < 0) {
            $startX = 0;
        }

        if ($startY < 0) {
            $startY = 0;
        }

        $this->_newThumb($cropWidth, $cropHeight);

        $this->_copyToThumb(
                           0,
                           0,
                           $startX,
                           $startY,
                           $cropWidth,
                           $cropHeight,
                           $cropWidth,
                           $cropHeight
                           );

        $this->_source = $this->_thumb;
        $this->_hasBeenModified = true;
        $this->_reloadSize();

        return $this;
    }

    /**
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $minWidth
     * @param int $minHeight
     * @return Centurion_Image_Adapter_Abstract
     */
    public function resize($maxWidth, $maxHeight, $minWidth = null, $minHeight = null)
    {
        return $this->resizeIn($maxWidth, $maxHeight, $minWidth, $minHeight);
    }

    /**
     * Resize image to fit at most ($maxWidth, $maxHeight)
     * The image can be smaller in one and only one dimension.
     *
     * @param int $maxWidth
     * @param int $maxHeight
     * @param int $minWidth
     * @param int $minHeight
     * @return Centurion_Image_Adapter_Abstract
     */
    public function resizeIn($maxWidth, $maxHeight, $minWidth = null, $minHeight = null)
    {
        $this->_maxWidth = $maxWidth;
        $this->_maxHeight = $maxHeight;
        $this->_minWidth = $minWidth;
        $this->_minHeight = $minHeight;

        $this->_generateThumb();

        $this->_source = $this->_thumb;
        $this->_reloadSize();

        return $this;
    }

    /**
     * Resize image to fit at least ($width, $height)
     * The image can exceed in one and only one dimension.
     *
     * @param int $width
     * @param int $height
     * @return Centurion_Image_Adapter_Abstract
     */
    public function resizeOut($width, $height)
    {
        if (false === $this->_options['resizeUp']) {
            $this->_maxHeight = (intval($height) > $this->_sourceHeight) ? $this->_sourceHeight:$height;
            $this->_maxWidth = (intval($width) > $this->_sourceWidth) ? $this->_sourceWidth:$width;
        } else {
            $this->_maxHeight = intval($height);
            $this->_maxWidth = intval($width);
        }

        list($newWidth, $newHeight) = $this->_calcImageSizeStrict($this->_sourceWidth, $this->_sourceHeight);

        // resize the image to be close to our desired dimensions
        $this->resizeIn($newWidth, $newHeight);

        return $this;
    }

    /**
     * Proxy function for the following double effects :
     * - resizeOut
     * - cropFromCenter
     * The result match will fit perfectly the ($width, $height) dimension
     * @param int $width
     * @param int $height
     * @return Centurion_Image_Adapter_Abstract
     */
    public function cropAndResizeFromCenter($width, $height)
    {
        $this->resizeOut($width, $height);
        $this->cropFromCenter($width, $height);

        return $this;
    }

    /**
     * Proxy function for the following double effects :
     * - resizeOut
     * - cropFromEdge
     * The result match will fit perfectly the ($width, $height) dimension
     * @param int $width
     * @param int $height
     * @return Centurion_Image_Adapter_Abstract
     */
    public function cropAndResizeFromEdge($width, $height, $edge)
    {
        $this->resizeOut($width, $height);
        $this->cropFromEdge($width, $height, $edge);

        return $this;
    }

    /**
     * Proxy function for the following double effects :
     * - resizeOut
     * - crop (from Top)
     * The result match will fit perfectly the ($width, $height) dimension
     * @param int $width
     * @param int $height
     * @return Centurion_Image_Adapter_Abstract
     */
    public function adaptiveResize($width, $height)
    {
        $this->resizeOut($width, $height);
        $this->crop(0, 0, $width, $height);

        return $this;
    }

    public function generateMosaic($files, $width, $height, $cols, $rows, $margin = 0, $bgColor = array(0xFF, 0xFF, 0xFF))
    {
        $miniWidth = (($width - ($cols + 1) * $margin) / $cols);
        $miniHeight = (($height - ($rows + 1) * $margin) / $rows);

        $roundedMiniWidth = round($miniWidth);
        $roundedMiniHeight = round($miniHeight);

        $this->_newThumb($width, $height);

        $color = imagecolorallocate($this->_thumb, $bgColor[0], $bgColor[1], $bgColor[2]);
        imagefill($this->_thumb, 0, 0, $color);

        $finalThumb = $this->_thumb;

        foreach ($files as $key => $file) {

            if (null === $file)
                continue;
            try {
                $this->open($file);
            }catch(Centurion_Exception $e) {
                continue;
            }

            $this->cropAndResizeFromCenter($miniWidth, $miniHeight);

            $this->_source = $this->_thumb;
            $this->_thumb = $finalThumb;

            $curCol = $key % $cols;
            $curRow = ($key - $curCol) / $cols;

            $dstX = round(($curCol) * ($margin +  $miniWidth) + $margin);
            $dstY = round(($curRow) * ($margin +  $miniHeight) + $margin);

            $this->_copyToThumb($dstX, $dstY, 0, 0, $roundedMiniWidth, $roundedMiniHeight, $roundedMiniWidth, $roundedMiniHeight);

            $finalThumb = $this->_thumb;
        }

        $this->_source = $this->_thumb;
        $this->_sourceWidth = $width;
        $this->_sourceWidth = $height;

        return $this;

    }

    /**
     * @return int
     */
    public function getThumbHeight()
    {
        return $this->_thumbHeight;
    }

    /**
     * @return int
     */
    public function getThumbWidth()
    {
        return $this->_thumbWidth;
    }

    public function setScale($scale)
    {
        $this->_scale = $scale;
    }

    public function setInflate($inflate)
    {
        $this->_inflate = $inflate;
    }

    public function setQuality($quality)
    {
        $this->_quality = $quality;
    }

    public function getSize()
    {
        return array($this->getWidth(), $this->getHeight());
    }

    public function getWidth()
    {
        return $this->getSourceWidth();
    }

    public function getHeight()
    {
        return $this->getSourceHeight();
    }

    public function getSourceWidth()
    {
        return $this->_sourceWidth;
    }

    public function getSourceHeight()
    {
        return $this->_sourceHeight;
    }

    public function getMime()
    {
        return $this->getSourceMime();
    }

    public function getSourceMime()
    {
        return $this->_sourceMime;
    }

    public static function calculateSize($sourceWidth, $sourceHeight, $scale, $inflate, $maxWidth,
        $maxHeight, $minWidth = null, $minHeight = null)
    {
        if ($maxWidth > 0) {
            $ratioWidth = $maxWidth / $sourceWidth;
        }

        if ($maxHeight > 0) {
            $ratioHeight = $maxHeight / $sourceHeight;
        }

        if ($scale) {
            if ($maxWidth && $maxHeight) {
                $ratio = ($ratioWidth < $ratioHeight) ? $ratioWidth : $ratioHeight;
            } else if ($maxWidth || $maxHeight) {
                $ratio = (isset($ratioWidth)) ? $ratioWidth:$ratioHeight;
            }

            if ((!$maxWidth && !$maxHeight) || (!$inflate && $ratio > 1)) {
                $ratio = 1;
            }

            $thumbWidth = round($ratio * $sourceWidth);
            $thumbHeight = round($ratio * $sourceHeight);
        } else {
            if (!isset($ratioWidth) || (!$inflate && $ratioWidth > 1)) {
                $ratioWidth = 1;
            }

            if (!isset($ratioHeight) || (!$inflate && $ratioHeight > 1)) {
                $ratioHeight = 1;
            }

            $thumbWidth = round($ratioWidth * $sourceWidth);
            $thumbHeight = round($ratioHeight * $sourceHeight);
        }

        if (null !== $minWidth) {
            $thumbHeight = round(($minWidth * $sourceHeight) / $sourceWidth);
            $thumbWidth = $minWidth;
        }

        if (null !== $minHeight) {
            $thumbWidth = round(($minHeight * $sourceWidth) / $sourceHeight);
            $thumbHeight = $minHeight;
        }
        
        return array($thumbWidth, $thumbHeight);
    }
    
    protected function _initThumb($sourceWidth, $sourceHeight, $scale, $inflate, $maxWidth,
        $maxHeight, $minWidth = null, $minHeight = null
    )
    {
            list($this->_thumbWidth, $this->_thumbHeight) = self::calculateSize($sourceWidth, $sourceHeight, $scale, $inflate, $maxWidth, $maxHeight, $minWidth = null, $minHeight = null);

        return $this;
    }

    /**
     * @return array(int, int)
     * @param int $width
     * @param int $height
     */
    protected function _calcImageSizeStrict($width, $height)
    {
        // first, we need to determine what the longest resize dimension is..
        if ($this->_maxWidth >= $this->_maxHeight) {
            // and determine the longest original dimension
            if ($width > $height) {
                list($width, $height) = $this->_calcHeight($width, $height);

                if ($width < $this->_maxWidth) {
                    list($width, $height) = $this->_calcWidth($width, $height);
                }
            } elseif ($height >= $width) {
                list($width, $height) = $this->_calcWidth($width, $height);

                if ($height < $this->_maxHeight) {
                    list($width, $height) = $this->_calcHeight($width, $height);
                }
            }
        } elseif ($this->_maxHeight > $this->_maxWidth) {
            if ($width >= $height) {
                list($width, $height) = $this->_calcWidth($width, $height);

                if ($height < $this->_maxHeight) {
                    list($width, $height) = $this->_calcHeight($width, $height);
                }
            } elseif ($height > $width) {
                list($width, $height) = $this->_calcHeight($width, $height);

                if ($width < $this->_maxWidth) {
                    list($width, $height) = $this->_calcWidth($width, $height);
                }
            }
        }

        return array($width, $height);
    }

    /**
     * Calculates a new width and height for the image based on $this->maxWidth and the provided dimensions
     *
     * @return array
     * @param int $width
     * @param int $height
     */
    protected function _calcWidth($width, $height)
    {
        $newWidthRatio = $this->_maxWidth/ $width;
        $newHeight = $height * $newWidthRatio;

        return array(round($this->_maxWidth), round($newHeight));
    }

    /**
     * Calculates a new width and height for the image based on $this->maxWidth and the provided dimensions
     *
     * @return array
     * @param int $width
     * @param int $height
     */
    protected function _calcHeight($width, $height)
    {
        $newHeightRatio = $this->_maxHeight / $height;
        $newWidth = $width * $newHeightRatio;

        return array(round($newWidth), round($this->_maxHeight));
    }

    /**
     * Generate the thumb by copying from $this->_source to $this->_thumb
     * resize from ($this->_sourceWidth, $this->_sourceHeight) to ($this->getThumbWidth(), $this->getThumbHeight())
     */
    protected function _generateThumb()
    {
        $this->_initThumb($this->_sourceWidth,
                          $this->_sourceHeight,
                          $this->_scale,
                          $this->_inflate,
                          $this->_maxWidth,
                          $this->_maxHeight,
                          $this->_minWidth,
                          $this->_minHeight);

        $this->_newThumb($this->getThumbWidth(), $this->getThumbHeight());

        if ($this->_sourceWidth == $this->_maxWidth && $this->_sourceHeight == $this->_maxHeight) {
            $this->_thumb = $this->_source;
        } else {
            $this->_hasBeenModified = true;
            $this->_copyToThumb(
                               0,
                               0,
                               0,
                               0,
                               $this->getThumbWidth(),
                               $this->getThumbHeight(),
                               $this->_sourceWidth,
                               $this->_sourceHeight
                               );
        }

        return $this;
    }

    /**
     * Generate a new thumb
     * @param int $weight
     * @param int $height
     */
    abstract protected function _newThumb($weight, $height);

    abstract protected function _copyToThumb($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
}
