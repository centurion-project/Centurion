<?php

class Translation_View_Helper_GetCurrentLanguageInfo extends Zend_View_Helper_Abstract
{
    
    public function getCurrentLanguageInfo ()
    {
        return Translation_Model_DbTable_Language::getCurrentLanguageInfo();
    }

}