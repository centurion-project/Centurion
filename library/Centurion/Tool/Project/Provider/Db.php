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
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Tool_Project_Provider_Db extends Centurion_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    const PRIMARY = 'PRIMARY';
    const DEFAULT_ENVIRONMENT = 'development';
    const DEFAULT_TABLE_PREFIX = '';
    
    protected $_dbImport = null;

    public function bootstrap($env)
    {
        putenv('RUN_CLI_MODE=true');
        define('RUN_CLI_MODE', true);

        defined('APPLICATION_PATH')
            || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../../../application'));

        // Define application environment
        defined('APPLICATION_ENV')
            || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : $env));

        // bootstrap include_path and constants
        require realpath(APPLICATION_PATH . '/../library/library.php');

        /** Zend_Application */
        require_once 'Zend/Application.php';
        require_once 'Centurion/Application.php';

        require_once 'Zend/Loader/Autoloader.php';
        $autoloader = Zend_Loader_Autoloader::getInstance()
            ->registerNamespace('Centurion_')
            ->setDefaultAutoloader(create_function('$class',
            "include str_replace('_', '/', \$class) . '.php';"
        ));
        $classFileIncCache = realpath(APPLICATION_PATH . '/../data/cache').'/pluginLoaderCache.tmp';
        if (file_exists($classFileIncCache)) {
            $fp = fopen($classFileIncCache, 'r');
            flock($fp, LOCK_SH);
            $data = file_get_contents($classFileIncCache);
            flock($fp, LOCK_UN);
            fclose($fp);
            $data = @unserialize($data);

            if ($data !== false)
                Centurion_Loader_PluginLoader::setStaticCachePlugin($data);
        }

        Centurion_Loader_PluginLoader::setIncludeFileCache($classFileIncCache);

        // Create application, bootstrap, and run
        $this->_application = new Centurion_Application(
            APPLICATION_ENV,
            APPLICATION_PATH . '/configs/'
        );

        $this->_application->bootstrap('db');
        $this->_application->bootstrap('FrontController');
        $this->_application->bootstrap('contrib');
    }

    /**
     * @param $env
     * @param bool $acceptAll
     * @param bool $ignoreLaunched
     * @throws Exception
     * @throws Centurion_Application_Resource_Exception
     *
     * @todo: rajouter la gestion des php
     * @todo: refractoring
     */
    public function update($env, $acceptAll = false, $ignoreLaunched = true)
    {
        if ($acceptAll === 'true') {
            $acceptAll = true;
        }
        if ($ignoreLaunched === 'true') {
            $ignoreLaunched = true;
        }
        if ($acceptAll === 'false') {
            $acceptAll = false;
        }
        if ($ignoreLaunched === 'false') {
            $ignoreLaunched = false;
        }

        $this->bootstrap($env);

        $application = $this->_application;
        $bootstrap = $application->getBootstrap();
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();

        $default = $front->getDefaultModule();

        $options = $bootstrap->getOption('resources');
        $options = $options['modules'];

        if (is_array($options) && !empty($options[0])) {

            $diffs = array_diff($options, array_keys($modules));

            if (count($diffs)) {
                throw new Centurion_Application_Resource_Exception(sprintf("The modules %s is not found in your registry (%s)",
                    implode(', ', $diffs),
                    implode(PATH_SEPARATOR, $modules)));
            }

            foreach ($modules as $key => $module) {
                if (!in_array($key, $options) && $key !== $default) {
                    unset($modules[$key]);
                    $front->removeControllerDirectory($key);
                }
            }

            $modules = Centurion_Inflector::sortArrayByArray($modules, array_values($options));
        }

        $db = Zend_Db_Table::getDefaultAdapter();

        foreach ($modules as $module => $moduleDirectory) {
            $modulePath = dirname($moduleDirectory);

            $dataPath = $modulePath . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
            echo $dataPath;
            if (is_dir($dataPath)) {
                echo 'Open ' . $dataPath . "\n";
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dataPath, FilesystemIterator::SKIP_DOTS | FilesystemIterator::KEY_AS_FILENAME));

                $files = iterator_to_array($files);

                ksort($files);
                $files = array_merge(array('schema.sql' => null, 'data.sql' => null), $files);

                foreach ($files as $file) {
                    if (null == $file) {
                        continue;
                    }

                    if ($file->isDir()) {
                        continue;
                    }

                    if (Centurion_Inflector::extension($file) !== '.sql') {
                        continue;
                    }

                    $choice = null;

                    if (file_exists($file->getPathname() . '.' . APPLICATION_ENV . '.done')) {
                        if ($ignoreLaunched) {
                            continue;
                        }
                        if (!$acceptAll) {
                            echo sprintf('The file %s seems to be already executed. Do you want to launch it?' . "\n", $file->getPathname());
                        }
                    }

                    if (!$acceptAll) {
                        echo sprintf('Find a new file file %s. Do you want to launch it?' . "\n", $file->getPathname());
                    } else {
                        $choice = '1';
                    }

                    if (null == $choice) {
                        do {
                            echo '1) Execute' . "\n";
                            echo '2) Ignore' . "\n";
                            echo '3) Mark it without execute it' . "\n";
                            echo '? ';
                            $choice = trim(fgets(STDIN));
                        } while(!in_array($choice, array('1', '2', '3')));
                    }

                    if ($choice == '2') {
                        echo "\n\n";
                        continue;
                    }

                    if ($choice == '1') {
                        echo sprintf('Exec the file %s' . "\n", $file->getPathname());

                        try {
                            $db->beginTransaction();
                            $query = '';
                            foreach (new SplFileObject($file->getPathname()) as $line) {
                                $query .= $line;
                                if (substr(rtrim($query), -1) == ';') {
                                    $statement = $db->query($query);
                                    $statement->closeCursor();
                                    unset($statement);
                                    $query = '';
                                }
                            }

                            $db->commit();

                        } catch(Exception $e) {
                            $db->rollback();
                            throw $e;
                        }
                    }

                    touch($file->getPathname() . '.' . APPLICATION_ENV . '.done');
                    echo "\n\n";
                }
            }
        }

        Centurion_Loader_PluginLoader::clean();
        Centurion_Signal::factory('clean_cache')->send($this);
    }

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
     * @return Zend_Db_Adapter_Abstract
     */
    protected function _getDbImport($environment = self::DEFAULT_ENVIRONMENT)
    {
        if (null === $this->_dbImport) {
            if (!$this->_loadedProfile)
                $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
            define('APPLICATION_PATH', $this->_loadedProfile->search('applicationDirectory')->getPath());

            $config = Centurion_Config_Directory::loadConfig(dirname($this->_loadedProfile->search('applicationConfigFile')->getPath()), $environment);
            $dbAdapter = Zend_Db::factory(new Zend_Config($config['resources']['db']));
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
