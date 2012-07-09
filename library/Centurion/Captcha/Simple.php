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
 * @package     Centurion_Captcha
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Captcha
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Antoine Roesslinger <ar@octaveoctave.com>
 * @todo move it to Centurion_Validate
 */
class Centurion_Captcha_Simple extends Zend_Captcha_Word
{
    protected $_useNumbers = false;
    
    protected $_pointer;
    
    /**#@+
     * Error codes
     */
    const LABEL         = 'label';
    const MISSING_VALUE = 'missingValue';
    const MISSING_ID    = 'missingID';
    const BAD_CAPTCHA   = 'badCaptcha';
    /**#@-*/

    /**
     * Error messages
     * @var array
     */
    protected $_messageTemplates = array(
        self::LABEL         => 'Please, fill in the field with the charactere %s of this string',
        self::MISSING_VALUE => 'Empty captcha value',
        self::MISSING_ID    => 'Captcha ID field is missing',
        self::BAD_CAPTCHA   => 'Captcha value is wrong',
    );
    
    public function getPointer()
    {
        if (empty($this->_pointer)) {
            $session        = $this->getSession();
            $this->_pointer = $session->pointer;
        }
        return $this->_pointer;
    }

    /**
     * @param $pointer
     * @return $this
     */
    private function _setPointer($pointer)
    {
        $session          = $this->getSession();
        $session->pointer = $pointer;
        $this->_pointer   = $pointer;
        return $this;
    }
    
	public function render(Zend_View_Interface $view = null, $element = null)
    {
        $this->_randomPointer();
        
        return  $view->translate($this->_createMessage(self::LABEL, null), $this->getPointer())
                . ' <b>' . $this->getWord() . '</b>';
    }
    
    public function isValid($value, $context = null)
    {
        if (empty($value['input'])) {
            $this->_error(self::MISSING_VALUE);
        }
        
        if (!isset($value['id'])) {
            $this->_error(self::MISSING_ID);
            return false;
        }
        
        $this->_id = $value['id'];
        
        $word = $this->getWord();
        $pointer = $this->getPointer();
        
        $char = substr($word, $pointer - 1, 1);
        
        if ($char === $value['input']) {
            return true;
        } else {
            $this->_error(self::BAD_CAPTCHA);
            
            return false;
        }
    }
    
    private function _randomPointer()
    {
        $wordlen = $this->getWordlen();
        
        $this->_setPointer(mt_rand(1, $wordlen));
    }
    
}
