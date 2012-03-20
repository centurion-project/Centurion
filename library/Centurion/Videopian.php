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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Denis Hovart <denis@upian.com>
 * @author      Hans Lemuet <hans@upian.com>
 * @author      Florent Messa <florent.messa@gmail.com>
 */

/**
 * @TODO: This should be in it's own file
 */
class Centurion_Videopian_Exception extends Exception
{
    const SERVICE_NOT_SUPPORTED = 'Unable to get the video data. Please make sure the service you are trying to use is supported by Videopian.';
}

class Centurion_Videopian
{

    # ================================================================================
    # Specify here the API keys for the services you want to use.
    # You'll need to request one for each.

    const IMEEM_API_KEY            = '';
    const IMEEM_API_SECRET        = '';
    const VEOH_API_KEY            = '';
    const FLICKR_API_KEY        = '';
    const SEVENLOAD_API_KEY        = '';
    const VIDDLER_API_KEY        = '';
    const REVVER_LOGIN            = '';
    const REVVER_PASSWORD        = '';
    
    # ================================================================================
    # Process the URL to extract the service and the video id
    public static function getService($url)
    {
        $url = preg_replace('#\#.*$#', '', trim($url));
        
        if (!preg_match('#http://#', $url))
            $url = 'http://' . $url;
        
        $servicesRegexp = array(
            '#blip\.tv.*/file/([0-9]*)#i'                                       =>  'blip',
            '#dailymotion\.com.*/(video|swf)/(?P<id>[^_]*)#i'                   =>  'dailymotion',
            '#flickr\.com.*/photos/[a-zA-Z0-9]*/([^/]*)#'                       =>  'flickr',
            '#video\.google\..{0,5}/.*[\?&]docid=([^&]*)#i'                     =>  'googlevideo',
            '#metacafe\.com/watch/(.[^/]*)#i'                                   =>  'metacafe',
            '#myspace\.com/.*[\?&]videoid=(.*)#i'                               =>  'myspace',
            '#revver\.com/video/([^/]*)#i'                                      =>  'revver',
            '#sevenload.com/.*/(videos|episodes)/(?P<id>[^-]*)#i'               =>  'sevenload',
            '#veoh\.com/.*/([^?&]*)/?#i'                                        =>  'veoh',
            '#vimeo\.com\/(moogaloop\.swf\?clip_id=)?(?P<id>[0-9]*)[\/\?]?#i'   =>  'vimeo',
            '#youtube\..{0,5}/.*[\?&]?v(\/|=)(?P<id>[^&]*)#i'                   =>  'youtube'
        );
        
        foreach ($servicesRegexp as $pattern => $service) {
            if (preg_match($pattern, $url, $matches)) {
                return array($service, isset($matches['id']) ? $matches['id']:$matches[1]);
            }
        }
        
        throw new Centurion_Videopian_Exception(Centurion_Videopian_Exception::SERVICE_NOT_SUPPORTED);
    }
    
    # ================================================================================
    # Fetch and return the video data
    public static function get($url)
    {
        list($service, $id) = self::getService($url);
        
        $video = new stdClass();
        $video->url = $url;
        $video->site = $service;
        
        $method = sprintf('get%s', ucfirst($service), array($id, $video));
        if (!is_callable(array(__CLASS__, $method))) {
            throw new Centurion_Videopian_Exception(Centurion_Videopian_Exception::SERVICE_NOT_SUPPORTED);
        }
        
        return call_user_func_array(array(__CLASS__, $method), array($id, $video));
    }

    /**
     * @TODO: All function below must be in it's own class file adapter
     */

