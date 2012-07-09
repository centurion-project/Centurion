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
class Centurion_Service_Flickr extends Zend_Service_Flickr
{
    /**
     * All api allowed by flickr.
     * Syntax : 
     *  'apikey' => array(
     *      'itemNode'       => 'nodeClassName', //Optional.
            'setNode'        => 'nodesetClassName', //Optional.
     *      'resultType'     => 'nodeClassName', //Optional. If set : $itemNode = $resultType and $setNode = $resultType . 'set';
     *      'defaultOptions' => array('optionName' => 'value', ...), //Optional. All default values
     *      'allowedOptions' => array('optionName' => false|true, ... ), //Optional. the boolean indicate if it required (true) or just allowed (false). 
     *  )
     * @todo Fill all apikey
     * @var array[string][string]mixed
     */
    static protected $_methods = array(
            'flickr.activity.userComments' => array(),
            'flickr.activity.userPhotos' => array(),
            'flickr.auth.checkToken' => array(),
            'flickr.auth.getFrob' => array(),
            'flickr.auth.getFullToken' => array(),
            'flickr.auth.getToken' => array(),
            'flickr.blogs.getList' => array(),
            'flickr.blogs.getServices' => array(),
            'flickr.blogs.postPhoto' => array(),
            'flickr.collections.getInfo' => array(),
            'flickr.collections.getTree' => array(
                    'resultType' => 'collection',
                    'defaultOptions' => array(),
                    'allowedOptions' => array('collection_id' => false, 'user_id' => false)
            ),
            'flickr.commons.getInstitutions' => array(),
            'flickr.contacts.getList' => array(),
            'flickr.contacts.getListRecentlyUploaded' => array(),
            'flickr.contacts.getPublicList' => array(),
            'flickr.favorites.add' => array(),
            'flickr.favorites.getList' => array(),
            'flickr.favorites.getPublicList' => array(),
            'flickr.favorites.remove' => array(),
            'flickr.galleries.addPhoto' => array(),
            'flickr.galleries.create' => array(),
            'flickr.galleries.editMeta' => array(),
            'flickr.galleries.editPhoto' => array(),
            'flickr.galleries.editPhotos' => array(),
            'flickr.galleries.getInfo' => array(),
            'flickr.galleries.getList' => array(),
            'flickr.galleries.getListForPhoto' => array(),
            'flickr.galleries.getPhotos' => array(),
            'flickr.groups.browse' => array(),
            'flickr.groups.getInfo' => array(),
            'flickr.groups.search' => array(),
            'flickr.groups.members.getList' => array(),
            'flickr.groups.pools.add' => array(),
            'flickr.groups.pools.getContext' => array(),
            'flickr.groups.pools.getGroups' => array(),
            'flickr.groups.pools.getPhotos' => array(),
            'flickr.groups.pools.remove' => array(),
            'flickr.interestingness.getList' => array(),
            'flickr.machinetags.getNamespaces' => array(),
            'flickr.machinetags.getPairs' => array(),
            'flickr.machinetags.getPredicates' => array(),
            'flickr.machinetags.getRecentValues' => array(),
            'flickr.machinetags.getValues' => array(),
            'flickr.panda.getList' => array(),
            'flickr.panda.getPhotos' => array(),
            'flickr.people.findByEmail' => array(),
            'flickr.people.findByUsername' => array(),
            'flickr.people.getInfo' => array(),
            'flickr.people.getPhotos' => array(),
            'flickr.people.getPhotosOf' => array(),
            'flickr.people.getPublicGroups' => array(),
            'flickr.people.getPublicPhotos' => array(
                    'resultType' => 'photo',
                    'defaultOptions' => array('per_page' => 10, 'page' => 1, 'extras' => 'license, date_upload, date_taken, owner_name, icon_server'),
                    'allowedOptions' => array()
                ),
            'flickr.people.getUploadStatus' => array(),
            'flickr.photos.addTags' => array(),
            'flickr.photos.delete' => array(),
            'flickr.photos.getAllContexts' => array(),
            'flickr.photos.getContactsPhotos' => array(),
            'flickr.photos.getContactsPublicPhotos' => array(),
            'flickr.photos.getContext' => array(),
            'flickr.photos.getCounts' => array(),
            'flickr.photos.getExif' => array(),
            'flickr.photos.getFavorites' => array(),
            'flickr.photos.getInfo' => array(),
            'flickr.photos.getNotInSet' => array(),
            'flickr.photos.getPerms' => array(),
            'flickr.photos.getRecent' => array(),
            'flickr.photos.getSizes' => array(
                    'resultType' => null,
                    'defaultOptions' => array(),
                    'allowedOptions' => array('photo_id' => true)
                ),
            'flickr.photos.getUntagged' => array(),
            'flickr.photos.getWithGeoData' => array(),
            'flickr.photos.getWithoutGeoData' => array(),
            'flickr.photos.recentlyUpdated' => array(),
            'flickr.photos.removeTag' => array(),
            'flickr.photos.search' => array(),
            'flickr.photos.setContentType' => array(),
            'flickr.photos.setDates' => array(),
            'flickr.photos.setMeta' => array(),
            'flickr.photos.setPerms' => array(),
            'flickr.photos.setSafetyLevel' => array(),
            'flickr.photos.setTags' => array(),
            'flickr.photos.comments.addComment' => array(),
            'flickr.photos.comments.deleteComment' => array(),
            'flickr.photos.comments.editComment' => array(),
            'flickr.photos.comments.getList' => array(),
            'flickr.photos.comments.getRecentForContacts' => array(),
            'flickr.photos.geo.batchCorrectLocation' => array(),
            'flickr.photos.geo.correctLocation' => array(),
            'flickr.photos.geo.getLocation' => array(),
            'flickr.photos.geo.getPerms' => array(),
            'flickr.photos.geo.photosForLocation' => array(),
            'flickr.photos.geo.removeLocation' => array(),
            'flickr.photos.geo.setContext' => array(),
            'flickr.photos.geo.setLocation' => array(),
            'flickr.photos.geo.setPerms' => array(),
            'flickr.photos.licenses.getInfo' => array(),
            'flickr.photos.licenses.setLicense' => array(),
            'flickr.photos.notes.add' => array(),
            'flickr.photos.notes.delete' => array(),
            'flickr.photos.notes.edit' => array(),
            'flickr.photos.people.add' => array(),
            'flickr.photos.people.delete' => array(),
            'flickr.photos.people.deleteCoords' => array(),
            'flickr.photos.people.editCoords' => array(),
            'flickr.photos.people.getList' => array(),
            'flickr.photos.transform.rotate' => array(),
            'flickr.photos.upload.checkTickets' => array(),
            'flickr.photosets.addPhoto' => array(),
            'flickr.photosets.create' => array(),
            'flickr.photosets.delete' => array(),
            'flickr.photosets.editMeta' => array(),
            'flickr.photosets.editPhotos' => array(),
            'flickr.photosets.getContext' => array(),
            'flickr.photosets.getInfo' => array(
                    'resultType' => null,
                    'defaultOptions' => array(),
                    'allowedOptions' => array('photoset_id' => true)
                ),
            'flickr.photosets.getList' => array(),
            'flickr.photosets.getPhotos' => array(
                    'itemNode' => 'photo',
                    'setNode' => 'photoset',
                    'resultType' => 'photo',
                    'defaultOptions' => array('per_page' => 500, 'page' => 1, 'media' => 'all', 'extras' => 'license, date_upload, date_taken, owner_name, icon_server, original_format, last_update, geo, machine_tags, o_dims, views, media, path_alias, url_sq, url_t, url_s, url_m, url_o'),
                    'allowedOptions' => array('photoset_id' => true, 'extras' => false, 'privacy_filter' => false, 'per_page' => false, 'page' => false, 'media' => false)
                ),
            'flickr.photosets.orderSets' => array(),
            'flickr.photosets.removePhoto' => array(),
            'flickr.photosets.comments.addComment' => array(),
            'flickr.photosets.comments.deleteComment' => array(),
            'flickr.photosets.comments.editComment' => array(),
            'flickr.photosets.comments.getList' => array(),
            'flickr.places.find' => array(),
            'flickr.places.findByLatLon' => array(),
            'flickr.places.getChildrenWithPhotosPublic' => array(),
            'flickr.places.getInfo' => array(),
            'flickr.places.getInfoByUrl' => array(),
            'flickr.places.getPlaceTypes' => array(),
            'flickr.places.getShapeHistory' => array(),
            'flickr.places.getTopPlacesList' => array(),
            'flickr.places.placesForBoundingBox' => array(),
            'flickr.places.placesForContacts' => array(),
            'flickr.places.placesForTags' => array(),
            'flickr.places.placesForUser' => array(),
            'flickr.places.resolvePlaceId' => array(),
            'flickr.places.resolvePlaceURL' => array(),
            'flickr.places.tagsForPlace' => array(),
            'flickr.prefs.getContentType' => array(),
            'flickr.prefs.getGeoPerms' => array(),
            'flickr.prefs.getHidden' => array(),
            'flickr.prefs.getPrivacy' => array(),
            'flickr.prefs.getSafetyLevel' => array(),
            'flickr.reflection.getMethodInfo' => array(),
            'flickr.reflection.getMethods' => array(),
            'flickr.stats.getCollectionDomains' => array(),
            'flickr.stats.getCollectionReferrers' => array(),
            'flickr.stats.getCollectionStats' => array(),
            'flickr.stats.getCSVFiles' => array(),
            'flickr.stats.getPhotoDomains' => array(),
            'flickr.stats.getPhotoReferrers' => array(),
            'flickr.stats.getPhotosetDomains' => array(),
            'flickr.stats.getPhotosetReferrers' => array(),
            'flickr.stats.getPhotosetStats' => array(),
            'flickr.stats.getPhotoStats' => array(),
            'flickr.stats.getPhotostreamDomains' => array(),
            'flickr.stats.getPhotostreamReferrers' => array(),
            'flickr.stats.getPhotostreamStats' => array(),
            'flickr.stats.getPopularPhotos' => array(),
            'flickr.stats.getTotalViews' => array(),
            'flickr.tags.getClusterPhotos' => array(),
            'flickr.tags.getClusters' => array(),
            'flickr.tags.getHotList' => array(),
            'flickr.tags.getListPhoto' => array(),
            'flickr.tags.getListUser' => array(),
            'flickr.tags.getListUserPopular' => array(),
            'flickr.tags.getListUserRaw' => array(),
            'flickr.tags.getRelated' => array(),
            'flickr.test.echo' => array(),
            'flickr.test.login' => array(),
            'flickr.test.null' => array(),
            'flickr.urls.getGroup' => array(),
            'flickr.urls.getUserPhotos' => array(),
            'flickr.urls.getUserProfile' => array(),
            'flickr.urls.lookupGallery' => array(),
            'flickr.urls.lookupGroup' => array(),
            'flickr.urls.lookupUser' => array(),
    );
    
