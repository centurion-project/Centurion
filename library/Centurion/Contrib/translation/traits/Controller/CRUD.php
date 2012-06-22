<?php
class Translation_Traits_Controller_CRUD extends Translation_Traits_Controller
{
    static $_filters = false;

    public function __construct($controller)
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(self::$_filters);
        parent::__construct($controller);
    }
    public function init()
    {
        parent::init();
        try {
            $displays = $this->_displays;
            $displays['language__name'] = array('label' => $this->view->translate('Language'));
            $displays['missing'] = array(
                    'label' => $this->view->translate('Missing translation'),
                    'type' => Centurion_Controller_CRUD::COLS_ROW_FUNCTION,
                    'function' => 'getMissingTranslation',
                    'sort' => false
            );
            $this->_displays = $displays;
        } catch (Exception $e) {

        }

        Centurion_Signal::factory('pre_dispatch')->connect(array($this, 'preDispatch'), $this->_controller);
    }

    public function indexAction()
    {
        Centurion_Db_Table_Abstract::setFiltersStatus(true);
    }

    public function translateAction()
    {
        $form = $this->_getForm();
        $fromPk = $this->_controller->getRequest()->getParam('from');
        $targetLang = $this->_controller->getRequest()->getParam('lang');

        $this->_controller->getRequest()->setParam('from', null);
        $this->_controller->getRequest()->setParam('lang', null);

        $model = Centurion_Db::getSingletonByClassName(get_class($form->getModel()));
        Centurion_Db_Table_Abstract::setFiltersStatus(false);
        try {
            $row = $model->get(array('language_id' => $targetLang,
                                     'original_id' => $fromPk));

            Centurion_Db_Table_Abstract::restoreFiltersStatus();
            $this->_form = null;

            $this->_controller->getRequest()->setParam('id', $row->id);
            self::$_filters = false;
            Centurion_Db_Table_Abstract::setFiltersStatus(self::$_filters);
            $this->getAction();

        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            $this->_controller->getRequest()->setParam(Centurion_Controller_CRUD::PARAM_FORM_VALUES, array('original_id' => $fromPk,
                                                                                                           'language_id' => $targetLang));
            $this->newAction();
        }
    }

    public function preDispatch()
    {
        $languageTable = Centurion_Db::getSingleton('translation/language');
        $languageName = array_flip(Zend_Locale_Data_Translation::$languageTranslation);
        
        $languagesArray = array();
        foreach ($languageTable->fetchAll() as $lang) {
            $languagesArray[$lang->pk] = $languageName[$lang->locale];
        }

        try {
            $this->_filters = array_merge(array ('language_id' =>  array('type' =>  Centurion_Controller_CRUD::FILTER_TYPE_CHECKBOX,
                                                                         'behavior' =>  Centurion_Controller_CRUD::FILTER_BEHAVIOR_IN,
                                                                         'label'   => $this->_controller->view->translate('Language'),
                                                                         'data'    => $languagesArray
                                                                   )
                                          ),
                                          $this->_filters
                             );
        } catch (Exception $e) {

        }
    }

    public function _preRenderForm()
    {
        $this->view->formViewScript[] = $this->_formViewScript;
        $this->_formViewScript = 'traits/form.phtml';

    }
}
