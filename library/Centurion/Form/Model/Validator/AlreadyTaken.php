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
 * @package     Centurion_Form
 * @subpackage  Validator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Validator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Form_Model_Validator_AlreadyTaken extends Zend_Validate_Abstract
{
    const MATCH_FOUND = 'match';
    
    /**
     * Model DbTable.
     *
     * @var string|Centurion_Db_Table_Abstract
     */
    protected $_model = null;
    
    /**
     * Params to check.
     *
     * @var string
     */
    protected $_params = array();
    
    protected $_messageTemplates = array(
         self::MATCH_FOUND  => "'%value%'is already taken, please choose another.",
    );
    
    public function __construct($model, $column, $params = array())
    {
        $this->_model = $model;
        $this->_column = $column;
        $this->_params = $params;
    }
    
    /**
     * Validate element value.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     * @TODO use exists db and not get to avoid a real query
     */
    public function isValid($value, $context = null)
    {
        $this->_params[$this->_column] = $value;
        
        try {
            $userRow = $this->_getModel()->get($this->_params);
            
            $this->_error(self::MATCH_FOUND, $value);
            
            return false;
        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            return true;
        } catch (Centurion_Db_Table_Row_Exception_MultipleObjectsReturned $e) {
            $this->_error(self::MATCH_FOUND, $value);
            
            return false;
        }
    }
    
    /**
     * Set params to check a value.
     *
     * @param array $params 
     * @return Centurion_Form_Model_Validator_AlreadyTaken
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
        
        return $this;
    }
    
    /**
     * Merge params.
     *
     * @param array $params 
     * @return Centurion_Form_Model_Validator_AlreadyTaken
     */
    public function mergeParams(array $params)
    {
        $this->_params = array_merge($this->_params, $params);
        
        return $this;
    }
    
    /**
     * Retrieve params.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }
    
    /**
     * Retrieve DbTable linked to validator.
     *
     * @return Centurion_Db_Table_Abstract
     */
    protected function _getModel()
    {
        if (is_string($this->_model)) {
            $this->_model = Centurion_Db::getSingleton($this->_model);
        }
        
        return $this->_model;
    }
}
