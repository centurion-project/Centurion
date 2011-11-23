<?php
class Centurion_Social_Service_Twitter extends Centurion_Social_Service_Abstract
{
    
    private $_configOauth = array(
        'version' => '1.0', // there is no other version...
        'requestScheme' => Zend_Oauth::REQUEST_SCHEME_HEADER,
        'signatureMethod' => 'HMAC-SHA1',
        'callbackUrl' => 'http://octaveoctave.local/social/auth/twitter/',
        'requestTokenUrl' => 'http://twitter.com/oauth/request_token',
        'authorizeUrl' => 'http://twitter.com/oauth/authorize',
        'accessTokenUrl' => 'http://twitter.com/oauth/access_token',
        'consumerKey' => '',
        'consumerSecret' => ''
    );
    
    private $_account = '';
    
    
    public function __set($name,$value)
    {
        if ($name == 'access_token'){
            $value = unserialize($value);
            parent::__set($name,$value);
        }
        else {
            parent::__set($name,$value);
        }
    }
    
    public function publish($message)
    {
        if (empty($this->access_token)){
            throw new Centurion_Exception('No access token set');
        }
    }
    
    public function getLoginUrl()
    {
        $consumer = new Zend_Oauth_Consumer($this->_configOauth);
        $token = $consumer->getRequestToken();
    }
    
    public function generateAccessToken($params = null)
    {
    }
    
    public function checkIfError($params)
    {
    }    
}