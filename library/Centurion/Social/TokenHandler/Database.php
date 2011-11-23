<?php
class Centurion_Social_TokenHandler_Database extends Centurion_Social_TokenHandler_Abstract
{
    const PARAM_ROW = 'row';
    const PARAM_COL = 'column';
    
    public function setRow($row) {
        $this->_params[self::PARAM_ROW] = $row;
    }
    
    public function setColumn($col) {
        $this->_params[self::PARAM_COL] = $col;
    }
    
    
    protected function _save($token)
    {
        $this->_params[self::PARAM_ROW]->{$this->_params[self::PARAM_COL]} = $token;
        $this->_params[self::PARAM_ROW]->save();
        
        return $token;
    }
    
    protected function _retrieveToken()
    {
        return $this->_params[self::PARAM_ROW]->{$this->_params[self::PARAM_COL]};
    } 
    
    public function setOptions($options)
    {        
        if (!isset($options[self::PARAM_ROW]) || !$options[self::PARAM_ROW] instanceof Centurion_Db_Table_Row_Abstract)
            throw new Centurion_Social_TokenHandler_Exception('Invalid param \'row\' for the Database token handler');

        if (!isset($options[self::PARAM_COL]))
            throw new Centurion_Social_TokenHandler_Exception('Invalid param \'col\' for the Database token handler');
            
        try {
            // will throw an exception if the column doesn't exists
            $tmp = $options[self::PARAM_ROW]->{$options[self::PARAM_COL]};
        } catch (Centurion_Db_Table_Row_Exception $e) {
            throw new Centurion_Social_TokenHandler_Exception(sprintf('Invalid param \'col\' for the Database token handler (%s)', $e->getMessage()));
        }
        
        $this->_params = $options;
    }
    
}