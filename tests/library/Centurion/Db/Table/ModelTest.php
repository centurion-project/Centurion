<?php

require_once dirname(__FILE__) . '/../../../../TestHelper.php';

class Centurion_Db_Table_ModelTest extends PHPUnit_Framework_TestCase
{
	protected $_testableModel = null;
	
	protected function getTestableModel()
	{
		global $application;

        $bootstrap = $application->getBootstrap();
        $bootstrap->bootstrap('FrontController');
        $bootstrap->bootstrap('modules');
        $bootstrap->bootstrap('db');
        
        
        $moduleRessource = $bootstrap->getResource('modules');
        
		if (null == $this->_testableModel) {
			$this->_testableModel = array();
			
			$front = Centurion_Controller_Front::getInstance();
            $modules = $front->getControllerDirectory();

            $moduleEnabled = Centurion_Config_Manager::get('resources.modules');
            
            foreach ($modules as $moduleName => $module) {
                if (!in_array($moduleName, $moduleEnabled)) {
                    continue;
                }
                
                $dbTableDir = realpath($module . '/../models/DbTable');
                if (!file_exists($dbTableDir)) {
                    continue;
                }

                $dir = new Centurion_Iterator_DbTableFilter($dbTableDir);

                foreach ($dir as $fileInfo) {
                    $filename = $fileInfo->getFilenameWithoutExtension();
                    $className = sprintf('%s_Model_DbTable_%s', ucfirst($moduleName), $filename);

                    $model = Centurion_Db::getSingletonByClassName($className);
                    
                    if (method_exists($model, 'getTestCondition')) {
                    	$testCondition = $model->getTestCondition();
                    	if (null !== $testCondition)
	                    	$this->_testableModel[] = array($model, $model->getTestCondition());
                    }
                }
            }
		}
		
		return $this->_testableModel;
	}
	
    public function testTable()
    {
    	foreach ($this->getTestableModel() as $tab) {
    		list($model, $conditions) = $tab;
    		
    		if (!in_array(Centurion_Db_Table_Abstract::TABLE_IS_TESTABLE, $conditions))
    			continue;
    			
    		$model->testunit();
    	}
    }
    
    
    public function testEachRow()
    {
    	foreach ($this->getTestableModel() as $tab) {
    		list($model, $conditions) = $tab;
    		
    		if (!in_array(Centurion_Db_Table_Abstract::ROW_IS_TESTABLE, $conditions))
    			continue;
    		
    		$offset = 0;
    		$delta = 200;
    		$continue = true;
    		for ($offset = 0 ; $continue ; $offset += $delta) {
    			$continue = false;
    			$rowset = $model->fetchAll(null, null, $delta, $offset);
    			
    			foreach ($rowset as $row) {
	    			$row->testunit();
    			}
    			
    			$continue = ($rowset->count() > 0);
    		}
    	}
    }
}