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
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Translation_Model_DbTable_TagUid extends Centurion_Db_Table_Abstract
{
    protected $_name = 'translation_tag_uid';
    
    protected $_referenceMap = array(
        'uid'   =>  array(
            'columns'       => 'uid_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translation_Model_DbTable_Uid',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        ), 
        'tag'   =>  array(
            'columns'       => 'tag_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translation_Model_DbTable_Tag',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        )
    );
}
