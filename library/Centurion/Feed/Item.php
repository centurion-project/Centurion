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
 * @package     Centurion_Feed
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Feed
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Feed_Item
{
    const DEFAULT_FEED_TYPE = 'rss';
    const DEFAULT_CHARSET = 'utf-8';

    protected $_descriptionViewScript = null;

    protected $_titleViewScript = null;

    protected $_contentViewScript = null;

    protected $_request = null;

    protected $_view = null;

    protected $_feedType = self::DEFAULT_FEED_TYPE;

    protected $_url = null;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, Centurion_View_Abstract $view)
    {
        $this->setRequest($request)
             ->setView($view)
             ->setResponse($response);
    }

    /**
     * Return the Request object
     *
     * @return Zend_Controller_Request_Abstract
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Set the Request object
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Centurion_Feed_Item
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;

        return $this;
    }

    /**
     * Return the Response object
     *
     * @return Zend_Controller_Response_Abstract
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * Set the Response object
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return Centurion_Feed_Item
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;

        return $this;
    }

    /**
     * Return the View object
     *
     * @return Centurion_View_Abstract
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Set the Request object
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Centurion_Feed_Item
     */
    public function setView(Centurion_View_Abstract $view)
    {
        $this->_view = $view;

        return $this;
    }

    public function setUrl($url)
    {
        $this->_url = $url;

        return $this;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getFeed()
    {
        $feed = array(
            'link'          =>  $this->getLink(),
            'title'         =>  $this->getTitle(),
            'description'   =>  $this->getDescription(),
            'charset'       =>  $this->getCharset(),
            'author'        =>  $this->getAuthor(),
            'email'         =>  $this->getEmail(),
            'webmaster'     =>  $this->getWebmaster(),
            'copyright'     =>  $this->getCopyright(),
            'image'         =>  $this->getImage(),
            'generator'     =>  $this->getGenerator(),
            'language'      =>  $this->getLanguage(),
            'entries'       =>  array()
        );

        foreach ($this->getItems() as $key => $item) {
            $params = array('row' => $item, 'url' => $this->getUrl());

            $title = null !== $this->_titleViewScript ? $this->getView()->renderToString($this->_titleViewScript, $params) : $this->getItemTitle($item);
            $description = null !== $this->_descriptionViewScript ? $this->getView()->renderToString($this->_descriptionViewScript, $params) : $this->getItemDescription($item);

            $content = null !== $this->_contentViewScript ? $this->getView()->renderToString($this->_contentViewScript) : '';

            array_push($feed['entries'], array(
                'title'         =>  $title,
                'guid'          =>  $this->getItemGuid($item),
                'description'   =>  $description,
                'lastUpdate'    =>  $this->getItemLastUpdate($item),
                'content'       =>  $content,
                'link'          =>  $this->getUrl() . $this->getItemLink($item)
            ));
        }

        return $feed;
    }

    abstract public function getTitle();

    abstract public function getDescription();

    abstract public function getLink();

    abstract public function getItems();

    public function getCharset()
    {
        return self::DEFAULT_CHARSET;
    }

    public function getItemTitle($row)
    {
        return htmlentities((string) $row, null, 'UTF-8');
    }

    public function getItemDescription($row)
    {
        return (string) $item;
    }

    public function getItemLink($row)
    {
        if (method_exists($row, 'getAbsoluteUrl')) {
            return $row->permalink;
        }

        return '';
    }

    public function getItemGuid($item)
    {
        return $item->pk;
    }

    public function getLanguage()
    {
        return Zend_Locale::getDefault();
    }

    public function getFeedType()
    {
        return $this->_feedType;
    }

    public function getItemLastUpdate($row)
    {
    }

    public function getEmail()
    {
    }

    public function getWebmaster()
    {
    }

    public function getCopyright()
    {
    }

    public function getImage()
    {
    }

    public function getGenerator()
    {
    }

    public function getAuthor()
    {
    }
}