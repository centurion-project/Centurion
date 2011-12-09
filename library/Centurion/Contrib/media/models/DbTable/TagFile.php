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
 * @category                   Centurion
 * @package                    Centurion_Contrib
 * @subpackage                 MediaCenter
 * @copyright                  Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license                    http://centurion-project.org/license/new-bsd     New BSD License
 * @version                    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  MediaCenter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Mathias Desloges <m.desloges@gmail.com>
 */
class Media_Model_DbTable_TagFile extends Centurion_Db_Table_Abstract
{
    const BELONG_TO = 'belong_to';

    protected $_primary = array('file_id', 'tag_id');

    protected $_name = 'media_tag_file';

    //protected $_rowClass = 'Media_Model_DbTable_Row_TagItem';
    protected $_rowClass = 'Centurion_Db_Table_Row';

    protected $_meta = array(
        'verboseName'   => 'tag_file',
        'verbosePlural' => 'tag_files'
    );

    protected $_referenceMap = array(
        'file'   => array(
            'columns'       => 'file_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Media_Model_DbTable_File'
        ),
        'tag'    => array(
            'columns'       => 'tag_id',
            'refColumns'    => 'id',
            'refTableClass' => 'Media_Model_DbTable_Tag'
        )
    );
    protected $_dependentTables = array(//'items'    =>  'Media_Model_DbTable_Item',
        //'tags'    =>  'Media_Model_DbTable_Tag',
    );
}

