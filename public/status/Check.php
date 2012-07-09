<?php

class Check {

    protected $_checklist = array();

    protected $_phpVersion = null;
    protected $_apacheVersion = null;
    protected $_hasZend = null;
    protected $_hasCenturion = null;

    protected $_hasApplicationEnv = null;
    protected $_currentEnv = null;

    /**
     * @var Zend_Application_Resource_Db
     */
    protected $_dbRessource = null;

    protected function _checkPhp()
    {
        $this->_phpVersion = PHP_VERSION;
        
        //TODO: check true of false
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => false,
                'text' => 'PHP version is <strong>' . PHP_VERSION . '</strong>',
                'alt' => '',
            );
        } else if (version_compare(PHP_VERSION, '5.2.6') >= 0) {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'PHP version is <strong>' . PHP_VERSION . '</strong>',
                'alt' => '5.3.0 could be better',
            );
        } else {
            $this->_checklist[] = array(
                'code' => 0,
                'canBeBetter' => true,
                'isNotSecure' => true,
                'text' => 'PHP version is <strong>' . PHP_VERSION . '</strong>',
                'alt' => '',
            );
        }

        //TODO: check time limit
        //TODO: check memory limit
    }

    protected function _checkApache()
    {

        $this->_apacheVersion = $_SERVER['SERVER_SOFTWARE'];

        if ($this->_apacheVersion == 'Apache') {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'Apache version is <strong>unknown</strong>!',
                'alt' => 'Apache version is <strong>unknown</strong>. Please verify manually that your are above 2.0',
            );
        } else {
            if (false !== ($pose = strpos($this->_apacheVersion, ' '))) {
                $this->_apacheVersion = substr($this->_apacheVersion, 0, $pose);
            }

            if (false !== ($pose = strpos($this->_apacheVersion, '/'))) {
                $this->_apacheVersion = substr($this->_apacheVersion, $pose + 1);
            }

            if (version_compare($this->_apacheVersion, '2.') >= 0) {
                $this->_checklist[] = array(
                    'code' => 1,
                    'canBeBetter' => false,
                    'isNotSecure' => false,
                    'text' => 'Apache version is <strong>'  . $this->_apacheVersion . '</strong>',
                    'alt' => '',
                );
            } else {
                $this->_checklist[] = array(
                    'code' => 0,
                    'canBeBetter' => true,
                    'isNotSecure' => false,
                    'text' => 'Apache version is <strong>'  . $this->_apacheVersion . '</strong>',
                    'alt' => '',
                );
            }
        }
    }

    protected function _checkLibraryZend()
    {
        $this->_hasZend = file_exists(__DIR__ . '/../../library/Zend');
        
        if ($this->_hasZend) {
            include_once __DIR__ . '/../../library/Zend/Version.php';
            $zendVersion = Zend_Version::VERSION;

            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => false,
                'text' => 'Zend version is <strong>'  .$zendVersion . '</strong>',
                'alt' => '',
            );
        } else {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'Zend was not found',
                'alt' => 'Have you forget to do a "git submodule init" ?',
            );
        }

        //TODO: check true of false
    }

    protected function _checkLibraryCenturion()
    {
        $this->_hasCenturion = file_exists(__DIR__ . '/../../library/Centurion');

        if ($this->_hasCenturion) {
            include_once __DIR__ . '/../../library/Centurion/Version.php';
            $centurionVersion = Centurion_Version::VERSION;

             $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => false,
                'text' => 'Centurion version is <strong>'  .$centurionVersion . '</strong>',
                'alt' => '',
            );
         } else {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'Centurion library was not found',
                'alt' => 'Have you forget to do a "git submodule init" ?',
            );
        }

        //TODO: check true of false
    }

    public function __construct()
    {
        
    }

    protected function _checkPhpExtensions()
    {
        $extensions = get_loaded_extensions();
        //TODO:
    }

    protected function _checkHtaccess()
    {
        $hasHtaccess = file_exists(__DIR__ . '/.htaccess');
        //TODO:
    }

    protected function _checkApplicationEnv()
    {
        if (defined('APPLICATION_ENV')) {
            $this->_hasApplicationEnv = true;
            $this->_currentEnv = APPLICATION_ENV;
        } else if (getenv('APPLICATION_ENV') != false) {
            $this->_hasApplicationEnv = true;
            $this->_currentEnv = getenv('APPLICATION_ENV');
        } else {
            $this->_hasApplicationEnv = false;
            $this->_currentEnv = 'production';
        }
        
        if ($this->_currentEnv == 'production') {
            $this->_checklist[] = array(
                'code' => 0,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'Current APPLICATION_ENV is <strong>' . $this->_currentEnv . '</strong>',
                'alt' => 'Warning: with this env, you will not see error (it\'s a no debug mode).<br />Try <b>development</b> instead.',
            );
        } else {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => false,
                'text' => 'Current APPLICATION_ENV is <strong>' . $this->_currentEnv . '</strong>',
                'alt' => '',
            );
        }
    }

    protected function _checkDbConnect()
    {
        if (!$this->_hasCenturion || !$this->_hasZend) {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => false,
                'text' => 'Database could not be checked (no Centurion or Zend)',
                'alt' => '',
            );
        } else {
            include_once __DIR__ . '/../../library/Centurion/Config/Directory.php';
            include_once __DIR__ . '/../../library/Centurion/Iterator/Directory.php';
            include_once __DIR__ . '/../../library/Zend/Config/Ini.php';

            $config = Centurion_Config_Directory::loadConfig(__DIR__ . '/../../application/configs', $this->_currentEnv);
            include_once __DIR__ . '/../../library/Zend/Application/Resource/Db.php';
            include_once __DIR__ . '/../../library/Zend/Db.php';
            $this->_dbRessource = new Zend_Application_Resource_Db();
            $this->_dbRessource->setParams($config['resources']['db']['params']);
            $this->_dbRessource->setAdapter($config['resources']['db']['adapter']);

            try {
                $bddVersion = $this->_dbRessource->getDbAdapter()->getServerVersion();

                if (version_compare(PHP_VERSION, '5.1') >= 0) {
                    $this->_checklist[] = array(
                        'code' => 1,
                        'canBeBetter' => false,
                        'isNotSecure' => false,
                        'text' => 'Mysql version <strong>' . $bddVersion . '</strong>',
                        'alt' => '',
                    );
                } else {
                    $this->_checklist[] = array(
                        'code' => -1,
                        'canBeBetter' => true,
                        'isNotSecure' => true,
                        'text' => 'Mysql version <strong>' . $bddVersion . '</strong>',
                        'alt' => '',
                    );
                }
            } catch (Exception $e) {
                $this->_dbRessource = null;
                if ($e->getCode() == 1049) {
                    $this->_checklist[] = array(
                        'code' => -1,
                        'canBeBetter' => true,
                        'isNotSecure' => true,
                        'text' => 'BDD  ' . $config['resources']['db']['params']['dbname'] . ' does not exists',
                        'alt' => '',
                    );
                } if( $e->getCode() == 1045) {
                    $this->_checklist[] = array(
                        'code' => -1,
                        'canBeBetter' => true,
                        'isNotSecure' => true,
                        'text' => 'Your mysql credential is not valid.',
                        'alt' => 'Change it in application/db.ini',
                    );
                } else {
                    throw $e;
                }
            }
        }
    }

    protected function _checkDbTable()
    {
        if (null !== $this->_dbRessource) {
            $tablesToCheck = array();
            $tablesNotFound = array();
            
            $tablesToCheck[] = 'auth_belong';
            $tablesToCheck[] = 'auth_group';
            $tablesToCheck[] = 'auth_group_permission';
            $tablesToCheck[] = 'auth_permission';
            $tablesToCheck[] = 'auth_user';
            $tablesToCheck[] = 'auth_user_permission';

            
            $tablesToCheck[] = 'centurion_content_type';
            $tablesToCheck[] = 'centurion_navigation';
            $tablesToCheck[] = 'centurion_site';

            $tablesToCheck[] = 'cms_flatpage';
            $tablesToCheck[] = 'cms_flatpage_template';
            
            $tablesToCheck[] = 'media_duplicate';
            $tablesToCheck[] = 'media_file';
            $tablesToCheck[] = 'media_image';
            $tablesToCheck[] = 'media_multiupload_ticket';
            $tablesToCheck[] = 'media_video';

            $tablesToCheck[] = 'translation_language';
            $tablesToCheck[] = 'translation_tag';
            $tablesToCheck[] = 'translation_tag_uid';
            $tablesToCheck[] = 'translation_translation';
            $tablesToCheck[] = 'translation_uid';
            
            $tablesToCheck[] = 'user_profile';

            foreach ($tablesToCheck as $tableName) {
                try {
                    $this->_dbRessource->getDbAdapter()->describeTable($tableName);
                } catch (Exception $e) {
                    if ($e->getCode() == '42') {
                        $tablesNotFound[] = $tableName;
                    } else {
                        throw $e;
                    }
                }
            }

            if (count($tablesNotFound) > 0) {
                if (count($tablesNotFound) == count($tablesToCheck)) {
                    //TODO: zf db install
                    $this->_checklist[] = array(
                        'code' => -1,
                        'canBeBetter' => true,
                        'isNotSecure' => false,
                        'text' => 'All table are missing.',
                        'alt' => 'Have you forget a "zf db install" ?',
                    );
                } else {
                    $this->_checklist[] = array(
                        'code' => 0,
                        'canBeBetter' => true,
                        'isNotSecure' => false,
                        'text' => 'Some table are missing',
                        'alt' => 'Some table are missing: <br /> - ' . implode('<br />- ', $tablesNotFound),
                    );
                }
            }
            
        } else {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => true,
                'text' => 'Can\'t check table; no connection to bdd',
                'alt' => '',
            );
        }
    }
    
    protected function _checkPermission()
    {
        $dirs = array(
            '/data/',
            '/data/indexes/',
            '/data/locales/',
            '/data/logs/',
            '/data/sessions/',
            '/data/temp/',
            '/data/uploads/',
            '/data/cache/',
            '/data/cache/class',
            '/data/cache/core',
            '/data/cache/output',
            '/data/cache/page',
            '/data/cache/tags',
            '/public/files',
            '/public/cached',
            '/public/status',
            '/public/index.php',
        );
        
        $notWritable = array();
        $prefixDir = realpath(dirname(__FILE__) . '/../..');
    
        foreach ($dirs as $dir) {
            $fullPath = $prefixDir . $dir;
            
            if (!is_writable($fullPath)) {
                $notWritable[] = $dir;
            }
        }
        
        if (count($notWritable) > 0) {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => true,
                'text' => 'Some of your file system are not writable',
                'alt' => 'Full list: <br /> - ' . implode('<br />- ', $notWritable),
            );
        }
    }

    public function _checkRedirect()
    {
        $url = 'http://' . $_SERVER['SERVER_NAME'];
        if ($_SERVER['SERVER_PORT'] !== 80) {
            $url .= ':' . $_SERVER['SERVER_PORT'];
        }

        $url .= str_replace('/status', '/test_redirect', $_SERVER['REQUEST_URI']);

        $url .= '?noredirect=true';

        $fp = @file_get_contents($url);

        if ($fp === 'Mod_Rewrite works!') {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => true,
                'text' => 'The rewrite works',
                'alt'  => '',
            );
        } else {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => true,
                'isNotSecure' => true,
                'text' => 'Your mod_rewrite seems to not worked. <a href="' . $url . '" target="_blank">Click here</a> to check',
                'alt' => 'Click on the link above. If it\'s not worked check mod_rewrite is enabled, or that the directive AllowOverride All is set to the application root.',
            );
        }
    }

    protected function _checkDocumentRoot()
    {
        if (!preg_match('`(/|\\\)public(/|\\\)?$`', $_SERVER['DOCUMENT_ROOT'])) {
            $this->_checklist[] = array(
                'code' => -1,
                'canBeBetter' => true,
                'isNotSecure' => true,
                'text' => 'Your DOCUMENT_ROOT is not correctly set',
                'alt' => 'Your DOCUMENT_ROOT must be set to point to the "public" folder.',
            );
        } else {
            $this->_checklist[] = array(
                'code' => 1,
                'canBeBetter' => false,
                'isNotSecure' => false,
                'text' => 'Your DOCUMENT_ROOT is correctly set',
                'alt' => '',
            );
        }
    }

    public function check() {

        set_include_path(implode(PATH_SEPARATOR, array(
            realpath(__DIR__ . '/../../library/'),
            get_include_path(),
        )));
        
        $this->_checkPhp();
        $this->_checkApache();
        $this->_checkLibraryZend();
        $this->_checkLibraryCenturion();

        if ($this->_hasCenturion || $this->_hasZend) {
            require_once 'Zend/Loader/Autoloader.php';
            $autoloader = Zend_Loader_Autoloader::getInstance()
                ->setDefaultAutoloader(create_function('$class',
                    "include str_replace('_', '/', \$class) . '.php';"
                ));
        }
        
        $this->_checkPhpExtensions();
        $this->_checkHtaccess();
        $this->_checkApplicationEnv();

        $this->_checkRedirect();

        $this->_checkPermission();
        $this->_checkDbConnect();
        $this->_checkDbTable();

        $this->_checkDocumentRoot();
    }

    public function hasError()
    {
        foreach ($this->_checklist as $data) {
            if ($data['code'] != '1') {
                return true;
            }
        }

        return false;
    }
    public function getCheckList()
    {
        return $this->_checklist;
    }
}
