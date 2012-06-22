<?php
abstract class Centurion_Traits_Abstract
{

    protected $_delegateLink;

    public function __construct(Centurion_Traits_Traitsable $delegateLink)
    {
        $this->_delegateLink = $delegateLink;
    }

    /**
     * check if the current trait class define or handle a call for a given method
     * @param string $methodName
     * @return bool
     */
    public function defineMethod($methodName)
    {
        if (method_exists($this, $methodName)) {
            return true;
        } else {
            return false;
        }
    }

    public function defineProperty($propertyName)
    {
        if (isset($this->{$propertyName})) {
            return true;
        } else {
            return false;
        }
    }

    public function init()
    {

    }

    // WARNING when this method is invoked, you may enter in an infinite loop use with extrem caution !
    public function __call($method, $args)
    {
        if (method_exists($this, $method))
            return call_user_func_array(array($this, $method), (array) $args);

        try {
            return $this->_delegateLink->delegateCall($this, $method, $args);
        } catch(Centurion_Exception $e) {
            throw new Centurion_Traits_Exception(sprintf('Trying to call %s on an object of type %s failed with message \'%s\'', $method, get_class($this->_delegateLink), $e->getMessage()));
        }
    }

    // WARNING when this method is invoked, you may enter in an infinite loop use with extrem caution !
    public function __get($property)
    {
        if (isset($this->$property))
            return $this->$property;

        try {
            return $this->_delegateLink->delegateGet($this, $property);
        } catch(Centurion_Exception $e) {
            throw new Centurion_Traits_Exception(sprintf('Trying to get %s on an object of type %s failed with message \'%s\'', $property, get_class($this->_delegateLink), $e->getMessage()));
        }
    }

    // WARNING when this method is invoked, you may enter in an infinite loop use with extrem caution !
    public function __set($property, $value)
    {
        if (isset($this->$property))
            return $this->$property = $value;

        try {
            return $this->_delegateLink->delegateSet($this, $property, $value);
        } catch(Centurion_Exception $e) {
            throw new Centurion_Traits_Exception(sprintf('Trying to set %s on an object of type %s failed with message \'%s\'', $property, get_class($this->_delegateLink), $e->getMessage()));
        }
    }
}
