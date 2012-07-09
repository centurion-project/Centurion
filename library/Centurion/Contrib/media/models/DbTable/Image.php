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
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Media_Model_DbTable_Image extends Centurion_Db_Table_Abstract
{
    protected $_primary = 'id';

    protected $_rowClass = 'Centurion_Db_Table_Row';

    protected $_rowsetClass = 'Centurion_Db_Table_Rowset';

    protected $_meta = array('verboseName'   => 'image',
                             'verbosePlural' => 'images');

    protected $_name = 'media_image';

    protected static $_effectName = array(
        'cropcenter'                => 'aa',
        'cropcenterresize'          => 'ab',
        'crop'                      => 'ac',
        'resize'                    => 'ad',
        'IMG_FILTER_NEGATE'         => 'ae',
        'IMG_FILTER_GRAYSCALE'      => 'af',
        'IMG_FILTER_BRIGHTNESS'     => 'ag',
        'IMG_FILTER_CONTRAST'       => 'ah',
        'IMG_FILTER_COLORIZE'       => 'ai',
        'IMG_FILTER_EDGEDETECT'     => 'ak',
        'IMG_FILTER_GAUSSIAN_BLUR'  => 'al',
        'IMG_FILTER_SELECTIVE_BLUR' => 'am',
        'IMG_FILTER_MEAN_REMOVAL'   => 'an',
        'IMG_FILTER_SMOOTH'         => 'ao',
        'IMG_FILTER_EMBOSS'         => 'ap',
        'IMG_FILTER_PIXELATE'       => 'aq',
        'adaptiveresize'            => 'ar',
        'cropedgeresize'            => 'as'
    );
    protected static $_paramName = array(
        'minWidth'  => 'b',
        'maxWidth'  => 'c',
        'minHeight' => 'd',
        'maxHeight' => 'e',
        'width'     => 'f',
        'height'    => 'g',
        'x'         => 'h',
        'y'         => 'i',
        'degree'    => 'j',
        'red'       => 'k',
        'green'     => 'l',
        'blue'      => 'm',
        'size'      => 'n',
        'pixelate'  => 'o',
        'edge'      => 'p'
    );

    /**
     * @param array $data
     * @return mixed
     */
    public function insert(array $data)
    {
        //TODO: why this code ?
        if (isset($data['width']) && isset($data['height'])) {
            $data[Centurion_Db_Table_Abstract::VERBOSE] = false;
            return parent::insert($data);
        }

        return parent::insert($this->_getImageSize($data['local_filename']));
    }

    /**
     * @param array $data
     * @param array|string $where
     * @return int
     * @TODO: $data is not send to parent. Something is not normal
     */
    public function update(array $data, $where)
    {
        return parent::update($this->_getImageSize($data['local_filename']), $where);
    }

    /**
     * @param $filename
     * @return array with 2 keys: height and width
     */
    protected function _getImageSize($filename)
    {
        if (!is_file($filename)) {
            $filename = Centurion_Config_Manager::get('media.uploads_dir') . DIRECTORY_SEPARATOR . $filename;
        }
        $adapter = Centurion_Image::factory();
        $adapter->open($filename);

        return array(
            'height'    =>  $adapter->getSourceHeight(),
            'width'     =>  $adapter->getSourceWidth()
        );
    }

    /**
     * @return array array of mime with extension
     */
    public function getMimeTypes()
    {
        return array(
            'image/gif' => 'gif',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/bmp' => 'bmp',
//            'image/cewavelet' => 'wif',
//            'image/cis-cod' => 'cod',
//            'image/fif' => 'fif',
//            'image/ief' => 'ief',
//            'image/jp2' => 'jp2',
            'image/jpeg' => 'jpg',
//            'image/jpm' => 'jpm',
//            'image/jpx' => 'jpf',
//            'image/pict' => 'pic',
//            'image/targa' => 'tga',
//            'image/tiff' => 'tif',
//            'image/vn-svf' => 'svf',
//            'image/vnd.dgn' => 'dgn',
//            'image/vnd.djvu' => 'djvu',
//            'image/vnd.dwg' => 'dwg',
//            'image/vnd.glocalgraphics.pgb' => 'pgb',
//            'image/vnd.microsoft.icon' => 'ico',
//            'image/vnd.ms-modi' => 'mdi',
//            'image/vnd.sealed.png' => 'spng',
//            'image/vnd.sealedmedia.softseal.gif' => 'sgif',
//            'image/vnd.sealedmedia.softseal.jpg' => 'sjpg',
//            'image/vnd.wap.wbmp' => 'wbmp',
            'image/x-bmp' => 'bmp',
//            'image/x-cmu-raster' => 'ras',
//            'image/x-freehand' => 'fh4',
            'image/x-png' => 'png',
//            'image/x-portable-anymap' => 'pnm',
//            'image/x-portable-bitmap' => 'pbm',
//            'image/x-portable-graymap' => 'pgm',
//            'image/x-portable-pixmap' => 'ppm',
//            'image/x-rgb' => 'rgb',
//            'image/x-xbitmap' => 'xbm',
//            'image/x-xpixmap' => 'xpm',
//            'image/x-xwindowdump' => 'xwd'
        );
    }

    /**
     * @static
     * @param $effects
     * @return array
     * @todo: test it
     */
    public static function effectsString2Array($effects)
    {
        $effectResult = array();
        $current = null;
        $lastEffectName = null;

        for ($i = 0, $length = strlen($effects); $i < $length; $i++) {
            if (false !== ($effectName = array_search($effects[$i] . $effects[$i + 1], self::$_effectName))) {
                $i++;
                $lastEffectName = $effectName;
                $effectResult[$effectName] = array();
                continue;
            }

            if (false !== ($paramName = array_search($effects[$i], self::$_paramName))) {
                $value = '';

                while (isset($effects[$i + 1]) && is_numeric($effects[$i + 1])) {
                    $value .= $effects[++$i];
                }

                $effectResult[$lastEffectName][$paramName] = $value;

                continue;
            }
        }

        return $effectResult;
    }

    /**
     * @static
     * @param $effects
     * @return string
     * @todo: test it
     */
    public static function effectsArray2String($effects)
    {
        $result = '';
        foreach ($effects as $key => $effect) {
            $result .= self::$_effectName[$key];
            if (is_array($effect)) {
                foreach ($effect as $key => $val) {
                    $result .= self::$_paramName[$key].$val;
                }
            }
        }

        return $result;
    }
}
