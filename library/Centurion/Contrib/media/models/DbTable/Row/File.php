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
class Media_Model_DbTable_Row_File extends Centurion_Db_Table_Row_Abstract
{
    protected $_proxy = null;

    static protected $_options = null;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->filename;
    }

    public function init()
    {
        $this->_specialGets['permalink'] = 'getStaticUrl';

        return parent::init();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return file_get_contents(Centurion_Config_Manager::get('media.uploads_dir') . PATH_SEPARATOR . $this->local_filename);
    }

    public function getProxy()
    {
        return $this->_getProxy($this->proxy_model, $this->proxy_pk);
    }

    public function getBelong()
    {
        return $this->_getProxy($this->belong_model, $this->belong_pk);
    }

    public function is($type)
    {
        $proxies = $this->getTable()->getDependentProxies();
        if (!array_key_exists($type, $proxies)) {
            throw new Centurion_Exception(sprintf('This type "%s" is not a valid proxy', $type));
        }

        return ($proxies[$type] === $this->proxy_model);
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        return $this->is('images');
    }

    /**
     * @return bool
     */
    public function isVideo()
    {
        return $this->is('videos');
    }

    public function getFullPath($path = null)
    {
        if ($path == null) {
            $path = $this->local_filename;
        }

        if (!is_file($path)) {
            $path = Centurion_Config_Manager::get('media.uploads_dir')
                . DIRECTORY_SEPARATOR
                . $path;
        }

        return $path;
    }

    protected function _populateData()
    {
        if (null == $this->mime) {
            //TODO: find a better way to get mime type with Zend.
            $this->mime = $this->getMimeType($this->getFullPath($this->local_filename));
        }

        if (null == $this->filesize) {
            $this->filesize = filesize($this->getFullPath($this->local_filename));
        }

        if (null == $this->filename) {
            $this->filename = basename($this->getFullPath($this->local_filename));
        }

        if (null == $this->sha1) {
            $this->sha1 = sha1_file($this->getFullPath($this->local_filename));
        }

        $row = $this->getTable()->fetchRow(array('sha1=?' => $this->sha1, 'filesize=?' => $this->filesize));
        //We want to be sure
        if ($row !== null && $this->sha1== $row->sha1 && $this->filesize == $row->filesize) {
            //We reuse the same local filename
            if (((null === $this->delete_original) || ($this->delete_original == 1)) && ((null === $row->delete_original) || ($row->delete_original == 1)) && ($this->local_filename !== $row->local_filename)) {
                unlink($this->getFullPath($this->local_filename));
            }

            $this->file_id = $row->file_id;
            $this->local_filename = $row->local_filename;
            $this->mime = $row->mime;
            $this->filesize = $row->filesize;
            $this->proxy_model = $row->proxy_model;
            $this->proxy_pk = $row->proxy_pk;
            $this->belong_model = $row->belong_model;
            $this->belong_pk = $row->belong_pk;
        }

        if (null == $this->file_id) {
            $this->file_id = sha1(rand());
        }
    }

    public function _insert()
    {
        if (null == $this->id) {
            $this->id = md5(Centurion_Inflector::uniq(uniqid()));
        }

        $this->_populateData();
    }

    public function _update()
    {
        if ($this->_cleanData['local_filename'] !== $this->_data['local_filename']) {
            $this->file_id = null;
            $this->filename = null;
            $this->sha1 = null;
            $this->mime = null;
            $this->filesize = null;
        }

        $this->_populateData();
    }

    public function getRelativePathFromTo($from, $to, $forceRealPath = true)
    {
        if ($forceRealPath) {
            $from = realpath($from);
            $to = realpath($to);
        }

        $relative = '';

        $currentTab = preg_split('`[/\\\\]`', $from);
        $toTab = preg_split('`[/\\\\]`', $to);

        $separated = false;
        $separatedAt = 1;

        foreach ($currentTab as $key => $val) {
            if (isset($toTab[$key]) && $toTab[$key] !== $val) {
                $separated = true;
                $separatedAt = $key;
            }
            if ($separated) {
                $relative .= '..' . DIRECTORY_SEPARATOR;
            }
        }

        $relative .= implode(DIRECTORY_SEPARATOR, array_slice($toTab, $separatedAt));

        return $relative;
    }

    protected function _getProxy($model, $pk)
    {
        if (null !== $model) {
            if (null === $this->_proxy) {
                $proxyTable = Centurion_Db::getSingletonByClassName($model);
                if (null !== $pk) {
                    $this->_proxy = $proxyTable->findOneById($pk);
                }
            }

            return $this->_proxy;
        }

        return false;
    }

    static public function getOptions($key = null)
    {
        if (null === self::$_options) {
            self::$_options = Centurion_Config_Manager::get('media');
        }

        if ($key !== null)
            return self::$_options[$key];
        return self::$_options;
    }

    /**
     * @return string
     * @todo : corriger, si un chemin relatif concat avec upload_dir
     * @todo : utiliser cette fonction à la place d'upload_dir
     */
    public function getFullLocalPath()
    {
        if (file_exists($this->local_filename)) {
            return $this->local_filename;
        }

        return self::getOptions('uploads_dir') . DIRECTORY_SEPARATOR . $this->local_filename;
    }

    /**
     * @return int
     */
    public function delete()
    {
        if ($this->delete_original == '1') {
            if ($this->file_id !== null && $this->getTable()->select(true)->where('file_id=?', $this->file_id)->count() == 1) {
                unlink($this->getFullLocalPath());
            }
        }

        return parent::delete();
    }

    /**
     * @param null $effects
     * @param bool $extra
     * @param bool $realPath
     * @return string
     * @todo manage extra param
     */
    public function getRelativePath($effects = null, $extra = false, $realPath = false)
    {
        if (is_array($effects)) {
            $effects = Media_Model_DbTable_Image::effectsArray2String($effects);
        }

        return Centurion_Inflector::urlEncode(pack("H*" , $this->file_id)) . '/_' . ((null !== $effects) ? $effects:'') . '.centurion';
    }

    /**
     * @param $value
     * @param null $file
     * @return bool
     */
    public function getMimeType($value, $file = null)
    {
        if ($file === null) {
            $file = array(
                'type' => null,
                'name' => $value
            );
        }

        // Is file readable ?
        //$1 'Zend/Loader.php';
        if (!Zend_Loader::isReadable($value)) {
            return $this->_throw($file, self::NOT_READABLE);
        }

        $validate = new Zend_Validate_File_MimeType('');

        $mimefile = $validate->getMagicFile();
        if (class_exists('finfo', false)) {
            $const = defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
            if (!empty($mimefile) && empty($finfo)) {
                $finfo = @finfo_open($const, $mimefile);
            }

            if (empty($finfo)) {
                $finfo = @finfo_open($const);
            }

            $type = null;
            if (!empty($finfo)) {
                $type = finfo_file($finfo, $value);
            }
        }

        if (empty($type) &&
            (function_exists('mime_content_type') && ini_get('mime_magic.magicfile'))) {
            $type = mime_content_type($value);
        }

        if (empty($type) && $this->_headerCheck) {
            $type = $file['type'];
        }

        if (empty($type)) {
            return $this->_throw($file, self::NOT_DETECTED);
        }

        return $type;
    }

    public function getSeoUrl($effects = null, $extra = false, $realPath = false)
    {
        if (is_array($effects))
            $effects = Media_Model_DbTable_Image::effectsArray2String($effects);

        $fileId = Centurion_Inflector::urlEncode(pack("H*" , $this->file_id));

        /*if (!$realPath && $this->file_id === $this->pk)
            $pk = '';
        else */
            $pk = Centurion_Inflector::urlEncode(pack("H*" , $this->pk));
        $key = Centurion_Inflector::urlEncode(pack("H*" , $this->getTemporaryKey($effects)));

        return $this->getDateObjectByCreatedAt()->toString('y/MM/dd/')
               . (($realPath)?$pk . '_'. ((null !== $effects) ? $effects . '_':'_'):'')
               . $this->filename
               . (($extra) ? '?' . $pk . ':' . $fileId . ':' . $key . ((null !== $effects) ? ':' . $effects:''):'');
    }

    public function getStaticUrl($effects = null)
    {
        if (is_array($effects)) {
            $effects = Media_Model_DbTable_Image::effectsArray2String($effects);
        }

        $url = $this->getSeoUrl($effects, true);

        $url = Media_Model_DbTable_File::getMediaAdapter()->getUrl($url);

        return $url;
    }

    /**
     * @param $effect
     * @param Zend_Date|int $mktime
     * @return string
     */
    public function getTemporaryKey($effect, $mktime = null)
    {
        return Media_Model_DbTable_File::getMediaAdapter()->getTemporaryKey($this->pk, $effect, $mktime);
    }
}
