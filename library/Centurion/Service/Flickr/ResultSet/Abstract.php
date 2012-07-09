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
abstract class Centurion_Service_Flickr_ResultSet_Abstract implements SeekableIterator
{
    protected $_resultClassName;
    protected $_resultSetNode;
    protected $_resultNode;
    
    /**
     * Results storage
     *
     * @var DOMNodeList
     */
    protected $_results = null;
    
    protected $_dom = null;

    /**
     * Reference to Centurion_Service_Flickr object with which the request was made
     *
     * @var Centurion_Service_Flickr
     */
    protected $_flickr;

    /**
     * Current index for the Iterator
     *
     * @var int
     */
    protected $_currentIndex = 0;
    
    /**
     * Parse the Flickr Result Set
     *
     * @param  DOMDocument         $dom
     * @param  Centurion_Service_Flickr $flickr
     * @return void
     */
    public function __construct($dom, Centurion_Service_Flickr $flickr)
    {
        $this->_flickr = $flickr;
        
        if ($dom instanceof DOMDocument) {
            $this->_dom = $dom;
            $xpath = new DOMXPath($dom);
            $photos = $xpath->query('//'.$this->_resultSetNode)->item(0);
            $this->_results = $xpath->query($this->_resultNode, $photos);
        } elseif ($dom instanceof DOMNodeList) {
            $this->_results = $dom;
        }
    }

    /**
     * 
     * @return DOMDocument
     */
    public function getDom()
    {
        return $this->_dom;
    }
    
    /**
     * 
     * 
     * @return Centurion_Service_Flickr
     */
    public function getFlickr()
    {
        return $this->_flickr;
    }
    
    /**
     * Implements SeekableIterator::current()
     *
     * @return Centurion_Service_Flickr_Result
     */
    public function current()
    {
        $className = $this->_resultClassName;
        return new $className($this->_results->item($this->_currentIndex), $this->_flickr);
    }

    /**
     * Implements SeekableIterator::key()
     *
     * @return int
     */
    public function key()
    {
        return $this->_currentIndex;
    }

    /**
     * Implements SeekableIterator::next()
     *
     * @return void
     */
    public function next()
    {
        $this->_currentIndex += 1;
    }

    /**
     * Implements SeekableIterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
        $this->_currentIndex = 0;
    }

    /**
     * Implements SeekableIterator::seek()
     *
     * @param  int $index
     * @throws OutOfBoundsException
     * @return void
     */
    public function seek($index)
    {
        $indexInt = (int) $index;
        if ($indexInt >= 0 && (null === $this->_results || $indexInt < $this->_results->length)) {
            $this->_currentIndex = $indexInt;
        } else {
            throw new OutOfBoundsException(sprtinf('Illegal index %s', $index));
        }
    }

    /**
     * Implements SeekableIterator::valid()
     *
     * @return boolean
     */
    public function valid()
    {
        return null !== $this->_results && $this->_currentIndex < $this->_results->length;
    }
}
