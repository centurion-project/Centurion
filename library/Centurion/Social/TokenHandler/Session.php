<?php
class Centurion_Social_TokenHandler_Session extends Centurion_Social_TokenHandler_Abstract
{
    
    const DEFAULT_NAMESPACE = 'Centurion_Social_TokenHandler';
    const PARAM_SERVICE = 'service';
    const PARAM_NAMESPACE = 'namespace';
    
    public function setService($service)
    {
        $this->_params[self::PARAM_SERVICE] = (string) $service;
    }
    
    public function setNamespace($namespace) {
        $this->_params[self::PARAM_NAMESPACE] = (string) $namespace;
    }
    
    protected function _save($token)
    {
        $ns = new Zend_Session_Namespace($this->_params[self::PARAM_NAMESPACE]);
        $ns->{$this->_params[self::PARAM_SERVICE]} = $token;
        
        return $ns->token;
    }
    
    protected function _retrieveToken()
    {
        
        $ns = new Zend_Session_Namespace($this->_params[self::PARAM_NAMESPACE]);
        return $ns->{$this->_params[self::PARAM_SERVICE]};
    } 
    
    public function setOptions($params)
    {
        $ns = self::DEFAULT_NAMESPACE;
        
        if (is_array($params)) {
            if (!isset($params[self::PARAM_SERVICE]) || '' === trim((string) $params[self::PARAM_SERVICE]))
                throw new Centurion_Social_TokenHandler_Exception('Invalid params given to the Session token Handler');

            $this->_params[self::PARAM_SERVICE] = $params[self::PARAM_SERVICE];                 
                
            if (isset($params[self::PARAM_NAMESPACE]))die('ici');
                $ns = $params[self::PARAM_NAMESPACE];
        } else {
            $service = trim((string) $params);
            
            if ('' === $service)
                throw new Centurion_Social_TokenHandler_Exception('Invalid params given to the Session token Handler');
            
            $this->_params[self::PARAM_SERVICE] = $service; 
        }
/*        if (!Zend_Session::isStarted()){
            Zend_Session::start();
        }
        if (Zend_Session::namespaceIsset($ns)) {
            throw new Exception(sprintf('Namespace %s already exists', $ns));
        }*/
        
        $this->_params[self::PARAM_NAMESPACE] = $ns;
    }    
    
}