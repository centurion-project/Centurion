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
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Element
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Form_Element_FileTable extends Centurion_Form_Element_File
{
    /**
     * Retrieve all element values
     *
     * @return array
     */
    public function getValues()
    {
        $fileinfo = $this->getFileInfo();
        
        return array(
            'filesize'          =>  $this->getFileSize(),
            'local_filename'    =>  $this->getLocalFilename(),
            'filename'          =>  $this->getInitialFilename(),
            'mime'              =>  $fileinfo[$this->getName()]['type']
        );
    }
    
    /**
     * (non-PHPdoc)
     * @see Zend/Form/Zend_Form_Element::setBelongsTo()
     */
    public function setBelongsTo($array)
    {
        return;
    }
}
