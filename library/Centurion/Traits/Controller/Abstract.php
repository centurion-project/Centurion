<?php
abstract class Centurion_Traits_Controller_Abstract extends Centurion_Traits_Abstract
{
    protected $_controller;
    
    public function __construct($controller) 
    {
        parent::__construct($controller);
        
        $this->_controller = $controller;
    }
}