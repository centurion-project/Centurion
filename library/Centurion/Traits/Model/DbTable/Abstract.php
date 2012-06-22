<?php
abstract class Centurion_Traits_Model_DbTable_Abstract extends Centurion_Traits_Abstract
{
    /**
     * @var Centurion_Db_Table_Abstract
     */
    protected $_model;
    protected $_modelInfo;

    protected $_defaultContraint = array('onDelete' => Centurion_Db_Table_Abstract::CASCADE,
                                         'onUpdate' => Centurion_Db_Table_Abstract::CASCADE);

    protected $_requiredColumns = array();

    public function __construct($model)
    {
        parent::__construct($model);

        $this->_model = $model;

        $this->_modelInfo = $this->_model->info();
        
        $this->checkForRequiredColumn();
    }

    public function checkForRequiredColumn($additionalCols = array())
    {
        $requiredCols = array_merge($this->_requiredColumns, $additionalCols);
        foreach($requiredCols as $col) {
            if (!$this->_checkColumnExists($col))
                throw new Centurion_Traits_Exception(sprintf('Model %s must have folowing column `%s`', get_class($this->_model), $col));
        }
    }
    
    protected function _checkColumnExists($columnName, $metadata = array())
    {
        if (!in_array($columnName, array_keys($this->_modelInfo[Centurion_Db_Table_Abstract::METADATA])))
            return false;

        foreach ($metadata as $metaName => $metaValue) {
            if (!isset($this->_modelInfo[Centurion_Db_Table_Abstract::METADATA][$metaName])
                || $this->_modelInfo[Centurion_Db_Table_Abstract::METADATA][$metaName] !== $metaValue)
                return false;
        }

        return true;
    }

    protected function _addReferenceMapRule($baseName, $column, $refModelClass, $refColumn = 'id', $constraint = null)
    {
        $refMap = $this->_referenceMap;

        $refMapRule = $this->_genRefRuleName($baseName);

        if (null === $constraint) {
            $constraint = $this->_defaultContraint;
        } else {
            $constraint = (array) $constraint;
        }

        $refMap = array_merge($refMap,
                              array($refMapRule => array_merge (array(
                                                       'columns'       => $column,
                                                       'refColumns'    => 'id',
                                                       'refTableClass' => $refModelClass
                                                   ),
                                                   $constraint)
                             )
        );

        $this->_referenceMap = $refMap;

        return $refMapRule;
    }

    protected function _addDependentTables($ruleBaseName, $targetModelClass)
    {
        $ruleName = $this->_genRefRuleName($ruleBaseName);

        $this->_dependentTables = array_merge($this->_dependentTables, array($ruleName => $targetModelClass));

        return $ruleName;
    }

    protected function _addManyDependentTables()
    {
        throw new Centurion_Traits_Exception('Method Centurion_Traits_Model_Abstract::_addManyDependentTables() is not Implemented yet');
    }
}
