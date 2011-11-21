<?php

class Centurion_View_Helper_Canonical extends Zend_View_Helper_FormElement
{
    public function canonical($locale = 'fr')
    {
        $currentRoute = Zend_Controller_Front::getInstance()->getRouter()->getCurrentRoute();
        
        if (method_exists($currentRoute, 'isTranslated') && $currentRoute->isTranslated() === true) {
            if ($this->view->url(array('@locale' => $locale)) !== $_SERVER['REQUEST_URI']) {
                $this->view->headLink ()->headLink(array('rel' => 'canonical', 'href' => $this->view->serverUrl($this->view->url(array('@locale' => 'fr')))));
            }
        }
    }
}