<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Service
 * @subpackage  Bitly
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Service
 * @subpackage  Bitly
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Centurion_Service_Bitly
{
    const URI_BASE = 'http://api.bit.ly';

    const STATUS_OK = 'OK';
    const STATUS_RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    const STATUS_INVALID_URI = 'INVALID_URI';
    const STATUS_MISSING_ARG_LOGIN = 'MISSING_ARG_LOGIN';
    const STATUS_UNKNOWN_ERROR = 'UNKNOWN_ERROR';

    const URL_SHORTEN = '/v3/shorten';
    const URL_EXPAND = '/v3/expand';
    const URL_CLICKS = '/v3/clicks';
    const URL_PRO_DOMAIN = '/v3/bitly_pro_domain';
    const URL_LOOKUP = '/v3/lookup';
    const URL_AUTHENTICATE = '/v3/authenticate';
    const URL_INFO = '/v3/info';
    const URL_VALIDATE = '/v3/validate';

    /**
     *
     * @var Zend_Http_Response
     */
    protected $_response = null;

    /**
     *
     * @var array
     */
    protected $_data = null;

    /**
     * @return Zend_Rest_Client
     */
    protected function _getClient()
    {
        if (null === $this->_client) {
            $this->_client = new Zend_Rest_Client(self::URI_BASE);
        }
        return $this->_client;
    }

    protected function _checkErrors()
    {
        switch ($this->_data['status_txt']) {
            case self::STATUS_OK:
                break;
            case self::STATUS_RATE_LIMIT_EXCEEDED:
            case self::STATUS_INVALID_URI:
            case self::STATUS_MISSING_ARG_LOGIN:
            case self::STATUS_UNKNOWN_ERROR:
            default:
                throw new Zend_Service_Exception('Error in Bit.ly service : ' . $this->_data['status_txt'], $this->_data['status_code']);
                break;
        }
    }

    public function __construct($config)
    {

    }

    /**
     *
     * @param string $path
     * @param array $options
     */
    protected function _callApi($path, $options)
    {
        $restClient = $this->_getClient();
        $restClient->getHttpClient()->resetParameters();

        if (!isset($options['login'])) {
            $options['login'] = Centurion_Config_Manager::get('centurion.service.bitlty.login');
        }
        if (!isset($options['apiKey'])) {
            $options['apiKey'] = Centurion_Config_Manager::get('centurion.service.bitlty.apiKey');
        }

        $param['format'] = 'json';

        $this->_response = $restClient->restGet($path, $options);

        switch ($param['format']) {
            case 'json':
                $this->_data = Zend_Json::decode($this->_response->getBody());
                break;
            case 'xml':
                throw new Exception('Not yet implemented. Please use json format.');
                break;
        }

        $this->_checkErrors();

        return $this->_data['data'];
    }

    public function shorten($param)
    {
        if (is_string($param)) {
            $param = array('longUrl' => $param);
        }

        if (!isset($param['longUrl'])) {
            throw new Zend_Service_Exception('longUrl is need to shorten it.');
        }

        $url = self::URL_SHORTEN;

        $result = $this->_callApi($url, $param);

        return $result['url'];
    }

    public function expand($shortUrl)
    {
        throw new Exception('Not yet implemented');
    }

    public function clicks()
    {
        throw new Exception('Not yet implemented');
    }

    public function proDomain()
    {
        throw new Exception('Not yet implemented');
    }

    public function loookup()
    {
        throw new Exception('Not yet implemented');
    }

    public function authenticate()
    {
        throw new Exception('Not yet implemented');
    }

    public function info()
    {
        throw new Exception('Not yet implemented');
    }

    public function validate()
    {
        throw new Exception('Not yet implemented');
    }
}
