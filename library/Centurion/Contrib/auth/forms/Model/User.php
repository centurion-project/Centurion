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
class Auth_Form_Model_User extends Centurion_Form_Model_Abstract
{
    /**
     * Excluded elements.
     *
     * @var array
     */
    protected $_exclude = array('id', 'created_at', 'last_login', 'updated_at', 'algorithm', 'salt', 'first_name', 'last_name');

    /**
     * Constructor
     *
     * @param   array|Zend_Config           $options    Options
     * @param   Centurion_Db_Table_Abstract $instance   Instance attached to the form
     * @return void
     */
    public function __construct($options = array(), Centurion_Db_Table_Row_Abstract $instance = null)
    {
        $this->_model = Centurion_Db::getSingleton('auth/user');

        $this->_elementLabels = array(
            'username'          =>  $this->_translate('Username'),
            'password'          =>  $this->_translate('Password'),
            'email'             =>  $this->_translate('Email'),
            'user_parent_id'    =>  $this->_translate('User parent'),
            'can_be_deleted'    =>  $this->_translate('Can be deleted'),
            'is_staff'          =>  $this->_translate('Is staff'),
            'is_active'         =>  $this->_translate('Is active'),
            'is_super_admin'    =>  $this->_translate('Is super admin'),
            'groups'            =>  $this->_translate('Groups'),
            'permissions'       =>  $this->_translate('Permissions')
        );

        parent::__construct($options, $instance);
    }

    /**
     * Initialize form (used by extending classes)
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->getElement('username')->addValidators(array(
            array('Regex',
                  false,
                  array('/^[a-z][a-z0-9._]{2,}$/i',
                        'messages' => array('regexNotMatch' => $this->_translate('Special characters are not allowed')))),
            array(new Centurion_Form_Model_Validator_AlreadyTaken('auth/user', 'username')),
        ));

        $this->getElement('password')->addValidator('stringLength', false, array('min' =>  4));

        $this->getElement('email')->setRequired(true)
                                  ->addValidator('EmailAddress');

        $password = clone $this->getElement('password');
        $password->addValidator(new Centurion_Validate_IdenticalField('password', 'Password'))
                 ->setLabel($this->_translate('Password confirmation'))
                 ->setName('password_again');

        $this->getElement('email')->addValidator(new Centurion_Form_Model_Validator_AlreadyTaken('auth/user', 'email'));

        $email = clone $this->getElement('email');
        $email->addValidator(new Centurion_Validate_IdenticalField('email', 'Email'))
              ->setLabel($this->_translate('Email confirmation'))
              ->setName('email_again')
              ->setRequired(true);

        $this->addElements(array($password, $email))
             ->moveElement('password_again', 'after', 'password')
             ->moveElement('email_again', 'after', 'email');

    }

    /**
     * Validate the form
     *
     * @param  array $data
     * @return boolean
     */
    public function isValid($data)
    {
        if (!isset($data['password']) || empty($data['password'])) {
            unset($data['password_again']);
            unset($data['password']);
        }

        if (isset($data['email']) && !empty($data['email'])) {
            $this->getElement('email_again')->setRequired(true);
        }

        return parent::isValid($data);
    }

    /**
     * Event when a form is populated with an instance.
     *
     * @return void
     */
    public function _onPopulateWithInstance()
    {
        parent::_onPopulateWithInstance();

        if ($this->hasInstance()) {

            if ($this->getElement('username')) {
                $this->getElement('username')->getValidator('Centurion_Form_Model_Validator_AlreadyTaken')
                                             ->setParams(array('!id' => $this->getInstance()->id));
            }

            if ($this->getElement('email_again')) {
                $this->getElement('email_again')->setValue($this->getElement('email')->getValue());
            }

            $this->getElement('email')->getValidator('Centurion_Form_Model_Validator_AlreadyTaken')
                                      ->setParams(array('!id' => $this->getInstance()->id));

            if ($this->getElement('password')) {
                $this->getElement('password')->setValue(null)
                                             ->setRequired(false);

                $this->getElement('password_again')->setRequired(false);
            }
        }
    }

    /**
     * Process values attached to the form.
     *
     * @param array $values Values
     * @return array Values processed
     */
    protected function _processValues($values)
    {
        $values = parent::_processValues($values);

        if ($this->hasInstance()) {
            $password = $this->getElement('password');

            if (null !== $password && !$password->getValue()) {
                unset($values['password']);
            }
        }

        return $values;
    }
}