    public static function getBlip($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        $fileData = "http://blip.tv/file/".$id."?skin=rss";
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Title
        $title_query = $xml->xpath('/rss/channel/item/title');
        $video->title = $title_query ? strval($title_query[0]) : null;
        
        # Description
        $description_query = $xml->xpath('/rss/channel/item/blip:puredescription');
        $video->description = $description_query ? strval(trim($description_query[0])) : null;
        
        # Tags
        $tags_query = $xml->xpath('/rss/channel/item/media:keywords');
        $video->tags = $tags_query ? explode(', ',strval(trim($tags_query[0]))) : null;
        
        # Duration
        $duration_query = $xml->xpath('/rss/channel/item/blip:runtime');
        $video->duration = $duration_query ? intval($duration_query[0]) : null;
        
        # Author & author URL
        $author_query = $xml->xpath('/rss/channel/item/blip:user');
        $video->author = $author_query ? strval($author_query[0]) : null;
        $author_safe_query = $xml->xpath('/rss/channel/item/blip:safeusername');
        $video->author_url = 'http://'.strval($author_safe_query[0]).'.blip.tv';
        
        # Publication date
        $date_published_query = $xml->xpath('/rss/channel/item/blip:datestamp');
        $video->date_published = $date_published_query ? new DateTime($date_published_query[0]) : null;
        
        # Last update date
        $video->date_updated = null;

        # Thumbnails
        $thumbnails_query = $xml->xpath('/rss/channel/item/blip:smallThumbnail');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnails_query[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnails_query = $xml->xpath('/rss/channel/item/media:thumbnail/@url');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnails_query[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $player_url_query = $xml->xpath('/rss/channel/item/blip:embedUrl');
        $video->player_url = $player_url_query ? strval($player_url_query[0]) : null;
        
        # FLV file URL
        $flv_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-flv"]/@url');
        $video->files['video/x-flv'] = $flv_url_query ? strval($flv_url_query[0]) : null;
        
        # MOV file URL
        $mov_url_query = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/quicktime"]/@url');
        $video->files['video/quicktime'] = $mov_url_query ? strval($mov_url_query[0]) : null;
        
        return $video;
    }
    
    public static function getDailymotion($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # XML data URL
        $fileData = 'http://www.dailymotion.com/rss/video/'.$id;
        $video->xml_url = $fileData;
        $video->id = $id;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Title
        $title_query = $xml->xpath('/rss/channel/item/title');
        $video->title = $title_query ? strval($title_query[0]) : null;
        
        # Description
        $description_query = $xml->xpath('/rss/channel/item/itunes:summary');
        $video->description = $description_query ? strval(trim($description_query[0])) : null;
        
        # Tags
        $tagsQuery = $xml->xpath('/rss/channel/item/itunes:keywords');
        $video->tags = $tagsQuery ? explode(', ',strval(trim($tagsQuery[0]))) : null;
        
        # Duration
        $durationQuery = $xml->xpath('/rss/channel/item/media:group/media:content/@duration');
        $video->duration = $durationQuery ? intval($durationQuery[0]) : null;
        
        # Author & author URL
        $authorQuery = $xml->xpath('/rss/channel/item/dm:author');
        $video->author = $authorQuery ? strval($authorQuery[0]) : null;
        $video->author_url = 'http://www.dailymotion.com/'.$video->author;
        
        # Publication date
        $datePublishedQuery = $xml->xpath('/rss/channel/item/pubDate');
        $video->date_published = $datePublishedQuery ? new DateTime($datePublishedQuery[0]) : null;
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnail = new stdClass();
        $thumbnail->url = 'http://www.dailymotion.com/thumbnail/320x240/video/'.$id;
        $thumbnail->width = 320;
        $thumbnail->height = 240;
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $video->player_url = 'http://www.dailymotion.com/swf/'.$id;
        
        # FLV file URL
        $flvUrlQuery = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-flv"]/@url');
        $video->files['video/x-flv'] = $flvUrlQuery ? strval($flvUrlQuery[0]) : null;
        
        return $video;
    }
    
    public static function getFlickr($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # API key check
        if (!self::FLICKR_API_KEY)
            throw new Exception('You need to request an api key in order to grab video information from Flickr.');
        
        # XML data URL
        $fileData = 'http://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=' . self::FLICKR_API_KEY . '&photo_id=' . $id;
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Media type check
        $mediaQuery = $xml->xpath('/rsp/photo/@media');
        if ($mediaQuery[0] != 'video')
            throw new Exception('The media you are trying to get from Flickr is not a video.');
        
        # Title
        $titleQuery = $xml->xpath('/rsp/photo/title');
        $video->title = $titleQuery ? strval($titleQuery[0]) : null;
        
        # Description
        $description_query = $xml->xpath('/rsp/photo/description');
        $video->description = empty($description_query) ? strval(trim($description_query[0])) : null;
        
        # Tags
        $tagsQuery = $xml->xpath('/rsp/photo/tags/tag');
        $tags = array();
        foreach ($tagsQuery as $tag_query) {
            $tag = (array) $tag_query;
            $tags[] = $tag[0];
        }
        $video->tags = $tagsQuery ? $tags : null;
        
        # Duration
        $durationQuery = $xml->xpath('/rsp/photo/video/@duration');
        $video->duration = empty($durationQuery) ? intval($durationQuery[0]) : null;
        
        # Author & author URL
        $authorQuery = $xml->xpath('/rsp/photo/owner/@username');
        $video->author = $authorQuery ? strval($authorQuery[0]) : null;
        $authorIdQuery = $xml->xpath('/rsp/photo/owner/@nsid');
        $video->author_url = $authorIdQuery ? 'http://www.flickr.com/photos/'.strval($authorQuery[0]) : null;
        
        # Publication date
        $datePublishedQuery = $xml->xpath('/rsp/photo/dates/@posted');
        $video->date_published = $datePublishedQuery ? new DateTime(date(DATE_RSS, intval($datePublishedQuery[0]))) : null;
        
        # Last update date
        $dateUpdatedQuery = $xml->xpath('/rsp/photo/dates/@lastupdate');
        $video->date_updated = $dateUpdatedQuery ? new DateTime(date(DATE_RSS, intval($dateUpdatedQuery[0]))) : null;
        
        # Thumbnails
        $thumbnailsQuery = $xml->xpath('/rsp/photo');
        $thumbnailsQuery = $thumbnailsQuery[0]->attributes();
        $thumbnail = new stdClass();
        $thumbnail->url = 'http://farm'.$thumbnailsQuery['farm'].'.static.flickr.com/'.$thumbnailsQuery['server'].'/'.$id.'_'.$thumbnailsQuery['secret'].'_m.jpg';
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnail = new stdClass();
        $thumbnail->url = 'http://farm'.$thumbnailsQuery['farm'].'.static.flickr.com/'.$thumbnailsQuery['server'].'/'.$id.'_'.$thumbnailsQuery['secret'].'_t.jpg';
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnail = new stdClass();
        $thumbnail->url = 'http://farm'.$thumbnailsQuery['farm'].'.static.flickr.com/'.$thumbnailsQuery['server'].'/'.$id.'_'.$thumbnailsQuery['secret'].'_s.jpg';
        $thumbnail->width = 75;
        $thumbnail->height = 75;
        $video->thumbnails[] = $thumbnail;
        
        # XML for files data URL
        $fileSizesData = 'http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=' . self::FLICKR_API_KEY . '&photo_id=' . $id;
        
        # XML
        $xmlSizes = new SimpleXMLElement(file_get_contents($fileSizesData));
        
        # Player & files URL
        $filesUrlQuery = $xmlSizes->xpath('/rsp/sizes/size[@media="video"]');
        foreach ($filesUrlQuery as $p) {
            switch (strval($p['label'])) {
                case 'Video Player':
                    $video->player_url = $filesUrlQuery ? strval($p['source']) : null;
                    break;
                case 'Site MP4':
                    $video->files['video/mp4'] = $filesUrlQuery ? strval($p['source']) : null;
                    break;
            }
        }
        
        return $video;
    }
    
    public static function getGooglevideo($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # XML data URL
        $fileData = 'http://video.google.com/videofeed?docid='.$id;
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(utf8_encode(file_get_contents($fileData)));
        $xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
        
        # Title
        $titleQuery = $xml->xpath('/rss/channel/item/title');
        $video->title = $titleQuery ? strval($titleQuery[0]) : null;
        
        # Description
        $descriptionQuery = $xml->xpath('/rss/channel/item/media:group/media:description');
        $video->description = $descriptionQuery ? strval(trim($descriptionQuery[0])) : null;
        
        # Tags
        $video->tags = null;
        
        # Duration
        $durationQuery = $xml->xpath('/rss/channel/item/media:group/media:content/@duration');
        $video->duration = $durationQuery ? intval($durationQuery[0]) : null;
        
        # Author & author URL
        $video->author = null;
        $video->author_url = null;
        
        # Publication date
        $datePublishedQuery = $xml->xpath('/rss/channel/item/pubDate');
        $video->date_published = $datePublishedQuery ? new DateTime($datePublishedQuery[0]) : null;
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnailsQuery = $xml->xpath('/rss/channel/item/media:group/media:thumbnail');
        $thumbnailsQuery = $thumbnailsQuery[0]->attributes();
        $thumbnail = new stdClass();
        $thumbnail->url = strval(preg_replace('#&amp;#', '&', $thumbnailsQuery['url']));
        $thumbnail->width = intval($thumbnailsQuery['width']);
        $thumbnail->height = intval($thumbnailsQuery['height']);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $playerUrlQuery = $xml->xpath('/rss/channel/item/media:group/media:content[@type="application/x-shockwave-flash"]/@url');
        $video->player_url = $playerUrlQuery ? strval($playerUrlQuery[0]) : null;
        
        # AVI file URL
        $aviUrlQuery = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-msvideo"]/@url');
        $video->files['video/x-msvideo'] = $aviUrlQuery ? preg_replace('#&amp;#', '&', $aviUrlQuery[0]) : null;
        
        # FLV file URL
        $flvUrlQuery = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/x-flv"]/@url');
        $video->files['video/x-flv'] = $flvUrlQuery ? strval($flvUrlQuery[0]) : null;
        
        # MP4 file URL
        $mp4UrlQuery = $xml->xpath('/rss/channel/item/media:group/media:content[@type="video/mp4"]/@url');
        $video->files['video/mp4'] = $mp4UrlQuery ? preg_replace('#&amp;#', '&', $mp4UrlQuery[0]) : null;
        
        return $video;
    }
    
    public static function getMetacafe($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # XML data URL
        $fileData = "http://www.metacafe.com/api/item/".$id;
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Title
        $titleQuery = $xml->xpath('/rss/channel/item/title');
        $video->title = $titleQuery ? strval($titleQuery[0]) : '';
        
        # Description
        $description_query = $xml->xpath('/rss/channel/item/media:description');
        $video->description = $description_query ? strval($description_query[0]) : '';
        
        # Tags
        $tagsQuery = $xml->xpath('/rss/channel/item/media:keywords');
        $video->tags = $tagsQuery ? explode(',', strval(trim($tagsQuery[0]))) : null;
        
        # Duration
        $video->duration = null;
        
        # Author & author URL
        $authorQuery = $xml->xpath('/rss/channel/item/author');
        $video->author = $authorQuery ? strval($authorQuery[0]) : '';
        $video->author_url = "http://www.metacafe.com/".$video->author;
        
        # Publication date
        $datePublishedQuery = $xml->xpath('/rss/channel/item/pubDate');
        $video->date_published = $datePublishedQuery ? new DateTime($datePublishedQuery[0]) : null;
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnails_query = $xml->xpath('/rss/channel/item/media:thumbnail/@url');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnails_query[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $playerUrlQuery = $xml->xpath('/rss/channel/item/media:content[@type="application/x-shockwave-flash"]/@url');
        $video->player_url = $playerUrlQuery ? strval($playerUrlQuery[0]) : '';
        
        # Files URL
        $video->files = array();
        
        return $video;
    }
    
    public static function getNyspace($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # XML data URL
        $fileData = "http://mediaservices.myspace.com/services/rss.ashx?type=video&videoID=".$id;
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Title
        $titleQuery = $xml->xpath('/rss/channel/item/title');
        $video->title = $titleQuery ? strval($titleQuery[0]) : '';
        
        # Description
        $video->description = null;
        
        # Tags
        $video->tags = null;
        
        # Duration
        $video->duration = null;
        
        # Author & author URL
        $video->author = null;
        $video->author_url = null;
        
        # Publication date
        $datePublishedQuery = $xml->xpath('/rss/channel/item/pubDate');
        $video->date_published = $datePublishedQuery ? new DateTime($datePublishedQuery[0]) : null;
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnailsQuery = $xml->xpath('/rss/channel/item/media:thumbnail/@url');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnailsQuery[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $video->player_url = "http://lads.myspace.com/videos/vplayer.swf?m=" . $id;
        
        # FLV file URL
        $flvUrlQuery = $xml->xpath('/rss/channel/item/media:content[@type="video/x-flv"]/@url');
        $video->files['video/x-flv'] = $flvUrlQuery ? strval($flvUrlQuery[0]) : null;
        
        return $video;
    }
    
    public static function getVeoh($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # API key check
        if (!self::VEOH_API_KEY)
            throw new Exception('You need to request an API key in order to grab video information from Veoh.');
        
        # XML data URL
        $fileData = "http://www.veoh.com/rest/v2/execute.xml?method=veoh.video.findByPermalink&permalink=" . $id . "&apiKey=" . self::VEOH_API_KEY;
        $video->xml_url = $fileData;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($fileData));
        
        # Title
        $titleQuery = $xml->xpath('/rsp/videoList/video/@title');
        $video->title = $titleQuery ? strval($titleQuery[0]) : '';
        
        # Description
        $descriptionQuery = $xml->xpath('/rsp/videoList/video/@description');
        $video->description = $descriptionQuery ? strval($descriptionQuery[0]) : '';
        
        # Tags
        $tagsQuery = $xml->xpath('/rsp/videoList/video/tagList/tag/@tagName');
        foreach($tagsQuery as $tag)
            $video->tags[] = strval($tag[0]);
        
        # Duration
        $durationQuery = $xml->xpath('/rsp/videoList/video/@length');
        $durationRaw = $durationQuery ? strval($durationQuery[0]) : null;
        preg_match('#(([0-9]{0,2}) hr )?([0-9]{0,2}) min ([0-9]{0,2}) sec#', $durationRaw, $matches);
        $hours = intval($matches[2]);
        $minutes = intval($matches[3]);
        $seconds = intval($matches[4]);
        $video->duration = ($hours * 60 * 60) + ($minutes * 60) + $seconds;
        
        # Author & author URL
        $author_query = $xml->xpath('/rsp/videoList/video/@username');
        $video->author = $author_query ? strval($author_query[0]) : '';
        $video->author_url = "http://www.veoh.com/users/".$video->author;
        
        # Publication date
        $date_published_query = $xml->xpath('/rsp/videoList/video/@dateAdded');
        $video->date_published = $date_published_query ? new DateTime($date_published_query[0]) : null;
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnails_query_medres = $xml->xpath('/rsp/videoList/video/@medResImage');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnails_query_medres[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnails_query_highres = $xml->xpath('/rsp/videoList/video/@highResImage');
        $thumbnail = new stdClass();
        $thumbnail->url = strval($thumbnails_query_highres[0]);
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $video->player_url = "http://www.veoh.com/veohplayer.swf?permalinkId=" . $id;
        
        # FLV file URL
        $flv_url_query = $xml->xpath('/rsp/videoList/video/@previewUrl');
        $video->files['video/x-flv'] = $flv_url_query ? strval($flv_url_query[0]) : null;
        
        return $video;
    }
    
    public static function getVimeo($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # PHP serialized data URL
        $url_data = 'http://vimeo.com/api/v2/video/'.$id.'/php';
        
        # Data
        $data = unserialize(file_get_contents($url_data));
        
        # Title
        $video->title = $data[0]['title'];
        
        # Description
        $video->description = $data[0]['description'];
        
        # Tags
        $video->tags = explode(', ',$data[0]['tags']);
        
        # Duration
        $video->duration = $data[0]['duration'];
        
        # Author & author URL
        $video->author = $data[0]['user_name'];
        $video->author_url = $data[0]['user_url'];
        
        # Publication date
        $video->date_published = new DateTime($data[0]['upload_date']);
        
        # Last update date
        $video->date_updated = null;
        
        # Thumbnails
        $thumbnail = new stdClass();
        $thumbnail->url = $data[0]['thumbnail_small'];
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnail = new stdClass();
        $thumbnail->url = $data[0]['thumbnail_medium'];
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        $thumbnail = new stdClass();
        $thumbnail->url = $data[0]['thumbnail_large'];
        list($thumbnail->width, $thumbnail->height) = getimagesize($thumbnail->url);
        $video->thumbnails[] = $thumbnail;
        
        # Player URL
        $video->player_url = 'http://vimeo.com/moogaloop.swf?clip_id='.$id;
        
        # XML data URL
        $file_data = 'http://www.vimeo.com/moogaloop/load/clip:'.$id;
        $video->xml_url = 'http://vimeo.com/api/clip/'.$id.'/xml';
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($file_data), LIBXML_NOCDATA);
        
        # Files URL
        $video->files = array();
        
        return $video;
    }
    
    public static function getYoutube($id, $video = null)
    {
        if (null === $video) {
            $video = new stdClass();
        }
        
        # XML data URL
        $file_data = 'http://gdata.youtube.com/feeds/api/videos/'.$id;
        $video->xml_url = $file_data;
        $video->id = $id;
        
        # XML
        $xml = new SimpleXMLElement(file_get_contents($file_data));
        $xml->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
        $xml->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
        $xml->registerXPathNamespace('yt', 'http://gdata.youtube.com/schemas/2007');
        
        # Title
        $title_query = $xml->xpath('/a:entry/a:title');
        $video->title = $title_query ? strval($title_query[0]) : false;
        
        # Description
        $description_query = $xml->xpath('/a:entry/a:content');
        $video->description = $description_query ? strval(trim($description_query[0])) : false;
        
        # Tags
        $tags_query = $xml->xpath('/a:entry/media:group/media:keywords');
        $video->tags = $tags_query ? explode(', ',strval(trim($tags_query[0]))) : false;
        
        # Duration
        $duration_query = $xml->xpath('/a:entry/media:group/yt:duration/@seconds');
        $video->duration = $duration_query ? intval($duration_query[0]) : false;
        
        # Author & author URL
        $author_query = $xml->xpath('/a:entry/a:author/a:name');
        $video->author = $author_query ? strval($author_query[0]) : false;
        $video->author_url = 'http://www.youtube.com/'.$video->author;
        
        # Publication date
        $date_published_query = $xml->xpath('/a:entry/a:published');
        $video->date_published = $date_published_query ? new DateTime($date_published_query[0]) : false;
        
        # Last update date
        $date_updated_query = $xml->xpath('/a:entry/a:updated');
        $video->date_updated = $date_updated_query ? new DateTime($date_updated_query[0]) : false;
        
        # Thumbnails
        $thumbnail_query = $xml->xpath('/a:entry/media:group/media:thumbnail');
        foreach ($thumbnail_query as $t) {
            $thumbnail = new stdClass();
            $thumbnail_query = $t->attributes();
            $thumbnail->url = strval($thumbnail_query['url']);
            $thumbnail->width = intval($thumbnail_query['width']);
            $thumbnail->height = intval($thumbnail_query['height']);
            $video->thumbnails[] = $thumbnail;
        }
        
        # Player URL
        $video->player_url = 'http://www.youtube.com/v/'.$id;
        
        # Files URL
        $video->files = array();
        
        return $video;
    }
}
