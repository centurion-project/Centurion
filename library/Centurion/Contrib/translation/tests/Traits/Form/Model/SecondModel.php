<?php
/**
 * @class Translation_Test_Traits_Form_Model_SecondModel
 * @package Tests
 * @subpackage Translatable
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * Form to test trait translation on form
 */
class Translation_Test_Traits_Form_Model_SecondModel
    extends     Centurion_Form_Model_Abstract
    implements  Translation_Traits_Form_Model_Interface{


    public function __construct($options = array()) {
        $this->_model = new Translation_Test_Traits_Model_DbTable_SecondModel();

        $this->_exclude = array('id', 'thirds', 'fourths');

        $this->_elementLabels = array(
            'title'			        =>  $this->_translate('Title@backoffice'),
            'content' 		        =>  $this->_translate('Short description@backoffice'),
            'is_active' 		        =>  $this->_translate('Image@backoffice')
        );

        parent::__construct($options);
    }

    public function init()   {
        parent::init();

        $this->getElement('content')
            ->setAttrib('class', 'field-rte')
            ->setAttrib('large', true)
            ->removeFilter('StripTags');
    }

    public function hasPermissionForField($key, $context) {
        if (Translation_Traits_Form_Model::BUTTON_TRANSLATION == $key) {
            $locale = $context[0];
            $user = Centurion_Auth::getCurrent();
            return $user->isAllowed(sprintf('translation_territory_%s', $locale));
        }
    }
}
