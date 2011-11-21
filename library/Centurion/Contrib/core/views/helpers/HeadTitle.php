<?php

class Centurion_View_Helper_HeadTitle extends Zend_View_Helper_HeadTitle
{
    public function setView(Zend_View_Interface $view)
    {
        static $first = true;


        parent::setView($view);

        if ($first && null !== ($defaultTitle = Centurion_Config_Manager::get('resources.layout.configs.headTitle.default'))) {
            $this->set($defaultTitle);
        }

        $first = false;
    }

    public function _escape($string)
    {
        $enc = 'UTF-8';
        if ($this->view instanceof Zend_View_Interface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }

        return htmlentities($string, ENT_COMPAT, $enc);
    }
}
