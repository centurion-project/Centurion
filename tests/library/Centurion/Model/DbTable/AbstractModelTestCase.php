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
 * @package     Centurion_Test
 * @subpackage  PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * Unit tests for models.
 * 
 * @category    Centurion
 * @package     Centurion_Test
 * @subpackage  PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
abstract class Centurion_Model_DbTable_AbstractModelTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Centurion model classe.
     * 
     * @var Centurion_Db_Table_Abstract
     */
    protected $_model = null;
    
    /**
     * Data inserted.
     * 
     * @var array
     */
    protected $_data = array();
    
    /**
     * @return array
     */
    protected function _getData()
    {
        return $this->_data;
    }
    
    public function setUp()
    {
    }
    
    /**
     * Generic test for findOneBy method.
     */
    public function testFindOneBy()
    {
        return;
        $modelId = $this->_model->insert($this->_getData());
        $this->assertFalse(null === $modelId);
        
        if (is_array($modelId)) {
            $this->_data = call_user_func_array(array($this->_model, 'find'), array_values($modelId))->current()
                                                                                                     ->toArray();
        } else {
            $this->_data = $this->_model->find($modelId)
                                        ->current()
                                        ->toArray();
        }                      
        foreach ($this->_getData() as $key => $value) {
            if (null === $value)
                continue;
            
            $method = 'findOneBy' . Centurion_Inflector::classify($key);
            $modelRow = $this->_model->{$method}($value);
            $this->assertEquals($this->_getData(), $modelRow->toArray(),
                                sprintf('Method %s of %s object doesn\'t return the expected values',
                                        $method, get_class($this->_model)));
        }
    }
}