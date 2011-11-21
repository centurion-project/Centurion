<?php

class Sayyes_Db_Abstract {
	
	protected $_generatorConfig;
	
	/**
	 * Return db adapter
	 * 
	 * @return  Zend_Db_Adapter_Abstract
	 */
	protected function _getAdapter() {
		return Zend_Db_Table::getDefaultAdapter();
	}
	
	/**
	 *  Removes underscores and capital the letter that was after the underscore
	 *  example: 'ab_cd_ef' to 'AbCdEf'
	 *
	 * @param string $name
	 * @return string
	 */
	protected function _getCapital($name) {
		$temp='';
		foreach (explode("_",$name) as $part) {
			$temp.=ucfirst($part);
		}
		return $temp;
	}
	
    public function separate($name) {
        $tab = explode('_', $name);
        $moduleName = ucfirst(array_shift($tab));
        $modelName = $this->_getCapital(implode('_', $tab));
        return array($moduleName, $modelName); 
    }
}