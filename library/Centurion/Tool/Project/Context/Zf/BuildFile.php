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
 * @package     Centurion_Tool
 * @subpackage  Zf
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Tool
 * @subpackage  Zf
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Nicolas Duteil <nd@octaveoctave.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Tool_Project_Context_Zf_BuildFile extends Zend_Tool_Project_Context_Filesystem_File 
{
    /**
     * @var string
     */
    protected $_filesystemName = 'build';
    
    protected $_modelClass = null;
    
    /**
     * Initialize
     *
     * @return Zend_Tool_Project_Context_Zf_ControllerFile
     */
    public function init()
    {
        parent::init();
        $this->setFilesystemName($this->_resource->getAttribute('fileName') . '.php');
        
        return $this;
    }
    
    /**
     * getName()
     *
     * @return string
     */
    public function getName()
    {
        return 'BuildFile';
    }
    
    /**
     * The attributes assigned to any given resource within
     * a project. These aid in searching as well as distinguishing
     * one resource of 'ModelFile' from another.
     *
     * @return array
     */
    public function getPersistentAttributes()
    {
        return array(
            'className' => $this->_resource->getAttribute('className')
        );
    }
    
    public function getModelClass()
    {
        if (null === $this->_modelClass) {
            $this->_modelClass = new Zend_CodeGenerator_Php_Class();
            $this->_modelClass
                 ->setName($this->_resource->getAttribute('className'))
                 ->setExtendedClass('Centurion_Db_Table_Abstract')
                 ->setProperties(array(
                     array(
                         'name'         => '_primary',
                         'visibility'   => 'protected',
                         'defaultValue' => count($this->_resource->getAttribute('primary')) > 1
                                           ? $this->_resource->getAttribute('primary')
                                           : current($this->_resource->getAttribute('primary')),
                    ),
                    array(
                        'name'         => '_rowClass',
                        'visibility'   => 'protected',
                        'defaultValue' => 'Centurion_Db_Table_Row',
                    ),
                    array(
                        'name'         => '_rowsetClass',
                        'visibility'   => 'protected',
                        'defaultValue' => 'Centurion_Db_Table_Rowset',
                    ),
                    array(
                        'name'          =>  '_name',
                        'visibility'    =>  'protected',
                        'defaultValue'  =>  $this->_resource->getAttribute('name'),
                    ),
                    array(
                        'name'         => '_cols',
                        'visibility'   => 'protected',
                        'defaultValue' => $this->_resource->getAttribute('cols'),
                    ),
                    array(
                        'name'         => '_metadata',
                        'visibility'   => 'protected',
                        'defaultValue' => $this->_resource->getAttribute('metadata'),
                    ),
                    array(
                        'name'         => '_dependentTables',
                        'visibility'   => 'protected',
                        'defaultValue' => $this->_resource->getAttribute('dependentTables'),
                    ),
                    array(
                        'name'         => '_referenceMap',
                        'visibility'   => 'protected',
                        'defaultValue' => $this->_resource->getAttribute('referenceMap'),
                    )
                ));
        }
        
        return $this->_modelClass;
    }
    
    public function getContents()
    {
        $codeGenFile = new Zend_CodeGenerator_Php_File(array(
            'fileName'  => $this->getPath(),
            'classes'   => array($this->getModelClass())
        ));
        
        return $codeGenFile->generate();
    }
}
