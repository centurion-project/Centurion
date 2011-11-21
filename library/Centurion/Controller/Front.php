<?php

class Centurion_Controller_Front extends Zend_Controller_Front
{
    /**
     * Return the router object.
     *
     * Instantiates a Zend_Controller_Router_Rewrite object if no router currently set.
     *
     * @return Zend_Controller_Router_Interface
     */
    public function getRouter()
    {
        if (null == $this->_router) {
            require_once 'Zend/Controller/Router/Rewrite.php';
            $this->setRouter(new Centurion_Controller_Router_Rewrite());
        }

        return $this->_router;
    }
    
    /**
     * Singleton instance
     *
     * @return Centurion_Controller_Front
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}