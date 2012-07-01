<?php
class Centurion_Social_Service_Facebook extends Centurion_Social_Service_Abstract
{
    const AUTHORIZE_URI = 'https://graph.facebook.com/oauth/authorize';
    const TOKEN_URI = 'https://graph.facebook.com/oauth/access_token';
    const PUBLISH_URI_PATTERN = 'https://graph.facebook.com/%s/feed';
    const ACCOUNTS_URI = 'https://graph.facebook.com/me/accounts';    
    
    const DEFAULT_TARGET = 'me';
    
    // facebook permissions
    const PUBLISH_STREAM    = 'publish_stream';
    const CREATE_EVENT      = 'create_event';
    const OFFLINE_ACCCESS   = 'offline_access';
    const MANAGE_PAGES   = 'manage_pages';
    
    protected $_mapReference = array('message' => 200,
                                     'name' => 200,
                                     'link' => 200,
                                     'linkCaption' => 200,
                                     'description' => 200,
                                     'picture' => null,
                                     'source' => null);
    
    protected $_facebookPermissions = array(self::PUBLISH_STREAM, 
                                            self::CREATE_EVENT,
                                            self::OFFLINE_ACCCESS,
                                            self::MANAGE_PAGES);
    
    protected $_requestedPermissions = array();
    
    protected $_clientID;
    
    protected $_clientSecret;
    
    protected $_targetObject;
    
    protected $_callbackUri;
        
    public function getRequestedPermissions()
    {
        return $this->_requestedPermissions;
    }

    public function setRequestedPermissions($permissions)
    {
        if (is_string($permissions))
            $permissions = explode(',', $permissions);
        elseif (!is_array($permissions))
            $permissions = (array) $permissions;
        
        foreach ($permissions as $key => $permission) {
            if (!in_array($permission, $this->_facebookPermissions))
                unset($permissions[$key]);
        }
        
        $this->_requestedPermissions = $permissions;
    }

    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    public function setClientSecret($clientSecret)
    {
        $this->_clientSecret = $clientSecret;
    }
    
    public function getCallbackUri()
    {
        return $this->_callbackUri;
    }

    public function setCallbackUri($callbackUri)
    {
        $this->_callbackUri = $callbackUri;
    }

    public function getClientID()
    {
        return $this->_clientID;
    }

    public function setClientID($clientID)
    {
        $this->_clientID = $clientID;
    }
    
    public function getTargetObject()
    {
        if (null === $this->_targetObject)
            $this->_targetObject = self::DEFAULT_TARGET;
            
        return $this->_targetObject;
    }

    public function setTargetObject($target)
    {
        if ($target === null) {
            $target = self::DEFAULT_TARGET;
        }
        $this->_targetObject = $target;
    }  
    
    public function publish($message)
    {
        foreach($message as $key => $params) {
            if (null !== $this->_mapReference[$key] && (strlen($params) > $this->_mapReference[$key]))
                $message[$key] = Centurion_Inflector::cuttext($params, $this->_mapReference[$key]);
        }

        if (!$this->getTokenHandler()->getToken())
            throw new Centurion_Social_NoTokenException('No access token');
        
        $target = $this->getTargetObject();
        if (null === $target || '' == trim($target))
            throw new Centurion_Social_Exception('No target for publishing has been defined');

        // if targetobject is a person id (other than "me"), it will not work
        if ($this->getTargetObject() != self::DEFAULT_TARGET) {
            $client = new Zend_Http_Client();
            $client->setParameterGet(array('access_token'   => $this->getTokenHandler()->getToken()))
                   ->setUri(self::ACCOUNTS_URI);        
            $response = $client->request(Zend_Http_Client::GET);
            
            $data = Zend_Json::decode($response->getBody());

            $pageAccessToken = null;
            echo'<pre>';
            foreach($data['data'] as $page) {

                if (in_array($this->getTargetObject(),$page)){
                    
                     $pageAccessToken = $page['access_token'];  
                     break; 
                }
            }                   
        }

        $message = array_merge(array('access_token' => ($pageAccessToken ? $pageAccessToken : $this->getTokenHandler()->getToken())),
                               $message);
                                       
        $client = new Zend_Http_Client();
        $client->setParameterPost($message)
               ->setUri(sprintf(self::PUBLISH_URI_PATTERN, $this->getTargetObject()));
        
        $response = $client->request(Zend_Http_Client::POST);
        
        $data = Zend_Json::decode($response->getBody());
        
        if (isset($data['error']))
        {            
            if ($data['error']['type'] == 'Exception') {
                
                if (strpos($data['error']['message'],'#200')) {
                    throw new Centurion_Social_NoTokenException($data['error']['message']);
                }
                if (strpos($data['error']['message'],'#210')) {
                    throw new Centurion_Social_NoTokenException($data['error']['message']);
                }
                throw new Centurion_Social_Exception($data['error']['message']);
//                elseif (strpos($data['error']['message'],'#506')) {
//                    throw new Centurion_Social_Exception($data['error']['message']); 
//                }
            }  elseif ($data['error']['type'] == 'OAuthException') {
                throw new Centurion_Social_NoTokenException($data['error']['message']);
            }
        }        
        return $data->id;
    }
    
    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        if (isset($options['token_handler'])) {
            $this->setTokenHandler($options['token_handler']);
        }
        
        if (isset($options['requested_permissions'])) {
            $this->setRequestedPermissions($options['requested_permissions']);
        }
        else {
            throw new Centurion_Social_Exception('Invalid parameters : param \'requested_permissions\' is missing');
        }
        
        if (isset($options['client_id'])) {
            $this->setClientID($options['client_id']);
        }
        else {
            throw new Centurion_Social_Exception('Invalid parameters : param \'client_id\' is missing');
        }

        if (isset($options['client_secret'])) {
            $this->setClientSecret($options['client_secret']);
        }
        else {
            throw new Centurion_Social_Exception('Invalid parameters : param \'client_secret\' is missing');
        }
        
        if (isset($options['callback_uri'])) {
            $this->setCallbackUri($options['callback_uri']);
        }
        else {
            throw new Centurion_Social_Exception('Invalid parameters : param \'callback_uri\' is missing');
        }
        
        // optional
        if (isset($options['target'])) {
            $this->setTargetObject($options['target']);
        }            
    }

    public function getLoginUrl()
    {
        //
        $params = array('client_id'    => $this->getClientID(),
                        'redirect_uri' => $this->getCallbackUri(),
                        'scope'        => implode(',', $this->getRequestedPermissions()));

        return self::AUTHORIZE_URI.'?'.http_build_query($params, '', '&');
    }
    
    public function generateAccessToken($params = null)
    {
        
        $client = new Zend_Http_Client();
        
        $client->setParameterGet(array(
                    'client_id'        => $this->getClientID(),
                    'redirect_uri'     => $this->getCallbackUri(),
                    'client_secret'    => $this->getClientSecret(),
                    'code'             => $params['code']))
               ->setUri(self::TOKEN_URI);
        
        $response = $client->request();
        
        parse_str($response->getBody(),$data);
        
        $this->getTokenHandler()->setToken($data['access_token']);
        
        return $data['access_token'];
    }
    
    public function checkIfError($params)
    {
        if (isset($params['error_reason'])) {
            switch ($params['error_reason']) {
                case 'user_denied':
                    $this->_errorMessage = 'Apps denied';
                    return true;
                default:
                    $this->_errorMessage = 'Unknown error';
                    return true;
            }
        } else {
            return false;
        }
    }
    
}
