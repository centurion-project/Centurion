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
 */
class Media_Model_DbTable_Video extends Centurion_Db_Table_Abstract
{
    protected $_primary = 'id';

    protected $_rowClass = 'Centurion_Db_Table_Row';

    protected $_rowsetClass = 'Centurion_Db_Table_Rowset';
    
    protected $_meta = array('verboseName'   => 'video',
                             'verbosePlural' => 'videos');

    protected $_name = 'media_video';

    /**
     * @var Centurion_Movie_Flv
     */
    protected $_movie = null;
    
    public function insert(array $data)
    {
        if (!isset($data['width']) || $data['width'] == 0) {
            $data['width'] = (int) $this->_getWidth($data['local_filename']);
        }
        if (!isset($data['height']) || $data['height'] == 0) {
            $data['height'] = (int) $this->_getHeight($data['local_filename']);
        }
        if (!isset($data['duration']) || $data['duration'] == 0) {
            $data['duration'] = $this->_getDuration($data['local_filename']);
        }
        
        $data[Centurion_Db_Table_Abstract::VERBOSE] = false;
        
        return parent::insert($data);
    }

    /**
     * @param string $filename
     * @todo: We should not use media.uploads_dir here.
     */
    protected function _loadIfNotLoaded($filename)
    {
        if ($this->_movie === null) {
            $this->_movie = Centurion_Movie::factory(
                Centurion_Config_Manager::get('media.uploads_dir') . DIRECTORY_SEPARATOR . $filename
            );
        }
    }

    /**
     * @param $filename
     * @return int
     */
    protected function _getWidth($filename)
    {
        $this->_loadIfNotLoaded($filename);
        return $this->_movie->getMetadata('width');
    }

    /**
     * @param $filename
     * @return int
     */
    protected function _getHeight($filename)
    {
        $this->_loadIfNotLoaded($filename);
        return $this->_movie->getMetadata('height');
    }

    /**
     * @param $filename
     * @return int
     */
    protected function _getDuration($filename)
    {
        $this->_loadIfNotLoaded($filename);
        return $this->_movie->getMetadata('duration');
    }

    /**
     * @return array
     */
    public function getMimeTypes()
    {
        return array(
            'video/x-flv' => 'flv',
            /*
            'application/x-shockwave-flash' => 'swf',
            'video/3gpp' => '3gp',
            'video/dl' => 'dl',
            'video/gl' => 'gl',
            'video/mj2' => 'mj2',
            'video/mpeg' => 'mpeg',
            'video/quicktime' => 'mov',
            'video/vdo' => 'vdo',
            'video/vivo' => 'viv',
            'video/vnd.fvt' => 'fvt',
            'video/vnd.mpegurl' => 'mxu',
            'video/vnd.nokia.interleaved-multimedia' => 'nim',
            'video/vnd.objectvideo' => 'mp4',
            'video/vnd.sealed.mpeg1' => 's11',
            'video/vnd.sealed.mpeg4' => 'smpg',
            'video/vnd.sealed.swf' => 'sswf',
            'video/vnd.sealedmedia.softseal.mov' => 'smov',
            'video/vnd.vivo' => 'vivo',
            'video/x-fli' => 'fli',
            'video/x-ms-asf' => 'asf',
            'video/x-ms-wmv' => 'wmv',
            'video/x-msvideo' => 'avi',
            'video/x-sgi-movie' => 'movie',
            */
        );
    }
}
