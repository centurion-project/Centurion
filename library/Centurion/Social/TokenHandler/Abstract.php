<?php
abstract class Centurion_Social_TokenHandler_Abstract
{
    
    /**
     * store the token configuration options
     * @var array
     */
    protected $_params;
    
    /**
     * store the token itself
     * @var string
     */
    protected $_token;
    
    /**
     * this class aim to manage transparently for the client the token storage
     * @param Zend_Config|array|null $options
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config)
            $this->setConfig($options);
        else
            $this->setOptions((array) $options);
    }
    
    /**
     * return the token, if needed retrieve it from the storage
     */
    public function getToken()
    {
        if (!isset($this->_token)) {
            $this->_token = $this->_retrieveToken();
        }
        
        return $this->_token;
    }
    
    /**
     * set the token and save it in the storage
     * @param string $token
     */
    public function setToken($token)
    {
        $this->_token = $this->_save($token);
    }

    /**
     * set the handler's configuration from a Zend_Config object
     * @param Zend_Config $options
     */
    public function setConfig(Zend_Config $options) 
    {
        $this->setOptions($options->toArray());
    }
    
    /**
     * set the handler's configuration from an array
     * @param array $options
     */
    abstract public function setOptions($options);    
    
    /**
     * save the token in the storage
     * @param string $token
     * @return string|null
     */
    abstract protected function _save($token);
    
    /**
     * retrieve the token from the storage
     * @return string|null
     */
    abstract protected function _retrieveToken(); 
    
    
}
