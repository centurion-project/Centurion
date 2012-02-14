<?php
/**
 * @class Core_Traits_Slug_Form_Model
 * Trait to auto-populate the PluginLoader of Forms
 * to allow it to load form decorator in Core
 *
 * @package Core
 * @author Richard DELOGE, rd@octaveoctave.com
 * @copyright Octave & Octave
 */
class Core_Traits_Decorators_Form_Model extends Centurion_Traits_Form_Abstract
{
    public function __construct($form)
    {
        parent::__construct($form);
        Centurion_Signal::factory('pre_generate')->connect(array($this, 'preGenerate'), $form);
    }

    /**
     * Populate pluginLoader to allow it to find Core Decorator
     */
    public function preGenerate()
    {
        //To use the decorator MultiselectOrder
        $this->_form->getPluginLoader(Centurion_Form::DECORATOR)->addPrefixPath(
            'Core_Form_Decorator',
            APPLICATION_PATH.'/../library/Centurion/Contrib/core/forms/Decorator'
        );
    }
}