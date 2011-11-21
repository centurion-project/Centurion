<?php
/**
 * Created by JetBrains PhpStorm.
 * User: david
 * Date: 06/12/10
 * Time: 21:18
 * To change this template use File | Settings | File Templates.
 */

class Sayyes_Db_Table_Writer extends Sayyes_Db_Abstract
{
    public function generateTableClass(Sayyes_Db_Table $table, $fileName) {
		$name = $table->getCapitalName();
		$messages = array();
		
        touch($fileName);
        $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
        $file->setFilename($fileName);
        
        $class = $file->getClass();
        
        if ($class == null) {
            $class = new Zend_CodeGenerator_Php_Class();
            $class->setName(ucfirst($table->getModuleName()) . '_Model_DbTable_' . $table->getCapitalName());
            $class->setExtendedClass('Centurion_Db_Table_Abstract');
        }
        
		$dockBlock = new Zend_CodeGenerator_Php_Docblock();
		$dockBlock->setShortDescription('Class for SQL table interface.');
		$dockBlock->setTag(array(	'name'			=>	'author',
									'description'	=>	'David SYLLA'));

		
		$class->setProperty(array(	'name' => 'TABLE',
									'const' => true,
									'defaultValue' => $table->getName()));

        foreach ($table->getFields() as $field) {
			$class->setProperty(array(	'name' => 'FIELD_' . strtoupper($field['COLUMN_NAME']),
										'const' => true,
										'defaultValue' => $field['COLUMN_NAME']));
		}

		$property = $class->getProperty('_name');
		if (false === $property) {
		    $property = new Zend_CodeGenerator_Php_Property();
		    $property->setName('_name');
		    $class->setProperty($property);
		}
        $property->setOptions(array(	'name'			=>	'_name',
									'visibility'	=>	'protected',
									'defaultValue'	=>	$table->getName(),
									'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> 'The table name',
																									'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'string')))
																									)
																							)
									))
									;
		$property = $class->getProperty('_primary');
        if (false === $property) {
            $property = new Zend_CodeGenerator_Php_Property();
            $property->setName('_primary');
            $class->setProperty($property);
        }
        $property->setOptions(array(	'name'			=>	'_primary',
									'visibility'	=>	'protected',
									'defaultValue'	=>	$table->getPrimaryKeys(),
									'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> 'The primary key column or columns',
																									'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'mixed')))
																									)
																							)
									)
							);
		$property = $class->getProperty('_rowClass');
        if (false === $property) {
            $property = new Zend_CodeGenerator_Php_Property();
            $property->setName('_rowClass');
            $class->setProperty($property);
        }
        $property->setOptions(array(	'name'			=>	'_rowClass',
									'visibility'	=>	'protected',
									'defaultValue'	=>	ucfirst($table->getModuleName()) . '_Model_DbTable_Row_'.$name,
									'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> 'Classname for row',
																									'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'string')))
																									)
																							)
									)
							);

        if($table->getDependentTables() != null) {
			$property = $class->getProperty('_dependentTables');
            if (false === $property) {
                $property = new Zend_CodeGenerator_Php_Property();
                $property->setName('_dependentTables');
                $class->setProperty($property);
            }
            $property->setOptions(array(	'name'			=>	'_dependentTables',
										'visibility'	=>	'protected',
										'defaultValue'	=>	$table->getDependentTables(),
										'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> 'Simple array of class names of tables that are "children" of the current table.',
																										'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'array')))
																										)
																								)
									)
								);
		}
		if($table->getReferenceMap() != null) {
			$property = $class->getProperty('_referenceMap');
            if (false === $property) {
                $property = new Zend_CodeGenerator_Php_Property();
                $property->setName('_referenceMap');
                $class->setProperty($property);
            }
        $property->setOptions(array(	'name'			=>	'_referenceMap',
										'visibility'	=>	'protected',
										'defaultValue'	=>	$table->getReferenceMap(),
										'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> 'Associative array map of declarative referential integrity rules.',
																										'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'array')))
																									)
																							)
									)
								);
		}

        if($table->getManyToMany() != null) {
			$property = $class->getProperty('_manyDependentTables');
            if (false === $property) {
                $property = new Zend_CodeGenerator_Php_Property();
                $property->setName('_manyDependentTables');
                $class->setProperty($property);
            }
        $property->setOptions(array(	'name'			=>	'_manyDependentTables',
										'visibility'	=>	'protected',
										'defaultValue'	=>	$table->getManyToMany(),
										'docblock'		=>	new Zend_CodeGenerator_Php_Docblock(array(	'shortDescription'	=> '',
																										'tags'				=>	array(new Sayyes_CodeGenerator_Php_Docblock_Tag_Var(array('datatype' => 'array')))
																									)
																							)
									)
								);
		}
		
        
        try {
            $file->setClass($class);
            $file->write();
       }catch (Exception $e ) {
            var_dump($e);die();
       }
    }
    
    public function generateRowClass(Sayyes_Db_Table $table, $fileName)
    {
    	$name = $this->_getCapital($table->getName());
        $messages = array();
        
        touch($fileName);
        $file = Zend_CodeGenerator_Php_File::fromReflectedFileName($fileName);
        $file->setFilename($fileName);
        
        $class = $file->getClass();
        
        if ($class == null) {
            $class = new Zend_CodeGenerator_Php_Class();
            $class->setName(ucfirst($table->getModuleName()) . '_Model_DbTable_Row_' . $table->getCapitalName());
            $class->setExtendedClass('Centurion_Db_Table_Row');
        }
        
        $dockBlock = new Zend_CodeGenerator_Php_Docblock();
        $dockBlock->setShortDescription('Class for SQL table interface.');
        $dockBlock->setTag(array(   'name'          =>  'author',
                                    'description'   =>  'David SYLLA'));
        
        try {
	        $file->setClass($class);
	        $file->write();
       }catch (Exception $e ) {
            var_dump($e);die();
       }
    }
}
