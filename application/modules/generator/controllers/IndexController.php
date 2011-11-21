<?php

class Generator_IndexController extends Centurion_Controller_Action
{
    protected $_dirs = array('configs',
                           'controllers',
                           'data',
                           'forms' => array('Model'),
                           'models' => array('DbTable' => array('Row')),
                           'views'  => array('scripts', 'helpers'),
                           'tests' => array('Controllers', 'Models'),
                        );

    protected function _getModulesList()
    {
        $table_reader = new Sayyes_Db_Table_Reader(Centurion_Config_Manager::get('resources.db.params.dbname'));

        foreach($table_reader->getTableList() as $tableName)  {
            $tab = explode('_', $tableName);
            if (!isset($modules[$tab[0]])) {
                $modules[$tab[0]] = array();
            }
            $modules[$tab[0]] = $tableName;
        }

        return $modules;
    }

    protected function _inspectDb($modules = array())
    {
        $table_reader = new Sayyes_Db_Table_Reader(Centurion_Config_Manager::get('resources.db.params.dbname'));
        $table_parser = new Sayyes_Db_Table_Parser();
        $table_writer = new Sayyes_Db_Table_Writer();
        $tables = array();
        $joinTables = array();

        foreach($table_reader->getTableList() as $tableName) {
                $table = new Sayyes_Db_Table();
                $table->setName($tableName);
                $table->setFields($table_reader->describeTable($table->getName()));
                $table->setPrimaryKeys($table_reader->getPrimaryKeys($table->getName()));
                $table->setForeignKeys($table_reader->getForeignKeys($table->getName()));
                $table->setDependentTables(
                    $table_parser->getParsedDependentTables(
                                        $table->getName(),
                                        $table_reader->getDependentTables(
                                                        $table->getName(),
                                                        $table->getPrimaryKeys()
                                        ),
                                        'Skilling'
                    )
                );
                $table->setReferenceMap(
                            $table_parser->getParsedReferenceMap(
                                $table->getName(),
                                $table_reader->getReferenceMap($table->getName()),
                                'Skilling'
                            )
                );
                $tables[$table->getName()] = $table;
        }

        foreach($tables as $table)
        {
            if($table->isJoinTable())
            {
                $joinTables[$table->getName()] = $table;
            }
        }
        foreach($joinTables as $joinTable)
        {
            foreach($joinTable->getKeyOfManyToMany() as $tableName => $ref)
            {
                $tables[$tableName]->pushManyToMany($ref['ref'], $ref['data']);
            }
        }

        foreach($tables as $tableName => $table)
        {
            try {
            $tab = explode('_', $tableName);
            $moduleName = $tab[0];

            if (!in_array($tab[0], $modules)) {
                continue;
            }

            $path = APPLICATION_PATH . '/modules/' . $moduleName  . '/models/DbTable/'.$table->getCapitalName().'.php';
            $table_writer->generateTableClass($table, $path);
            $this->_log('Model DbTable created : ' . $table->getCapitalName());

            $path = APPLICATION_PATH . '/modules/' . $moduleName  . '/models/DbTable/Row/'.$table->getCapitalName().'.php';
            $table_writer->generateRowClass($table, $path);
            $this->_log('Model DbTable Row created : ' . $table->getCapitalName());
        }
        catch(Exception $e) {
            var_dump($e);
            die();
        }
        }
        return $tables;
    }

