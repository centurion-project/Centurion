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
class Centurion_Service_Flickr_Result_Photo extends Centurion_Service_Flickr_Result_Abstract
{
    /**
     * 
     * @param DOMElement $element
     * @param Centurion_Service_Flickr $flickr
     * @return void
     */
    public function __construct(DOMElement $element, Centurion_Service_Flickr $flickr)
    {
        parent::__construct($element, $flickr);
        
        //Depending form witch api key this class has been construct, thumbs urls can be found in the DOMElement (save one call)
        $rewriteParam = array(
            'url_sq' => 'thumbSquare',
            'url_t'  => 'thumbThumbnail',
            'url_s'  => 'thumbSmall',
            'url_m'  => 'thumbSmall',
            'url_o'  => 'thumbOriginal'
        );
        
        foreach ($rewriteParam as $param => $realParam) {
            if (isset($this->{$param}) && !isset($this->{$realParam}))
                $this->{$realParam} = $this->{$param};
        }
    }
    
    /**
     * 
     * @param string $param
     * @return mixed
     */
    public function __get($param)
    {
        /**
         * Transparent autoloading of size.
         * Depending form witch api key this class has been construct, size may not be send to construct.
         */
        if (in_array($param, array('thumbSquare', 'thumbThumbnail', 'thumbSmall', 'thumbMedium', 'thumbOriginal'))) {
            $this->_loadSize();
            return $this->{$param};
        }
        
        /**
         * Transparent autoloading of params.
         * Depending form witch api key this class has been construct, some params may not be send to construct.
         */
        if (in_array($param, array('description', 'secret', 'license', 'rotation', 'isfavorite', 'originalsecret', 'originalformat', 
                    'owner', 'title', 'description', 'visibility', 'dates', 'permissions', 'editability', 'comments', 'notes', 'tags', 'urls'))) {
            $this->_getInfos();
            return $this->{$param};
        }
    }
    
    /**
     * Load all infos for this photo
     * 
     * @return void
     */
    protected function _getInfos()
    {
        $dom = $this->_flickr->call('flickr.photos.getInfo', array('photo_id' => $this->id), true);
        
        $xpath = new DOMXPath($dom);
        
        $tags = $xpath->query('//tags')->item(0);
        $tags = $xpath->query('tag', $tags);
        $this->tags = new Centurion_Service_Flickr_ResultSet($tags, $this->_flickr, 'tag');
        
        $photo = $xpath->query('//photo')->item(0);
        
        foreach ($xpath->query('./@*', $photo) as $property) {
            $this->{$property->name} = (string) $property->value;
        }
        
        $this->owner = new Centurion_Service_Flickr_Result($xpath->query('//owner')->item(0), $this->_flickr);
        
        $this->description = $xpath->query('//description')->item(0)->nodeValue;
        $this->title = $xpath->query('//title')->item(0)->nodeValue;
    }
    
    /**
     * Load all thumb's url of this photo
     * 
     * @return void
     */
    protected function _loadSize()
    {
        $dom = $this->_flickr->call('flickr.photos.getSizes', array('photo_id' => $this->id), true);
        
        $xpath = new DOMXPath($dom);
        $sizes = $xpath->query('//size');
        foreach ($sizes as $size) {
            $this->{'thumb'.$size->getAttribute('label')} = $size->getAttribute('source');
        }
    }
}
