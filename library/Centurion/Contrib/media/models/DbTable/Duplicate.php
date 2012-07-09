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
 * @package     Centurion_Media
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Media_Model_DbTable_Duplicate extends Centurion_Db_Table_Abstract
{
    protected $_primary = 'id';
    
    protected $_name = 'media_duplicate';
    
    protected $_rowClass = 'Media_Model_DbTable_Row_Duplicate';
    
    protected $_meta = array('verboseName'   => 'duplicate',
                             'verbosePlural' => 'duplicates');
    
    protected $_referenceMap = array(
        'file'   =>  array(
            'columns'       => 'file_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Media_Model_DbTable_File',
            'onDelete'      => self::CASCADE
        )
    );
}