    public function indexAction()
    {
        try {
            $modules = array_keys($this->_getModulesList());
            $modules = array_combine($modules, $modules);
    
            $form = new Centurion_Form();
            $form->addElement('multiCheckbox', 'modules', array('MultiOptions' => $modules));
            $form->addElement('submit', 'submit', array('lable' => 'Générer', 'required' => true));
    
            if ($form->isValid($this->_request->getParams())) {
                $modulesList = $form->getElement('modules')->getValue();
    
                $this->_makeDirStructure($modulesList);
                echo '<br />';
                $tables = $this->_inspectDb($modulesList);
                echo '<br />';
                $this->_makeAdminController($modulesList, $tables);
                echo '<br />';
                $this->_makeFrontController($modulesList, $tables);
                echo '<br />';
                $this->_makeForm($modulesList, $tables);
                echo '<br />';
                $this->_makeBootstrap($modulesList);
                echo '<br />';
                $this->_makeTestModel($modulesList, $tables);
                echo '<br />';
                $this->_makeTestController($modulesList, $tables);
                echo '<br />';
                $this->_makeTest($modulesList, $tables);
                //$this->_makeViews();
            }
            $this->view->writable = is_writable(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules');
            $this->view->form = $form;
        } catch (Exception $e) {
            var_dump($e->getTrace());
            var_dump($e->getMessage());
            die();
        }
    }

    public function _makeBootstrap($modules)
    {
        foreach ($modules as $module) {
            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($module) . DIRECTORY_SEPARATOR . 'Bootstrap.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $class = new Zend_CodeGenerator_Php_Class();
                $class->setName(ucfirst($module) . '_Bootstrap');
                $class->setExtendedClass('Centurion_Application_Module_Bootstrap');
            }

            $file->setClass($class);
            try {
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);die();
           }
        }
    }

    protected function _makeForm($modules, $tables)
    {
        foreach ($tables as $table) {
            list($moduleName, $className)  = $table->separate($table->getName());
            if (!in_array(strtolower($moduleName), $modules) || $table->isJoinTable())
                continue;
            $className = $className;
            $this->_log('Model form created : ' . $moduleName . '_'.$className);

            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($moduleName) . DIRECTORY_SEPARATOR . 'forms' . DIRECTORY_SEPARATOR . 'Model' . DIRECTORY_SEPARATOR . $className . '.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $class = new Zend_CodeGenerator_Php_Class();
                $class->setName($moduleName . '_Form_Model_'.$className);
                $class->setExtendedClass('Centurion_Form_Model_Abstract');
            }

            $method = $class->getMethod('__construct');

            if (false === $method) {
                $method = new Zend_CodeGenerator_Php_Method();
                $method->setName('__construct');

                $class->setMethod($method);
            }

            $method->setParameters(array(
                                array(
                                    'name' => 'options',
                                    'position' => 0,
                                    'DefaultValue' => array(),
                                ),
                                array(
                                    'name' => 'instance',
                                    'position' => 1,
                                    'DefaultValue' => null,
                                    'type' => 'Centurion_Db_Table_Row_Abstract',
                                )
                            )
                        );

            $excludes = array();
            $elements = array();

            foreach ($table->getFields() as $field) {
                if (in_array($field['COLUMN_NAME'], array('created_at', 'id', 'updated_at', 'slug'))) {
                    $excludes[] = $field['COLUMN_NAME'];
                    continue;
                }
                $elements[] = $field['COLUMN_NAME'];
            }
            $excludes = '\'' . implode('\', \'', $excludes) . '\'';
            $body = <<<EOS
\$this->_exclude = array($excludes);

\$this->_elementLabels = array(

EOS;
foreach ($elements as $element) {
    $body .= '\'' . $element . '\'              => $this->_translate(\'' . str_replace('_id' , '', $element) .'\'),' . "\n";
}

            $body .= <<<EOS
);

parent::__construct(\$options, \$instance);
EOS;
            $method->setBody($body);


            $property = $class->getProperty('_modelClassName');

            if (false === $property) {
                $property = new Zend_CodeGenerator_Php_Property();
                $property->setName('_modelClassName');
                $class->setProperty($property);
            }

            $property->setOptions(array('name'          =>  '_modelClassName',
                                        'visibility'    =>  'protected',
                                        'defaultValue'  =>  $moduleName . '_Model_DbTable_' . $className,
                                        ))
                                        ;

            $file->setClass($class);
            try {
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);die();
           }
        }
    }

    protected function _makeAdminNav($moduleName, $object, $table)
    {
        $navigationTable = Centurion_Db::getSingleton('core/navigation');

        $navigationRow = $navigationTable->fetchRow(array('centurion_navigation.module=?' => strtolower($moduleName), 'centurion_navigation.controller=?' => 'admin' . str_replace('_', '-', Centurion_Inflector::tableize($object))));

        if (null === $navigationRow) {
            $target = $navigationTable->findOneByLabel('Contents');

            $navigationRow = $navigationTable->createRow(array('label' => ucfirst(str_replace('_', ' ', substr(strstr($table->getName(), '_'), 1))), 'module' => strtolower($moduleName), 'controller' => 'admin-' . str_replace('_', '-', Centurion_Inflector::tableize($object))));
            $navigationTable->insertNode($navigationRow, $target);
            $navigationRow->save();
        }
    }
    
    protected function _makeAdminViews($moduleName, $object, $table)
    {
        $viewName = 'admin-' . str_replace('_', '-', Centurion_Inflector::tableize($object));
        $viewPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($moduleName) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . $viewName;


        if (!file_exists($viewPath)) {
            mkdir($viewPath);
            $this->_log('Dossier crée : ' . $viewPath);
        }

        $viewScript = $viewPath . DIRECTORY_SEPARATOR . 'form.phtml';
        $displays = array();
        $infos = array();
        $firstText = null;
        foreach ($table->getFields() as $field) {
            if (in_array($field['COLUMN_NAME'], array('created_at', 'id', 'updated_at', 'slug'))) {
                continue;
            }
            if (null === $firstText && $field['DATA_TYPE'] == 'varchar') {
                $firstText = $field['COLUMN_NAME'];
                continue;
            }
            $displays[] = $field['COLUMN_NAME'];
        }

        $str = <<<EOS
<?php
\$this->gridForm()->addHeader(\$this->form, array(
    'elements' =>  array('$firstText')
));

EOS;
    if (count($displays)> 0) {
        $displays = '\'' . implode('\', \'', $displays) . '\'';
        $str .= <<<EOS

\$this->gridForm()->addMain(\$this->form, array(
    'label'         => 'Auto generated form',
    'description'   => 'Auto generated form',
    'elements'      => array($displays)
));

EOS;
    }
    if (count($infos) > 0) {
        $infos = '\'' . implode('\', \'', $infos) . '\'';
        $str .= <<<EOS

\$this->gridForm()->addAside(\$this->form, array(
    'label'         => 'Infos',
    'elements'      => array($infos)
));

EOS;
    }

    $str .= <<<EOS

echo \$this->partial('grid/_form.phtml', \$this);
EOS;
        file_put_contents($viewScript, $str);
    }

    protected function _makeAdminController($modules, $tables)
    {
        foreach ($tables as $table) {
            list($moduleName, $realClassName)  = $table->separate($table->getName());

            if (!in_array(strtolower($moduleName), $modules) || $table->isJoinTable())
                continue;

            $this->_makeAdminViews($moduleName, $realClassName, $table);
            $this->_makeAdminNav($moduleName, $realClassName, $table);
                
            $className = 'Admin'.$realClassName.'Controller';
            $this->_log('Model AdminController created : ' . $moduleName . '_'.$className);

            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($moduleName) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $className . '.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $class = new Zend_CodeGenerator_Php_Class();
                $class->setName($moduleName . '_'.$className);
                $class->setExtendedClass('Centurion_Controller_CRUD');
            }

            $property = $class->getProperty('_formClassName');

            if (false === $property) {
                $property = new Zend_CodeGenerator_Php_Property();
                $property->setName('_formClassName');
                $class->setProperty($property);
            }

            $method = $class->getMethod('preDispatch');
            if (false === $method) {
                $method = new Zend_CodeGenerator_Php_Method();
                $method->setName('preDispatch');

                $class->setMethod($method);
            }

            $method->setBody(<<<EOS
\$this->_helper->authCheck();
\$this->_helper->aclCheck();

\$this->_helper->layout->setLayout('admin');

parent::preDispatch();
EOS
            );

            $method = $class->getMethod('init');
            if (false === $method) {
                $method = new Zend_CodeGenerator_Php_Method();
                $method->setName('init');

                $class->setMethod($method);
            }

            $contentName = str_replace('_', ' ', substr(strstr($table->getName(), '_'), 1));

            $displays = array();
            $filter = array();
            $firstcol = null;

           foreach ($table->getFields() as $field) {
                if (in_array($field['COLUMN_NAME'], array('created_at', 'id', 'updated_at', 'slug'))) {
                    continue;
                }
                $displays[] = $field['COLUMN_NAME'];
            }

            $body = <<<EOS
\$this->_displays = array (

EOS;
    if ($firstcol !== null) {
        $body .= <<<EOS
'$firstcol' => array('type' => Centurion_Controller_CRUD::COL_TYPE_FIRSTCOL,
                 'param' => array('cover' => null, 'title' => '$firstcol', 'subtitle' => null),
                 'label' => \$this->view->translate('$firstcol')),

EOS;
}
        foreach ($displays as $display) {
$body .= <<<EOS
'$display' => \$this->view->translate('$display'),

EOS;
        }
        $body .= <<<EOS
);

\$this->_filters = array (

);

\$this->view->placeholder('headling_1_content')->set(\$this->view->translate('Manage $contentName'));
\$this->view->placeholder('headling_1_add_button')->set(\$this->view->translate('$contentName'));

parent::init();
EOS;
            $method->setBody($body);
            $property->setOptions(array('name'          =>  '_formClassName',
                                        'visibility'    =>  'protected',
                                        'defaultValue'  =>  $moduleName . '_Form_Model_' . $realClassName,
                                        ))
                                        ;

            try {
                $file->setClass($class);
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);
                die();
           }
        }
    }

    public function _makeTest($modules, $tables)
    {
    foreach ($modules as $module) {
            $module = ucfirst($module);
            $fileName[$module] = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($module) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'AllTests.php';
            touch($fileName[$module]);
            $fileAllTest[$module] = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName[$module]);
            $fileAllTest[$module]->setFilename($fileName[$module]);
            $suiteBody[$module] = '';
            
            $bodyContent = <<<EOS
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', '{$module}_Test_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class {$module}_Test_AllTests
{
    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        \$suite = new PHPUnit_Framework_TestSuite('$module test suite');
        \$suite->addTest({$module}_Test_Models_AllTests::suite());
        \$suite->addTest({$module}_Test_Controllers_AllTests::suite());
        return \$suite;
    }
}
if (PHPUnit_MAIN_METHOD == '{$module}_Test_AllTests::main') {
    {$module}_Test_AllTests::main();
}    
EOS;
            $fileAllTest[$module]->setBody($bodyContent);
            $fileAllTest[$module]->setSourceDirty();
            $fileAllTest[$module]->write();
        }
    }
    
    public function _makeTestController($modules, $tables)
    {
        foreach ($modules as $module) {
            $module = ucfirst($module);
            $fileName[$module] = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($module) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . 'AllTests.php';
            touch($fileName[$module]);
            $fileAllTest[$module] = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName[$module]);
            $fileAllTest[$module]->setFilename($fileName[$module]);
            $suiteBody[$module] = '';
        }
        
        foreach ($tables as $table) {
            list($moduleName, $className)  = $table->separate($table->getName());

            if (!in_array(strtolower($moduleName), $modules) || $table->isJoinTable())
                continue;
                
            $className = $className;

            $this->_log('Controller test created for ' . $moduleName . '_'.$className);

            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . 
                            DIRECTORY_SEPARATOR . strtolower($moduleName) . 
                            DIRECTORY_SEPARATOR . 'tests' . 
                            DIRECTORY_SEPARATOR . 'Controllers' . 
                            DIRECTORY_SEPARATOR . 'Admin'.$className.'ControllerTest.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $file->setRequiredFiles(array('\'.dirname(__FILE__) . \'/../../../../../tests/TestHelper.php'));
                $class = new Zend_CodeGenerator_Php_Class();
                $class->setName($moduleName . '_Test_Controllers_Admin' . $className . 'ControllerTest');
                $class->setExtendedClass('Centurion_Test_PHPUnit_AdminControllerTestCase');
            }

            try {
                $file->setClass($class);
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);die();
           }
           
           $suiteBody[$moduleName] .= '        $suite->addTestSuite(\''.$moduleName.'_Test_Controllers_Admin'.$className.'ControllerTest\');' . "\n";
        }
        
        foreach ($modules as $moduleName) {
            $moduleName = ucfirst($moduleName);
            $bodyContent = <<<EOS
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', '{$moduleName}_Test_Controllers_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class {$moduleName}_Test_Controllers_AllTests
{

    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        \$suite = new PHPUnit_Framework_TestSuite('$moduleName Controller test suite Suite');
{$suiteBody[$moduleName]}
        return \$suite;
    }
}
if (PHPUnit_MAIN_METHOD == '{$moduleName}_Test_Controllers_AllTests::main') {
    {$moduleName}_Test_Controllers_AllTests::main();
}    
EOS;
            $fileAllTest[$moduleName]->setBody($bodyContent);
            $fileAllTest[$moduleName]->setSourceDirty();
            $fileAllTest[$moduleName]->write();
        }
    }
    
    public function _makeTestModel($modules, $tables)
    {
        foreach ($modules as $module) {
            $module = ucfirst($module);
            $fileName[$module] = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($module) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . 'AllTests.php';
            touch($fileName[$module]);
            $fileAllTest[$module] = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName[$module]);
            $fileAllTest[$module]->setFilename($fileName[$module]);
            $suiteBody[$module] = '';
        }
        
        foreach ($tables as $table) {
            list($moduleName, $className)  = $table->separate($table->getName());

            if (!in_array(strtolower($moduleName), $modules) || $table->isJoinTable())
                continue;
                
            $className = $className;

            $this->_log('Model test created for ' . $moduleName . '_'.$className);

            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($moduleName) . DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR . 'Models' . DIRECTORY_SEPARATOR . $className.'Test.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $class = new Zend_CodeGenerator_Php_Class();
                $file->setRequiredFiles(array('\'.dirname(__FILE__) . \'/../../../../../tests/TestHelper.php'));
                $class->setName($moduleName . '_Test_Models_' . $className . 'Test');
                $class->setExtendedClass('Centurion_Test_DbTable');
            }

            $method = $class->getMethod('setUp');
            if (false === $method) {
                $method = new Zend_CodeGenerator_Php_Method();
                $method->setName('setUp');

                $class->setMethod($method);
            }
            $lowerModuleName = lcfirst($moduleName);
            $lowerclassName = lcfirst($className);
            $body = <<<EOS
