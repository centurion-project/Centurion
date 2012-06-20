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
class Centurion_Service_Flickr_Result_Collection extends Centurion_Service_Flickr_Result_Abstract
{
    /**
     * 
     * @var Centurion_Service_Flickr_ResultSet
     */
    protected $_sets = null;
    
    /**
     * 
     * @param DOMElement $element
     * @param Centurion_Service_Flickr $flickr
     */
    public function __construct(DOMElement $element, Centurion_Service_Flickr $flickr)
    {
        parent::__construct($element, $flickr);
        
        $xpath = new DOMXPath($element->ownerDocument);
        
        $this->_sets = new Centurion_Service_Flickr_ResultSet($xpath->query('set', $element), $this->_flickr, 'set');
    }
    
    /**
     * Getter for $this->_sets
     * 
     * @return Centurion_Service_Flickr_ResultSet
     */
    public function getSets()
    {
        return $this->_sets;
    }
}
