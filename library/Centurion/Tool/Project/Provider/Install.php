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

/**
 * @category    Centurion
 * @package     Centurion_Tool
 * @subpackage  Provider
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */

class Centurion_Tool_Project_Provider_Install extends Centurion_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Pretendable
{
    protected $_application = null;

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
     */
    public function db($env)
    {
        $this->bootstrap($env);

        $application = $this->_application;
        $bootstrap = $application->getBootstrap();
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();

        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);

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
            $bootstrapClass = $this->_formatModuleName($module) . '_Bootstrap';
            $modulePath = dirname($moduleDirectory);

            $dataPath = $modulePath . '/data/';
            if (is_dir($dataPath)) {
                $files = array(
                    'schema.sql',
                    'data.sql',
                );

                foreach ($files as $file) {
                    if (file_exists($dataPath . $file)) {
                        echo 'Installing : ' . $dataPath . $file . "\n";
                        try {
                            $db->beginTransaction();
                            $query = '';
                            foreach (new SplFileObject($dataPath . $file) as $line) {
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
                }
            }
        }

        Centurion_Loader_PluginLoader::clean();
        Centurion_Signal::factory('clean_cache')->send($this);
    }

    /**
     * Format a module name to the module class prefix
     *
     * @param  string $name
     * @return string
     */
    protected function _formatModuleName($name)
    {
        $name = strtolower($name);
        $name = str_replace(array('-', '.'), ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        return $name;
    }

    public function acl($env)
    {
        $this->bootstrap($env);

        $application = $this->_application;
        $bootstrap = $application->getBootstrap();
        $front = $bootstrap->getResource('FrontController');

        $modules = $front->getControllerDirectory();

        $default = $front->getDefaultModule();
        $curBootstrapClass = get_class($bootstrap);

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

        require_once APPLICATION_PATH . '/../library/Centurion/Contrib/auth/models/DbTable/Permission.php';
        require_once APPLICATION_PATH . '/../library/Centurion/Contrib/auth/models/DbTable/Row/Permission.php';

        $permissionTable = Centurion_Db::getSingleton('auth/permission');
        foreach ($modules as $module => $moduleDirectory) {
            echo "\n\n".'Scan new module: '.$module."\n";
            $bootstrapClass = $this->_formatModuleName($module) . '_Bootstrap';
            $modulePath = dirname($moduleDirectory);

            $dataPath = $modulePath . '/controllers/';

            if (is_dir($dataPath)) {
                $db = Zend_Db_Table::getDefaultAdapter();

                foreach (new DirectoryIterator($dataPath) as $file) {
                    if ($file->isDot() || !$file->isFile())
                        continue;
                    if (substr($file, 0, 5) !== 'Admin')
                        continue;
                    $controllerName = substr($file, 5, -14);

                    $object = Centurion_Inflector::tableize($controllerName, '-');

                    $tab = array(
                        'index' => 'View %s %s index',
                        'list' => 'View %s %s list',
                        'get' => 'View an %s %s',
                        'post' => 'Create an %s %s',
                        'new' => 'Access to creation of an %s %s',
                        'delete' => 'Delete an %s %s',
                        'put' => 'Update an %s %s',
                        'batch' => 'Batch an %s %s',
                        'switch' => 'Switch an %s %s',
                    );

                    foreach ($tab as $key => $description) {
                        list($row, $created) = $permissionTable->getOrCreate(array('name' => $module . '_' . $object . '_' . $key));
                        if ($created) {
                            echo 'Create permission: '.$module . '_' . $object . '_' . $key . "\n";
                            $row->description = sprintf($description, $module, $object);
                            $row->save();
                        }
                    }
                }
            }
        }

        Centurion_Loader_PluginLoader::clean();
        Centurion_Signal::factory('clean_cache')->send($this);
    }
    
    public function check()
    {
        defined('APPLICATION_PATH')
            || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../../../application'));
        
        $os = 'linux';
        
        if (!isset($_SERVER['OS'])) {
            echo 'Could not determine you OS.' . "\n";
            echo 'Are you on ?' . "\n";
            echo '1) Windows' . "\n";
            echo '2) Linux' . "\n";
            echo '3) Exit' . "\n";
            
            $os = trim(fgets(STDIN));
            
            if ($os == '1') {
                $os = 'windows';
            } else if ($os == '2') {
                $os = 'linux';
            } else if ($os == '3') {
                return;
            }
        } elseif (preg_match('`Window`i', $_SERVER['OS'])) {
            $os = 'windows';
        }
        if ($os == 'linux') {
            $apacheGroup = 'www-data';
            
            $groupNames = array(
                'www-data',
                'apache2',
                'apache',
                'daemon'
            );
            
            if (function_exists('posix_getgrnam')) {
                foreach ($groupNames as $group) {
                    if (false !== posix_getgrnam($group)) {
                        $apacheGroup = $group;
                        break;
                    }
                }
            }
            
            if (null === $apacheGroup) {
                echo 'Please fill apache group : ' . "\n";
                $apacheGroup = trim(fgets(STDIN));
            }
        }
            
        if (!file_exists(APPLICATION_PATH . '/../public/.htaccess')) {
            echo '1.1 Le fichier .htaccess n\'existe pas' . "\n";
        } else {
            $content = file_get_contents(APPLICATION_PATH . '/../public/.htaccess');
            
            preg_match('`[s|S][e|E][t|T][e|E][n|N][v|V] APPLICATION_ENV ([^\s]*)`', $content, $matches);
            $configs = new Zend_Config_Ini(APPLICATION_PATH . '/configs/env.ini');
            
            if (!array_key_exists($matches[1], $configs->toArray())) {
                echo '1.2 Le fichier .htaccess ne contient pas un environement valid' . "\n";
                echo '1.2 Found ' . $matches[1] . ', expected \'' . implode('\' or \'', array_keys($configs->toArray())) . "'\n\n";
            }
        }
        
        /**
         * List of file/folder to check :
         * Warning :
         * 1 - path of folder have to finish with "/"
         * 2 - we can use * to manage all file into a folder
         * 
         * @var array(string)
         */
        $pathFiles = array(
            '/data/',
            '/data/indexes/',
            '/data/locales/',
            '/data/logs/',
            '/data/sessions/',
            '/data/temp/',
            '/data/uploads/',
            '/data/cache/',
            '/data/cache/config/',
            '/data/cache/class/',
            '/data/cache/core/',
            '/data/cache/output/',
            '/data/cache/page/',
            '/data/cache/tags/',            
			'/public/status/*',
			'/public/index.php',
			'/public/index.php_next',
			'/public/files/',
			'/public/cached/',
			'/public/status/',
        	'/public/status',
        );
                
        
        foreach ($pathFiles as $pathFile) {
        	$nodes = explode("/", $pathFile);
        	$leaf = array_pop($nodes);
        	$dir = APPLICATION_PATH . '/..' .implode("/", $nodes);			
        	// if we are on a folder path and the folder does'nt exist
        	if( ""===$leaf && !file_exists($dir) ){
        		echo '2.1 Directory ' . $dir . ' does not exists.' . "\n";
        		if (is_writable(dirname($dir))) {
        			if (!mkdir($dir, 0775)) {
        				echo '2.1.1 Can\'t create the directory' . "\n";
        			} else {
        				echo '2.1.1 => I fixed it by creating directory' . "\n";
        			}
        		} else {
        			echo '2.1.1 Can\'t fix it because i don\'t have write access in parent dir' . "\n";
        		}
        	// if we are on a folder path and the folder exists        		
        	}elseif ( ""===$leaf &&  is_dir($dir) ){
        		if (!is_writable($dir)) {
        			echo '2.2 Directory ' . $dir . ' is not writable.' . "\n";
        		}
        		if ($os === 'linux') {
        			if (function_exists('posix_getgrgid')) {
        				$groupInfo = posix_getgrgid(filegroup($dir));

        				if ($groupInfo['name'] !== $apacheGroup) {
        					echo '2.2.1 Group of directory ' . $dir . ' is not apache\'s group' . "\n";

        					if (chgrp($dir, $apacheGroup)) {
        						echo '2.2.1.1 => I fixed it, now it\'s : ' . $apacheGroup . "\n\n";
        					} else {
        						echo '2.2.1.2 => Can\'t fix it. Don\'t have permission to make a chown.' . "\n\n";
        					}
        				}
        			}
        			$perms = fileperms($dir);
        			// fix specific Centurion's perms for file
        			$this->_osLinuxFixPerms($dir, $perms, '2.2.2');
        		}
        	// if we are on an existing folder and and we want to manage all file into folder
        	}elseif("*"==$leaf && file_exists($dir) && is_dir($dir) ){
        		if ($dh = opendir($dir)) {
        			while (($file = readdir($dh)) !== false) {
        				$file = $dir . '/' . $file;
        				if ($os === 'linux') {
        					if (function_exists('posix_getgrgid')) {
        						$groupInfo = posix_getgrgid(filegroup($file));

        						if ($groupInfo['name'] !== $apacheGroup) {
        							echo '2.3.1 Group of file ' . $file . ' is not apache\'s group' . "\n";

        							if (chgrp($file, $apacheGroup)) {
        								echo '2.3.1.1 => I fixed it, now it\'s : ' . $apacheGroup . "\n\n";
        							} else {
        								echo '2.3.1.2 => Can\'t fix it. Don\'t have permission to make a chown.' . "\n\n";
        							}
        						}
        					}
        					$perms = fileperms($file);
        					// fix specific Centurion's perms for file
        					$this->_osLinuxFixPerms($file, $perms, '2.3.2');
        				}
        			}
        			closedir($dh);
        		}
			// if we are on an existing file
        	}elseif(file_exists($dir . '/' . $leaf) && is_dir($dir) && is_file($dir . '/' . $leaf)  ){
        		$file = $dir . '/' . $leaf;
        		if ($os === 'linux') {
        			if (function_exists('posix_getgrgid')) {
        				$groupInfo = posix_getgrgid(filegroup($file));
        		
        				if ($groupInfo['name'] !== $apacheGroup) {
        					echo '2.4.1 Group of file ' . $file . ' is not apache\'s group' . "\n";
        		
        					if (chgrp($file, $apacheGroup)) {
        						echo '2.4.1.1 => I fixed it, now it\'s : ' . $apacheGroup . "\n\n";
        					} else {
        						echo '2.4.1.2 => Can\'t fix it. Don\'t have permission to make a chown.' . "\n\n";
        					}
        				}
        			}        		
        			$perms = fileperms($file);
        		
        			// fix specific Centurion's perms for file
        			$this->_osLinuxFixPerms($file, $perms, '2.4.2');
        		}
        	// if the path in conf is not valid path.
        	}else {
        		echo '2.5 => Can\'t manage path : '.$pathFile. "\n\n";        		
        	}        	
        }       
        
        //TODO: check Db
        //TODO: check chmod
    }
    
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $dir
     * @param unknown_type $perms
     */
    private function _osLinuxFixPerms($path, $perms, $index='2.x.x'){
    	 
    	if (!($perms & 0x0020)) {
    		echo $index.'.1 FATAL apache can\'t read in ' . $path . "\n";
    		 
    		$perms = $perms | 0x0020;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.1.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.1.2 => I can\'t fixed it' . "\n";
    		}
    	}
    	if (!($perms & 0x0010)) {
    		echo $index.'.2 FATAL apache can\'t write in ' . $path . "\n";
    		 
    		$perms = $perms | 0x0010;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.2.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.2.2 => I can\'t fixed it' . "\n";
    		}
    	}
    	if (!(($perms & 0x0008))) {
    		echo $index.'.3 FATAL apache can\'t execute in ' . $path . "\n";
    		 
    		$perms = $perms | 0x0008;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.3.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.3.2 => I can\'t fixed it' . "\n";
    		}
    	}
    	 
