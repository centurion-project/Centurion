<?php
class Core_Traits_Version_Model_DbTable extends Centurion_Traits_Model_DbTable_Abstract
{
    const ORIGINAL_FIELD = 'original_id';

    protected $_originalRefRule;

    protected $_requiredColumns = array(self::ORIGINAL_FIELD);

    public function getOriginalRefRule()
    {
        return $this->_originalRefRule;
    }

    public function init()
    {
        parent::init();

        $this->_originalRefRule = $this->_addReferenceMapRule('original', self::ORIGINAL_FIELD, get_class($this->_model));

        //Centurion_Signal::factory('on_dbTable_select')->connect(array($this, 'onSelect'), $this->_model);
    }

    public function onSelect($signal, $sender, $select, $applyDefaultFilters)
    {
        throw new Centurion_Traits_Exception('Not implemented yet');
    }
}
