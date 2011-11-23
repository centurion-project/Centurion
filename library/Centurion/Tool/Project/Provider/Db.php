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
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */
 
require_once 'Centurion/Tool/Project/Provider/Abstract.php'; 

require_once 'Centurion/Exception.php';
 
require_once 'Centurion/Inflector.php';
 
require_once 'Centurion/Import.php'; 

require_once 'Centurion/Import/Abstract.php'; 

require_once 'Centurion/Import/Pdo/Mysql.php';

/**
 * @category    Centurion
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Tool_Project_Provider_Db extends Centurion_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    const PRIMARY = 'PRIMARY';
    const DEFAULT_ENVIRONMENT = 'development';
    const DEFAULT_TABLE_PREFIX = '';
    
    protected $_dbImport = null;
    
    /**
     * Inspect db for Zend_Db adapter.
     * Connect to a database and generate models.
     *
     * @param string $tablePrefix 
     * @param string $environment 
     * @return void
     */
    public function inspect($tablePrefix = self::DEFAULT_TABLE_PREFIX, $environment = self::DEFAULT_ENVIRONMENT)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $buildDirectory = $this->_loadedProfile->search('buildDirectory');
        
        $tables = $this->_getDbImport()->getAdapter()->listTables();
        $dependentTables = array();
        $classes = array();
        foreach ($tables as $key => $table) {
            $metadata = $this->_getDbImport()->getAdapter()->describeTable($table);
            $primary = array();
            $className = Centurion_Inflector::modelize($table);
            $classNamePrefix = sprintf('%s%s', $this->_formatPrefix($tablePrefix), $className);
            $relations[$table] = array();
            foreach ($metadata as $columnName => $columnValue) {
                if ($columnValue[self::PRIMARY] === true) {
                    array_push($primary, $columnName);
                }
            }
            
            $localRelations = $this->_getDbImport()->listTableRelations($table);
            foreach ($localRelations as $localRelationKey => $localRelation) {
                $referenceClassName = Centurion_Inflector::modelize($localRelation['table']);
                if (!array_key_exists($referenceClassName, $dependentTables)) {
                    $dependentTables[$referenceClassName] = array();
                }
                
                $relations[$table][strtolower($referenceClassName)] = array(
                    'columns'       =>  $localRelation['local'],
                    'refColumns'    =>  $localRelation['foreign'],
                    'refTableClass' =>  sprintf('%s%s', $this->_formatPrefix($tablePrefix), $referenceClassName),
                );
                
                $key = strtolower(Centurion_Inflector::pluralize($className));
                
                $dependentTables[$referenceClassName][$key] = $classNamePrefix;
            }
            
            $existingModelFile = $this->_loadedProfile->search(array(
                'dataDirectory',
                'buildDirectory', 
                'buildFile' => array('fileName' => $className)
            ));
            
            if (false !== $existingModelFile) {
                $existingModelFile->delete();
            }
            
            $buildFile = $buildDirectory->createResource(
                'BuildFile',
                array(
                    'name'           =>  $table,
                    'primary'        =>  $primary,
                    'cols'           =>  array_keys($metadata),
                    'metadata'       =>  $metadata,
                    'className'      =>  $classNamePrefix,
                    'fileName'       =>  $className,
                    'referenceMap'   =>  $relations[$table]
                )
            );
            
            $classes[$className] = $buildFile;
        }
        
        foreach ($dependentTables as $key => $dependentTable) {
            if (!array_key_exists($key, $classes)) {
                continue;
            }
            
            $classes[$key]->getModelClass()
                          ->getProperty('_dependentTables')
                          ->setDefaultValue($dependentTable);
        }
        
        if ($this->_registry->getRequest()->isPretend()) {
            foreach ($classes as $key => $class) {
                $this->_registry->getResponse()->appendContent(sprintf('Would create model at %s: %s', $buildDirectory->getPath(), $key));
                $this->_registry->getResponse()->appendContent($class->getContents());
            }
        } else {
            if (!file_exists($buildDirectory->getPath())) {
                throw new Centurion_Exception('Build directory does not exist.');
            }
            if (!is_writable($buildDirectory->getPath())) {
                throw new Centurion_Exception('Build directory is not writable.');
            }
            foreach ($classes as $key => $class) {
                $this->_registry->getResponse()->appendContent(sprintf('Creating model at %s: %s', $buildDirectory->getPath(), $key));
                $class->create();
            }
            $this->_storeProfile();
        }
    }
    
    /**
     * Retrieve import adapter.
     *
     * @param string $environment 
     * @return void
     */
    protected function _getDbImport($environment = self::DEFAULT_ENVIRONMENT)
    {
        if (null === $this->_dbImport) {
            if (!$this->_loadedProfile)
                $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            
            $config = new Zend_Config_Ini($this->_loadedProfile->search('applicationConfigFile')->getPath(), $environment);
            $dbAdapter = Zend_Db::factory($config->resources->db);
            $this->_dbImport = Centurion_Import::factory($dbAdapter);
        }
        
        return $this->_dbImport;
    }
    
    /**
     * Format prefix for internal use.
     *
     * @param  string $prefix
     * @return string
     */
    protected function _formatPrefix($prefix)
    {
        if ($prefix == '') {
            return $prefix;
        }
        
        return rtrim($prefix, '_') . '_';
    }
}