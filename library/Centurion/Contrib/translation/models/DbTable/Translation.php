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
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Translation_Model_DbTable_Translation extends Centurion_Db_Table_Abstract
{
    protected $_primary = array('uid_id', 'language_id');
    protected $_name = 'translation_translation';
    protected $_rowClass = 'Translation_Model_DbTable_Row_Translation';
    
    protected $_referenceMap = array(
        'ui'   =>  array(
            'columns'       => 'uid_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translation_Model_DbTable_Uid',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        ), 
        'language'   =>  array(
            'columns'       => 'language_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Translation_Model_DbTable_Language',
            'onDelete'      => self::CASCADE,
            'onUpdate'      => self::CASCADE,
        )
    );
}
