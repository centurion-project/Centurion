<?php
/**
 * @class MultiselectOrder_Form_Decorator_MultiselectOrder
 * Generate a multiselect orderable.
 * This decorator extends of Zend_Form_Decorator_ViewScript to set the view to use
 * and add the basePath to find the view resource
 *
 * @package Core
 * @author Richard DELOGE, rd@octaveoctave.com
 * @copyright Octave & Octave
 */
class Core_Form_Decorator_MultiselectOrder extends Zend_Form_Decorator_ViewScript{
    /**
     * View script to build the multi select ordered
     * @var string
     */
    protected $_viewScript = 'centurion/form/_element-multiselect.phtml';
}