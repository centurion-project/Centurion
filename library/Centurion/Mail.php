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
 * @version    $Id$
 */

/**
 *
 * @category    Centurion
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Mail extends Zend_Mail
{
    static protected $_useInlineImage = false;
    
    /**
     * Set global option for using or not inline image.
     * @param bool $useInlineImage
     */
    static function setUseInlineImage($useInlineImage)
    {
        self::$_useInlineImage = $useInlineImage;
    }
    
    /**
     * Sets the HTML body for the message
     *
     * @param  string    $html
     * @param  string    $charset
     * @param  string    $encoding
     * @return Zend_Mail Provides fluent interface
     * @copyright Michal Vrchota (http://www.zfsnippets.com/snippets/view/id/64)
     */
    public function setBodyHtml($html, $charset = null, $encoding = Zend_Mime::ENCODING_QUOTEDPRINTABLE)
    {
        if (self::$_useInlineImage) {
            $this->setType(Zend_Mime::MULTIPART_RELATED);

            $dom = new DOMDocument(null, $this->getCharset());
            @$dom->loadHTML($html);

            $images = $dom->getElementsByTagName('img');

            for ($i = 0; $i < $images->length; $i++) {
                $img = $images->item($i);
                $url = $img->getAttribute('src');
                
                $image_content = null;
                
                //TODO: protect fopen on local file.
                //TODO: try to avoid using $_SERVER param
                if (isset($_SERVER['HTTP_HOST']) && false !== ($pos = strpos($url, $_SERVER['HTTP_HOST']))) {
                    $file = substr($url, $pos + strlen($_SERVER['HTTP_HOST']));
                    $filePath = APPLICATION_PATH . '/../public' . $file;
                    
                    if (file_exists($filePath)) {
                        $image_content = file_get_contents($filePath);
                        $mime_type = mime_content_type($filePath);
                    }
                }
                
                if (null == $image_content) {
                    $image_http = new Zend_Http_Client($url);
                    $response = $image_http->request();
                    
                    if ($response->getStatus() == 200) {
                        $image_content = $response->getBody();
                    }
                    $mime_type = $response->getHeader('Content-Type');
                }
                
                if (null !== $image_content) {
                    $pathInfo = pathinfo($url);

                    $mime = new Zend_Mime_Part($image_content);
                    $mime->id          = sha1($url);
                    $mime->location    = sha1($url);
                    $mime->type        = $mime_type;
                    $mime->disposition = Zend_Mime::DISPOSITION_INLINE;
                    $mime->encoding    = Zend_Mime::ENCODING_BASE64;
                    $mime->filename    = $pathInfo['basename'];
                    $this->addAttachment($mime);
                    
                    $img->setAttribute('src', $mime->id);
                }
            }
            
            $html = $dom->saveHTML();
        }

        return parent::setBodyHtml($html, $charset, $encoding);
    }
}