    /**
     * 
     * @param string $apiKey Flickr api key. If not given, it will try to find it in config.
     */
    public function __construct($apiKey = null)
    {
        if (null === $apiKey) {
            $apiKey = Centurion_Config_Manager::get('flickr.apikey');
        }
        
        parent::__construct($apiKey);
    }

    public function call($method, $options, $returnDom = false)
    {
        if (!isset(self::$_methods[$method])) {
            throw new Zend_Service_Exception(sprintf('Method %s does not exists', $method));
        }
        
        $defaultOptions = null;
        $allowedOptions = null;
        $type = null;
        if (isset(self::$_methods[$method]['defaultOptions']))
            $defaultOptions = self::$_methods[$method]['defaultOptions'];
        if (isset(self::$_methods[$method]['allowedOptions']))
            $allowedOptions = self::$_methods[$method]['allowedOptions'];
        if (isset(self::$_methods[$method]['type']))
            $type = self::$_methods[$method]['type'];
        
        $options = $this->_prepareOptions2($method, $options, $defaultOptions, $allowedOptions);
        
        $restClient = $this->getRestClient();
        $restClient->getHttpClient()->resetParameters();
        $response = $restClient->restGet('/services/rest/', $options);
        
        if ($response->isError()) {
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' . $response->getStatus());
        }
        
        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        
        self::_checkErrors($dom);
        
        if ($returnDom)
            return $dom;
        
        $className = 'Centurion_Service_Flickr_ResultSet_' . ucfirst(self::$_methods[$method]['resultType']);
        
        if (class_exists($className, true)) {
            return new $className($dom, $this);
        }
        
        self::$_methods[$method] = array_merge(array('resultType' => null, 'itemNode' => null, 'setNode' => null), self::$_methods[$method]);
        
        return new Centurion_Service_Flickr_ResultSet($dom, $this, self::$_methods[$method]['resultType'], self::$_methods[$method]['itemNode'], self::$_methods[$method]['setNode']);
    }
    
