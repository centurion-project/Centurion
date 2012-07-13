<?php

class Centurion_View_Filter_Extend
{
    public $view;
    
    /**
     * Receive a reference of the view upon instantiation.
     *
     * @param Zend_View_Interface $view
     * @return $this
     */
    public function setView($view)
    {
        $this->view = $view;
        
        return $this;
    }
    
    /**
     * Renders the master view if needed.
     *
     * @param string $buffer
     * @return string
     */
    public function filter($buffer)
    {
        $helper = $this->view->getHelper('extend');
        
        if ($helper->isOpen() && !$this->view->isPartial()) {
            $helper->setCurrentSection($buffer);
            $buffer = $helper->render();
        }
        
        return $buffer;
    }
}
