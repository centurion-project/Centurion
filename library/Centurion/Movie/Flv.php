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
 * @package     Centurion_Movie
 * @subpackage  Flv
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Movie
 * @subpackage  Flv
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */

/**
 * Inspired and cleaned from http://www.phpcs.com/codes/LECTURE-CARACTERISTIQUES-FICHIER-FLV_48039.aspx
 * @see http://www.phpcs.com/codes/LECTURE-CARACTERISTIQUES-FICHIER-FLV_48039.aspx
 * @todo clean
 * @todo rename param, function... to Centurion convention.
 * @todo add exception
 * @todo documentation
 */
class Centurion_Movie_Flv
{
    protected $_metadata;
    protected $_filename;
    public $isFLVFile = false;
    public $isReadable = false;
    protected $_fileId;
    protected $_fileSize = 0;
    protected $_debug = false;

    public function __construct($filename)
    {
        if (file_exists($filename)) {
            $this->_filename = $filename;
            $this->_fileSize = filesize($filename);
            $this->_fileId = @fopen($filename, 'rb');

            if ($this->_fileId) {
                $this->isReadable = true;

                $this->_readMetadata();

                fclose($this->_fileId);
            }
        }
    }

    protected function _readMetadata()
    {
        $meta_tags = array('duration', 'width', 'height', 'videodatarate', 'canSeekToEnd', 'videocodecid', 'audiodatarate', 'audiocodecid', 'framerate');

        $buffer = fread($this->_fileId, 256);

        foreach ($meta_tags as $meta_tag) {
            $v = $this->_getStr($buffer, $meta_tag);
            $this->_metadata[$meta_tag] = $this->_bin2double($v);
        }
    }

    public function getMetadata($key = null)
    {
        if ( $key !== null && isset($this->_metadata[$key])) {
            return $this->_metadata[$key];
        }

        if ($key !== null) {
            return null;
        }
        return $this->_metadata;
    }

    protected function _hex2bin($hex)
    {
        $l = strlen($hex);
        $bin = '';
        for ($i = 0; $i < $l; $i = $i + 2) {
            $bin .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $bin;
    }

    protected function _hexreverse($hex)
    {
        $l = strlen($hex);
        $bytes = array();
        for ($i = 0; $i < $l; $i = $i + 2) {
            $bytes[] = $hex[$i] . $hex[$i + 1];
        }
        $reversed_bytes = array_reverse($bytes);
        return implode($reversed_bytes);
    }

    protected function _bin2double($v)
    {
        $hex = bin2hex($v);
        $hex_word = substr($hex, 0, 16);
        $reversed_hex = $this->_hexreverse($hex_word);
        $hex_double = $this->_hex2bin($reversed_hex);
        $unpacked_double = unpack('d', $hex_double);
        return round($unpacked_double[1]);
    }

    protected function _getStr($buf, $tag)
    {
        $p = strpos($buf, $tag);
        if ($p !== false) {
            $deb = $p + strlen($tag) + 1;
            return substr($buf, $deb);
        }
        return '';
    }
}