    protected function _userSearchById(array $options = null)
    {
        static $method = 'flickr.people.getPublicPhotos';
        static $defaultOptions = array('per_page' => 10, 'page' => 1, 'extras' => 'license, date_upload, date_taken, owner_name, icon_server');
        
        $options = $this->_prepareOptions2($method, $options, $defaultOptions);
        $this->_validateUserSearch($options);
        
        // now search for photos
        $restClient = $this->getRestClient();
        $restClient->getHttpClient()->resetParameters();
        $response = $restClient->restGet('/services/rest/', $options);
        
        if ($response->isError()) {
            throw new Zend_Service_Exception('An error occurred sending request. Status code: ' . $response->getStatus());
        }
        
        $dom = new DOMDocument();
        $dom->loadXML($response->getBody());
        
        self::_checkErrors($dom);
        
        /**
         * @see Zend_Service_Flickr_ResultSet
         */
        require_once 'Zend/Service/Flickr/ResultSet.php';
        return new Zend_Service_Flickr_ResultSet($dom, $this);
    }

    public function userSearch($query, array $options = null)
    {
        // can't access by username, must get ID first
        if (strchr($query, '@')) {
            // optimistically hope this is an email
            $options['user_id'] = $this->getIdByEmail($query);
        } else {
            // we can safely ignore this exception here
            $options['user_id'] = $this->getIdByUsername($query);
        }
        
        return $this->_userSearchById($options);
    }

    public function userSearchById($userId, array $options = null)
    {
        $options['user_id'] = $userId;
        
        return $this->_userSearchById($options);
    }
    
    protected function _prepareOptions2($method, array $options, $defaultOptions = array(), $allowedOptions = null)
    {        
        $options['method']  = (string) $method;
        $options['api_key'] = $this->apiKey;

        if (null !== $defaultOptions)
            $options = array_merge($options, $defaultOptions);
        
        if (null !== $allowedOptions) {
            $allowedOptions['method'] = true;
            $allowedOptions['api_key'] = true;
            
            $diff = array_diff(array_keys($options), array_keys($allowedOptions));
            if (count($diff) > 0) {
                throw new Zend_Service_Exception(sprintf('You tried to use some option that are not allowed : %s', implode(', ', $diff)));
            }
            
            $requiredOptions = array_filter($allowedOptions);
            
            $intersect = array_intersect(array_keys($requiredOptions), array_keys($options));
            if (count($intersect) != count($requiredOptions)) {
                throw new Zend_Service_Exception(sprintf('You forget some options. These are required : %s', implode(', ', array_keys(array_diff($intersect, $requiredOptions)))));
            }
        }
        
        return $options;
    }
}
