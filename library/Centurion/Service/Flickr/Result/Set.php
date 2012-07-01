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
 * @package     Centurion_Service
 * @subpackage  Flickr
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Service
 * @subpackage  Flickr
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Service_Flickr_Result_Set extends Centurion_Service_Flickr_Result_Abstract
{
    protected $_photos = null;
    
    function __get($param)
    {
        if ($param == 'photos') {
            if ($this->_photos === null)
                $this->_getPhotos();
            return $this->_photos;
        }
    }
    
    protected function _getPhotos(array $options = array())
    {
        if (trim($this->id) !== '') {
            $options = array_merge(array('photoset_id' => $this->id), $options);
            $this->_photos = $this->_flickr->call('flickr.photosets.getPhotos', $options);
        }
        
        return $this->_photos;
    }
}
