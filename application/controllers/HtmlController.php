<?php

/**
 * This controller is used to store all static html integration
 */
class HtmlController extends Centurion_Controller_Action
{
    public function preDispatch()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();
    }

    /**
     * This function list all action available for current controller (so all available static html integration)
     *
     */
    public function indexAction()
    {
        $this->_helper->authCheck();
        $this->_helper->aclCheck();

        $reflection = new ReflectionClass('HtmlController');
        $methods = $reflection->getMethods();

        $htmlMethod = array();
        foreach ($methods as $methodClass) {
            $method = $methodClass->name;

            if ('_' !== $method[0] && 'indexAction' !== $method && 'Action' === substr($method, -6)) {
                $htmlMethod[] = Centurion_Inflector::tableize(substr($method, 0, -6), '-');
            }
        }

        ksort($htmlMethod);

        $this->view->methods = $htmlMethod;
    }
}

