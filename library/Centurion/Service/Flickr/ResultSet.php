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
class Centurion_Service_Flickr_ResultSet extends Centurion_Service_Flickr_ResultSet_Abstract
{
    protected $_resultClassName = 'Centurion_Service_Flickr_Result';
    protected $_resultSetNode;
    protected $_resultNode;
    
    function __construct($dom, $flickr, $type, $itemNode = null, $setNode = null)
    {
        $class = 'Centurion_Service_Flickr_Result_'.ucfirst($type);
        
        if (class_exists($class, true)) {
            $this->_resultClassName = $class;
        }
        
        if (null !== $setNode)
            $this->_resultSetNode = $setNode;
        else
            $this->_resultSetNode = $type . 's';
        
        if (null !== $itemNode)
            $this->_resultNode = $itemNode;
        else
            $this->_resultNode = $type;
        parent::__construct($dom, $flickr);
    }
}
