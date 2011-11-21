<?php
interface Centurion_Delegate_Interface 
{
    public function isAllowedContext($context, $resource = null);
    
    public function delegateCall($context, $methodName, $args = array());
    
    public function delegateGet($context, $propertyName);
    
    public function delegateSet($context, $propertyName, $value);
}