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
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_View_Helper_Email extends Zend_View_Helper_Abstract
{
    /**
     * @param string $email
     * @return $this|string
     */
    public function email($email = '')
    {
        if (!empty($email)) {
            return $this->_crypt($email);
        }

        return $this;
    }

    public function mailto($email, $content = null, $title = null)
    {
        $email = $this->_crypt($email);

        if (null === $content) {
            $content = $email;
        }

        if (null === $title) {
            $title = $email;
        }

        return <<<EOF
<a href="mailto:{$email}" title="{$title}">{$content}</a>
EOF;
    }

    protected function _crypt($email)
    {
        $crypt = '';
        foreach ((array) str_split($email) as $char) {
            $crypt .= sprintf("&#%d", ord($char));
        }

        return $crypt;
    }
}
