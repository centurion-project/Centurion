<?php
class Centurion_Notification_Message
{

    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'info';
    
    protected $_code;
    
    protected $_type;
    
    protected $_message;
    
    public function __construct($message, $type = self::INFO, $code = null)
    {
        $this->_message = (string) $message;
        
        if (null !== $code)
            $this->_code = $message;
    }
    
    public function getMessage()
    {
        return $this->_message;
    }

    public function getCode()
    {
        return $this->_code;
    }

    public function getType()
    {
        return $this->_type;
    }    
    
}