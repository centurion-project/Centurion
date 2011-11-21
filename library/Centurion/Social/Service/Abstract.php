<?php
abstract class Centurion_Social_Service_Abstract
{
    
    protected $_mapReference;
    
    protected $_tokenHandler;
    
    protected $_errorMessage;
    
    protected static $_service;
    
    public function getErrorMessage() {
        return $this->_errorMessage;
    }    
    
    public function __construct($service, $config = null)
    {
        
        self::$_service = $service;
        
        if ($config) {
            if ($config instanceof Zend_Config) {
                
            }
            elseif (is_array($config)) {
                $this->setOptions($config);
            }
        }
    }
    
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());
    }    

    public function setTokenHandler($param)
    {
        $token = null;
        if (null != $this->_tokenHandler) {
            $token = $this->_tokenHandler->getToken();
        }
        
        if ($param instanceof Centurion_Social_TokenHandler_Abstract) {
            $this->_tokenHandler = $param;
        } elseif (is_array($param)) {
            $this->_tokenHandler = Centurion_Social_TokenHandler_Factory::getHandler($param);
        }
        
        if (null != $token)
            $this->_tokenHandler->setToken($token);
    }
    
    public function getTokenHandler()
    {
        if (null === $this->_tokenHandler) {
            $params = array(Centurion_Social_TokenHandler_Factory::SESSION, get_class($this));
            $this->setTokenHandler($params);
        }
        
        return $this->_tokenHandler;
    }      
    
    public function getMapReference()
    {
        return self::$_mapReference;
    }

    public function getService()
    {
        return self::$_service;
    }    
    
    public function validateMap($map)
    {
        $keys = array_keys($map);
        $refKeys = array_keys($this->_mapReference);
        
        foreach($keys as $mapKey) {
            if (!in_array($mapKey, $refKeys)) {
//                if ($throwException)
                throw new Centurion_Social_Exception(sprintf("map contains invalid key (%s)", $key));
            }
        }
    }
    
    abstract public function publish($message);
    abstract public function getLoginUrl();
    abstract public function generateAccessToken($params = null);
    abstract public function checkIfError($params);
    
}