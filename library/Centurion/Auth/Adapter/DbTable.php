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
 * @package     Centurion_Auth
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Auth
 * @subpackage  Adapter
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
    /**
     * authenticate() - defined by Zend_Auth_Adapter_Interface.  This method is called to
     * attempt an authentication.  Previous to this call, this adapter would have already
     * been configured with all necessary information to successfully connect to a database
     * table and attempt to find a record matching the provided identity.
     *
     * @throws Zend_Auth_Adapter_Exception if answering the authentication query is impossible
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        if ($user = Centurion_Db::getSingleton('auth/user')->findOneByUsername($this->_identity)) {
            $algorithm = $user['algorithm'];
            
            if (false !== $pos = strpos($algorithm, '::')) {
              $algorithm = array(substr($algorithm, 0, $pos), substr($algorithm, $pos + 2));
            }
            
            if (!is_callable($algorithm)) {
              throw new Zend_Auth_Adapter_Exception(sprintf('The algorithm callable "%s" is not callable.', $algorithm));
            }
            
            $this->setCredential(call_user_func_array($algorithm, array($user['salt'] . $this->_credential)));
        }
        
        return parent::authenticate();
    }
}