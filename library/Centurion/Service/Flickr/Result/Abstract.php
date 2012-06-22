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
class Centurion_Service_Flickr_Result_Abstract
{
   /**
     * Original Zend_Service_Flickr object.
     *
     * @var Centurion_Service_Flickr
     */
    protected $_flickr;
    
    /**
     * The DOMElement
     * 
     * @var DOMElement
     */
    protected $_element;

    /**
     * Parse the Flickr Result
     *
     * @param  DOMElement          $image
     * @param  Centurion_Service_Flickr $flickr Original Centurion_Service_Flickr object with which the request was made
     * @return void
     */
    public function __construct(DOMElement $element, Centurion_Service_Flickr $flickr)
    {
        $xpath = new DOMXPath($element->ownerDocument);
        
        foreach ($xpath->query('./@*', $element) as $property) {
            $this->{$property->name} = (string) $property->value;
        }

        $this->_flickr = $flickr;
        $this->_element = $element;
    }
}
