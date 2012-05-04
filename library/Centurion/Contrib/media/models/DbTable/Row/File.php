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
 * @author      Laurent Chenay <lc@octaveoctave.com>
 */
class Media_Model_DbTable_Row_File extends Centurion_Db_Table_Row_Abstract
{
    protected $_proxy = null;

    static protected $_options = null;

    public function __toString()
    {
        return $this->filename;
    }

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

        return $proxies[$type] === $this->proxy_model;
    }

    public function isImage()
    {
        return $this->is('images');
    }

    public function isVideo()
    {
        return $this->is('videos');
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
     * @todo : utiliser cette fonction ? la place d'upload_dir
     */
    public function getFullLocalPath()
    {
        if (file_exists($this->local_filename)) {
            return $this->local_filename;
        }

        return self::getOptions('uploads_dir') . DIRECTORY_SEPARATOR . $this->local_filename;
    }

    public function delete()
    {
        parent::delete();

        if ($this->delete_original == 1) {
            if ($this->file_id !== null && $this->getTable()->select(true)->where('file_id=?', $this->file_id)->count() == 1) {
                unlink($this->getFullLocalPath());
            }
        }

    }

    public function getRelativePath($effects = null, $extra = false, $realPath = false)
    {
        if (is_array($effects)) {
            $effects = Media_Model_DbTable_Image::effectsArray2String($effects);
        }

        return Centurion_Inflector::urlEncode(pack("H*" , $this->file_id)) . '/_' . ((null !== $effects) ? $effects:'') . '.centurion';
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

    public function getTemporaryKey($effect, $mktime = null)
    {
        return Media_Model_DbTable_File::getMediaAdapter()->getTemporaryKey($this->pk, $effect, $mktime);
    }

    /**
     * Counts how many times this media is used throughout the application
     * @fixme uses of media in rte fields is not taken into account
     */
    public function countTimesUsed()
    {
        $dependentTables = $this->getTable()->getDependentTables();
        $count = 0;
        foreach ($dependentTables as $key => $tableClassName) {
            // @todo move this ignorelist elsewhere
            // ignore these tables as it is only tags on media
            if(in_array($key, array('tag_files', 'duplicates'))) continue;
            $model = Centurion_Db::getSingletonByClassName($tableClassName);
            $references = $this->getTable()->getReferencesInTable($model);
            foreach ($references as $key => $ref) {
                $count += $model->select(true)->filter(array($key.'__id'=>$this->id))->count();
            }
        }
        return $count;
    }

}
