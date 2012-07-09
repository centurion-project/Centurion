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
 * @subpackage  Centurion_Test_PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Test
 * @subpackage  Centurion_Test_PHPUnit
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Test_PHPUnit_AdminControllerTestCase extends Centurion_Test_PHPUnit_ControllerTestCase
{
    //TODO: Use view helper url to make route.
    
    protected $_moduleName = null;
    protected $_objectName = null;
    
    public function __construct($name = NULL, array $data = array(), $dataName = '')
    {
        if ($this->_moduleName == null && $this->_objectName == null) {
            $class = get_class($this);
            preg_match('`([a-z]*)_.*_Admin([a-z]*)ControllerTest`i', $class, $matches);
            $this->_moduleName = strtolower($matches[1]);
            $this->_objectName = Centurion_Inflector::tableize($matches[2], '-');
        }
        parent::__construct($name, $data, $dataName);
    }
    
    public function testRedirectIfNotLogued()
    {
        $this->logInAsAnnonymous();
        $this->dispatch('/' . $this->_moduleName . '/admin-' . $this->_objectName);
        
        $this->assertRedirectRegex('`/login`');
        $this->resetResponse();
    }
    
    public function testGridIsInHtmlIfLogued()
    {
        $this->loginAsAdmin();
        $this->dispatch('/' . $this->_moduleName . '/admin-' . $this->_objectName);
        $this->assertNotRedirectRegex('`/login/`');
        $this->assertQueryCount('form#grid-action-form', 1);
        
        //Check with only the permission
    }
    
    public function testICanSeeNewForm()
    {
        $this->loginAsAdmin();
        $this->dispatch('/' . $this->_moduleName . '/admin-' . $this->_objectName .'/new');
        $this->assertQueryCount('form.form', 1);
        //Check with only the permission
    }
}
