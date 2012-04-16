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
 * @package     Centurion_Form
 * @subpackage  Validator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Form
 * @subpackage  Validator
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @TODO: this is not a form validator. It's a global validator.
 */
class Auth_Form_Validator_Login extends Zend_Validate_Abstract
{
    const NOT_MATCH = 'notmatch';
    const DB_INVALID = 'databaseinvalid';

    /**
     * Db Adapter.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_dbAdapter = null;

    /**
     * Table name.
     *
     * @var string
     */
    protected $_tableName = null;

    /**
     * Login column.
     *
     * @var string
     */
    protected $_loginColumn = null;

    /**
     * Password column.
     *
     * @var string
     */
    protected $_passwordColumn = null;

    /**
     * Salting mechanism.
     *
     * @var string
     */
    protected $_saltingMechanism = null;

    /**
     * Name of the alternative checked column.
     *
     * @var string
     */
    protected $_checkColumn = null;

    /**
     * Auth adapter name.
     *
     * @var string
     */
    protected $_authAdapter = 'Zend_Auth_Adapter_DbTable';

    /**
     * Array of validation failure messages
     *
     * @var array
     */
    protected $_messageTemplates = array(
         self::NOT_MATCH  => 'Invalid credentials',
         self::DB_INVALID => 'Database could not find a valid record, check your params'
    );

    /**
     * Constructor.
     *
     * @param array $params Params
     * @return void
     */
    public function __construct(array $params)
    {
        foreach ($params as $paramName => $paramValue) {
            $paramName = '_' . $paramName;
            if (property_exists($this, $paramName)) {
                $this->{$paramName} = $paramValue;
            }
        }
    }

    /**
     * Returns true if and only if $value passes all validations in the chain
     *
     * Validators are run in the order in which they were added to the chain (FIFO).
     *
     * @param  mixed $value
     * @return boolean
     * @TODO: check that required parameter are set
     */
    public function isValid($value, $context = null)
    {
        $adapter = new $this->_authAdapter($this->_dbAdapter,
                                           $this->_tableName,
                                           $this->_loginColumn,
                                           $this->_passwordColumn,
                                           $this->_saltingMechanism);
                                           
        $adapter->setIdentity($context['login']);
        $adapter->setCredential($value);

        if (null !== $this->_checkColumn) {
            $adapter->getDbSelect()->where($this->_checkColumn);
        }

        try {
            $result = Centurion_Auth::getInstance()->authenticate($adapter);
        } catch (Zend_Auth_Exception $e) {
            $this->_error(self::DB_INVALID);

            return false;
        }

        if ($result->isValid()) {
            Centurion_Signal::factory('pre_login')->send(null, $adapter);

            $result = $adapter->getResultRowObject(null);
            Centurion_Auth::getInstance()->clearIdentity();
            Centurion_Auth::getInstance()->getStorage()->write($result);

            //Zend_Session::writeClose(false);

            Centurion_Signal::factory('post_login')->send(null, $result);

            return true;
        }

        $this->_error(self::NOT_MATCH);

        return false;
    }
}
