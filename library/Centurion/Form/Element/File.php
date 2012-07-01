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
class Centurion_Form_Element_File extends Zend_Form_Element_File
{
    /**
     * Initial filename.
     *
     * @var string
     */
    protected $_initialFilename = null;

    /**
     * Local filename.
     *
     * @var string
     */
    protected $_localFilename = null;

    /**
     * is the translator disabled?
     * @var boolean
     */
    protected $_translatorDisabled = true;

    /**
     * Receive the uploaded file
     *
     * @return boolean
     */
    public function receive()
    {
        $fileinfo = $this->getFileInfo();
        
        if (null === $this->_initialFilename) {
            $this->_initialFilename = $fileinfo[$this->getName()]['name'];
        }

        $dirName = md5(Centurion_Inflector::uniq($fileinfo[$this->getName()]['tmp_name']));
        
        $filename = substr($dirName, 0, 2) . DIRECTORY_SEPARATOR . substr($dirName, 2)
                  . Centurion_Inflector::extension($fileinfo[$this->getName()]['name']);

        $this->_localFilename = $filename;
                  
        if (!file_exists($this->getDestination() . DIRECTORY_SEPARATOR . substr($dirName, 0, 2)))
            mkdir($this->getDestination() . DIRECTORY_SEPARATOR . substr($dirName, 0, 2), 0770, true);
                  
        $this->getTransferAdapter()
             ->addFilter(
                'Rename',
                array(
                    'target'    => $this->getDestination() . DIRECTORY_SEPARATOR . $filename, 
                    'overwrite' => true,
                )
            )
            ->setOptions(array('useByteString' => false)); // retrieve the real filesize
        
            
        return parent::receive();
    }

    /**
     * Is translation disabled?
     *
     * @return boolean
     */
    public function translatorIsDisabled()
    {
        return true;
    }

    /**
     * Get initial filename.
     *
     * @return string
     */
    public function getInitialFilename()
    {
        return $this->_initialFilename;
    }

    /**
     * Get local filename.
     *
     * @return string
     */
    public function getLocalFilename()
    {
        return $this->_localFilename;
    }
}
