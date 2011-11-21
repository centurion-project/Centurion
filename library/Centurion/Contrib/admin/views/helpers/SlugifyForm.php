<?php

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