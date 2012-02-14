To use the multiselect with ordering
====================================

*   Add a trait on your form to support decorator provided by Core

                class myForm
                        extends Centurion_Form_Model_Abstract
                        implements  Core_Traits_Decorators_Form_Model_Interface


*   overload the method `render` and add these ligne before call to method `render` of parent :
        (because Centurion_Form_Model reset elements decorators).

                $this->getElement('myManyToManyRelation')->setDecorators(array(
                           array('Multiselect' => 'MultiselectOrder')
                       )
                   );