    	if ($perms & 0x0004) {
    		echo $index.'.4 Warning other can read in ' . $path . "\n";
    		 
    		$perms = $perms & ~0x0004;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.4.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.4.2 => I can\'t fixed it' . "\n";
    		}
    	}
    	if ($perms & 0x0002) {
    		echo $index.'.5 Warning other can write in ' . $path . "\n";
    		 
    		$perms = $perms & ~0x0002;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.5.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.5.2 => I can\'t fixed it' . "\n";
    		}
    	}
    	 
    	if (($perms & 0x0001)) {
    		echo $index.'.6 FATAL other can execute in ' . $path . "\n";
    		 
    		$perms = $perms & ~0x0001;
    		 
    		if (chmod($path, $perms)) {
    			echo $index.'.6.1 => I fixed it' . "\n";
    		} else {
    			echo $index.'.6.2 => I can\'t fixed it' . "\n";
    		}
    	}
    }
    
    
    
    /**
     * 
     * Enter description here ...
     * @param unknown_type $perms
     * @param unknown_type $return_as_string
     * @param unknown_type $filename
     * @see http://pastebin.com/iKky8Vtu
     * @todo clean it to Centurion convention
     * @todo maybe move it to an static function
     * @tood maybe move it to another class (Centurion_File_System) ?
     */
    function mkperms($perms, $return_as_string = false, $filename = '') {
        $perms = explode(',', $perms);
        $generated = array('u'=>array(),'g'=>array(),'o'=>array());
        if(!empty($filename)) {
            $fperms = substr(decoct(fileperms($filename)), 3); // Credits to jchris dot fillionr at kitware dot com
           // Fill array $generated
            $fperms = str_split($fperms);
            $fperms['u'] = $fperms[0]; unset($fperms[0]);
            $fperms['g'] = $fperms[1]; unset($fperms[1]);
            $fperms['o'] = $fperms[2]; unset($fperms[2]);
            foreach($fperms as $key=>$fperm) {
                if($fperm >= 4) {
                   $generated[$key]['r'] = true;
                   $fperm -= 4;
                }
                if($fperm >= 2) {
                   $generated[$key]['w'] = true;
                   $fperm -= 2;
                }
                if($fperm >= 1) {
                   $generated[$key]['x'] = true;
                   $fperm--;
                } 
            }
        }
        foreach($perms as $perm) {
             if(!preg_match('#^([ugo]*)([\+=-])([rwx]+|[\-])$#i', $perm, $matches)) {
                 trigger_error('Wrong input format for mkperms'); return 0644;
                 // Wrong format => generate default 
             }
             $targets = str_split($matches[1]);
             $addrem = $matches[2];
             $perms_ = str_split($matches[3]);
             $fromTheLoop = 0; // To make sure we clear it only once for direct affectation
             foreach($targets as $target) {
                     foreach($perms_ as $perms__) {
                         if($addrem == '=') {
                             if(!$fromTheLoop) {
                                 unset($generated[$target]['r']);
                                 unset($generated[$target]['w']);
                                 unset($generated[$target]['x']);
                             }
                             $fromTheLoop++;
                             $addrem = '+';
                         }
                         if($perms__ == '-') {
                             unset($generated[$target]['r']);
                             unset($generated[$target]['w']);
                             unset($generated[$target]['x']);
                         } else {
                             if($addrem == '+') {
                                 $generated[$target][$perms__] = true;
                             } elseif($addrem == '-') {
                                 unset($generated[$target][$perms__]);
                             } elseif($addrem == '=') {
                                 
                             }
                         }
                     }
             }
        }
        $generated_chars    = array(0, 0, 0);
        $corresponding      = array('u'=>0, 'g'=>1, 'o'=>2);
        $correspondingperms = array('r'=>4, 'w'=>2, 'x'=>1);
    
        foreach($generated as $key=>$generated_) {
            foreach($generated_ as $generated__=>$useless) {
                $generated_chars[$corresponding[$key]] += $correspondingperms[$generated__];
            }
        }
        if($return_as_string) return implode($generated_chars);
     else return base_convert(implode($generated_chars), 8, 10);
    }
}