\$this->setTable('$lowerModuleName/$lowerclassName');
\$this->addColumns(
            array(

EOS;

            foreach ($table->getFields() as $field) {
$body .= <<<EOS
                '{$field['COLUMN_NAME']}',

EOS;
        }
        $body .= <<<EOS
        )
    );
EOS;
            $method->setBody($body);
            
            try {
                $file->setClass($class);
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);die();
           }
           
           $suiteBody[$moduleName] .= '        $suite->addTestSuite(\''.$moduleName.'_Test_Models_'.$className.'Test\');' . "\n";
        }
        
        foreach ($modules as $moduleName) {
            $moduleName = ucfirst($moduleName);
        $bodyContent = <<<EOS
if (! defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', '{$moduleName}_Test_Models_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class {$moduleName}_Test_Models_AllTests
{

    public static function main ()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite ()
    {
        \$suite = new PHPUnit_Framework_TestSuite('$moduleName Model test suite Suite');
{$suiteBody[$moduleName]}
        return \$suite;
    }
}
if (PHPUnit_MAIN_METHOD == '{$moduleName}_Test_Models_AllTests::main') {
    {$moduleName}_Test_Models_AllTests::main();
}    
EOS;
            $fileAllTest[$moduleName]->setBody($bodyContent);
            $fileAllTest[$moduleName]->setSourceDirty();
            $fileAllTest[$moduleName]->write();
        }
    }
    
    public function _makeFrontController($modules, $tables)
    {
        foreach ($tables as $table) {
            list($moduleName, $className)  = $table->separate($table->getName());

            if (!in_array(strtolower($moduleName), $modules) || $table->isJoinTable())
                continue;
            $className = $className.'Controller';

            $this->_log('Model frontController created : ' . $moduleName . '_'.$className);

            $fileName = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . strtolower($moduleName) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $className . '.php';

            touch($fileName);
            $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
            $file->setFilename($fileName);

            $class = $file->getClass();

            if ($class == null) {
                $class = new Zend_CodeGenerator_Php_Class();
                $class->setName($moduleName . '_'.$className);
                $class->setExtendedClass('Centurion_Controller_AGL');
                
                $class->setProperty(array('visibility' => 'protected', 'name' => '_model', 'value' => $moduleName . '/'.$className));
            }

            try {
                $file->setClass($class);
                $file->write();
           }catch (Exception $e ) {
                var_dump($e);die();
           }
        }
    }

    protected function _makeDirStructure($modules = array())
    {
        foreach ($modules as $module) {
            $basePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module;
            if (!file_exists($basePath)) {
                mkdir($basePath);
                $this->_log('Dossier crée : ' . $basePath);
            }
            $this->_makeDirStructureRecursive($this->_dirs, $basePath);
        }
    }

    protected function _makeDirStructureRecursive($dirs, $basePath)
    {
        if (!file_exists($basePath)) {
            mkdir($basePath);
            $this->_log('Dossier crée : ' . $basePath);
        }

        foreach($dirs as $key => $dir) {
            if (is_array($dir)) {
                $this->_log('Dossier crée : ' . $basePath . DIRECTORY_SEPARATOR . $key);
                $this->_makeDirStructureRecursive($dir, $basePath. DIRECTORY_SEPARATOR . $key);
            } else {
                $this->_log('Dossier crée : ' . $basePath . DIRECTORY_SEPARATOR . $dir);
                if (!file_exists($basePath . DIRECTORY_SEPARATOR . $dir)) {
                    mkdir($basePath . DIRECTORY_SEPARATOR . $dir);
                }
            }
        }
    }

    protected function _log($str)
    {
        echo '<div class="log">' . $str . '</div>';
    }

}

