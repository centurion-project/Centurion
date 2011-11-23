<?php

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