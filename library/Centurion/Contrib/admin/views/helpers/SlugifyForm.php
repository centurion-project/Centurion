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
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Admin
 * @package     View
 * @subpackage  Helper
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Laurent Chenay <lchenay@gmail.com>
 */
class Admin_View_Helper_SlugifyForm extends Zend_View_Helper_Abstract
{
    static protected $_firstTime = true;
    
    public function slugifyForm($elementName = 'name', $slugName = 'slug')
    {
        if (self::$_firstTime) {
            $this->view->HeadScript()->appendFile('/cui/plugins/utils/urlify.js');
            self::$_firstTime = false;
        }
        
        $this->view->HeadScript()->captureStart();
        ?>
        $(function() {
            $("#<?php echo $elementName;?>").keyup(function(){
                $('#<?php echo $slugName;?>').val(URLify($(this).val(), 100));
            });  
        });
        <?php 
        $this->view->HeadScript()->captureEnd();
    }
}
