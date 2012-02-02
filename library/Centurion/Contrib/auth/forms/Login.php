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
 * @package     Centurion_Contrib
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Auth
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Auth_Form_Login extends Centurion_Form
{
    const FORM_NAME = 'loginform';

    /**
     * Number of seconds to expire the namespace.
     *
     * @var string
     */
    protected $_loginLifetime = null;

    /**
     * The validator class name.
     *
     * @var string
     */
    protected $_formValidator = 'Auth_Form_Validator_Login';

    /**
     * Constructor.
     *
     * @param array $params Params to instantiate the form validator
     * @param string $formName Form name
     * @param string $loginLifetime Number of seconds to expire the namespace
     * @return void
     */
    public function __construct(array $params, $formName = self::FORM_NAME, $loginLifetime = 904800)
    {
        $this->cleanForm();

        $keys = array('dbAdapter', 'tableName', 'loginColumn', 'passwordColumn');
        $diff = array_diff_key(array_flip($keys), $params);

        if (count($diff) != 0) {
           throw new Centurion_Form_Exception('constructor array must have keys for ' . implode(' ', array_keys($diff)));
        }

        if (!$params['dbAdapter'] instanceof Zend_Db_Adapter_Abstract) {
            throw new Centurion_Form_Exception('dbAdapter must be an instance of Zend_Db_Adapter_Abstract');
        }

        $this->addElements(array(
            array('text', 'login', array(
                'required'  =>  true,
                'Label'     =>  $this->_translate('Username@backoffice')
            )),
            array('password', 'password', array(
                'required'      =>  true,
                'Label'         =>  $this->_translate('Password@backoffice'),
                'validators'    =>  array(
                    array(
                        'validator'             =>  'StringLength',
                        'options'               =>  array('min' =>  5),
                        'breakChainOnFailure'   => true
                    ),
                    array(new $this->_formValidator($params))
                )
            )),
            array('checkbox', 'remember_me', array('Label' => $this->_translate('Remember me@backoffice'))),
            array('hidden', 'next', array()),
        ));

        $this->addElement('hash', '_XSRF', array('salt' => 'login'));
        
        $this->setMethod(self::METHOD_POST)
             ->setName($formName)
             ->setAttrib('id', $formName);


        $this->_loginLifetime = abs((int) $loginLifetime);

        parent::__construct();
    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);

        if ($valid) {
            $session = new Zend_Session_Namespace('Zend_Auth');
            if (((bool) $this->getValue('remember_me'))) {
                Zend_Session::rememberMe();
            } else {
                $session->setExpirationSeconds($this->_loginLifetime);
            }
        }

        return $valid;
    }

    /**
     * Retrieve the identity attached to the form process.
     *
     * @return Auth_Model_DbTable_Row_User
     */
    public function getIdentity()
    {
        return Centurion_Auth::getInstance()->getIdentity();
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasValidIdentityYet()
    {
        return Centurion_Auth::getInstance()->hasIdentity();
    }
}