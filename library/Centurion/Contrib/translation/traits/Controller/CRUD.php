<?php
class Translation_Traits_Controller_CRUD extends Translation_Traits_Controller
{
    static $_filters = false;
    const MISSING_TRANSLATION_INDICATOR = 'missing_translation_indicator';
    
    public function __construct($controller){
        Centurion_Db_Table_Abstract::setFiltersStatus(self::$_filters);
        parent::__construct($controller);
    }

    public function init(){
        parent::init();

        //Add a column in the list to display for each record, its current language and all missing translations
        try {
            $displays = $this->_displays;

            $displays['language__name'] = array('label' => $this->view->translate('Language'));
            $displays['getMissingTranslation'] = array(
                        'type'      => Centurion_Controller_CRUD::COLS_CALLBACK,
                        'label'     => $this->view->translate('Missing translations'),
                        'sort'      => array($this, 'sortMissingTranslation'),
                        'sortable'  => false
                     );

            $this->_displays = $displays;
        } catch (Exception $e) { /* do nothing*/ }


        Centurion_Signal::factory('pre_dispatch')
                            ->connect(
                                array($this, 'preDispatch'),
                                $this->_controller
                            );

        //To customize form according to the request
        //.. to add a filter to define this form as a translate form
        Centurion_Signal::factory('post_generate')
                            ->connect(
                                array($this, 'postGenerateForm'),
                                'Centurion_Form_Model_Abstract'
                            );

        //... and to change object id if the user click on save and continue
        Centurion_Signal::factory('post_save')
                            ->connect(
                                array($this, 'postSaveForm'),
                                'Centurion_Form_Model_Abstract'
                            );
    }

    public function indexAction()
    {
        $filter = $this->_getParam('filter');
        if(isset($filter['language_id'])) {
            Centurion_Db_Table_Abstract::setFiltersStatus(false);
        }
        else {
            Centurion_Db_Table_Abstract::setFiltersStatus(true);
        }
    }

    public function translateAction()
    {
        $form = $this->_getForm();
        $request = $this->_controller->getRequest();

        $fromPk = $request->getParam('from');
        $targetLang = $request->getParam('lang');

        if(null === $fromPk){
            //TO not allow user to create the translated version before the first version
            $this->_forward('new');
            return;
        }

        $request->setParam('from', null);
        $request->setParam('lang', null);

        $model = Centurion_Db::getSingletonByClassName(get_class($form->getModel()));
        Centurion_Db_Table_Abstract::setFiltersStatus(false);

        try {
            $row = $model->get(array(
                                    'language_id' => $targetLang,
                                    'original_id' => $fromPk
                                 )
                              );

            Centurion_Db_Table_Abstract::restoreFiltersStatus();
            $this->_form = null;

            $request->setParam('id', $row->id);

            self::$_filters = false;
            Centurion_Db_Table_Abstract::setFiltersStatus(self::$_filters);
            $this->getAction();

        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            $request->setParam( Centurion_Controller_CRUD::PARAM_FORM_VALUES,
                                array(  'original_id' => $fromPk,
                                        'language_id' => $targetLang
                                )
                              );

            Centurion_Signal::factory('on_new_translation_before_form')
                                ->send($this->_controller);

            $this->newAction();
        }
    }

    public function preDispatch()
    {
        $languageTable = Centurion_Db::getSingleton('translation/language');
        $languagesArray = array();

        foreach ($languageTable->fetchAll() as $lang) {
            if(isset($this->_userPermissions)
                && true === $this->_userPermissions
                && $this->_helper->checkTranslationPermission(Centurion_Auth::getCurrent(), $lang) == false) {

                continue;
            }

            if($lang->name === null) {
                 $languagesArray[$lang->pk] = $lang->locale;
            } else {
                 $languagesArray[$lang->pk] = $this->view->translate($lang->name);
            }
        }

        try {
            $this->_filters = array_merge(
                                $this->_filters,
                                array(
                                    'language_id' => array(
                                        'type'      => Centurion_Controller_CRUD::FILTER_TYPE_CHECKBOX,
                                        'behavior'  => Centurion_Controller_CRUD::FILTER_BEHAVIOR_IN,
                                        'label'     => $this->_controller->view->translate('Language'),
                                        'data'      => $languagesArray
                                    )
                                )
                              );
        } catch (Exception $e) { /* do nothing */ }
    }

    public function _preRenderForm()
    {
        $this->view->formViewScript[] = $this->_formViewScript;
        $this->_formViewScript = 'traits/form.phtml';

    }

    public function getMissingTranslation($row)
    {
        if (null !== $row->original_id) {
            $row = $row->original;
        }

        $name = $row->getTable()->info(Centurion_Db_Table_Abstract::NAME);

        $select = Centurion_Db::getSingleton('translation/language')
                                ->select(true)
                                ->setIntegrityCheck(false);

        $joinLeftCondition = $name . '.`language_id` = `translation_language`.`id`'
                         .' and (' .$name . '.`id` = ' .$row->id .' or ' . $name . '.`original_id` = ' . $row->id . ')';
        $select->joinLeft($name, $joinLeftCondition, array());
        $select->where($name . '.`id` is null');
        
        $languages = $select->fetchAll();

        $str = array();

        foreach ($languages as $language) {
            if(isset($row->_usePermissions)
                && $row->_usePermissions == true) {

                if($this->hasPermissionForField(self::MISSING_TRANSLATION_INDICATOR, array($language)) == true) {
                    $str[] = '<img src="' . $this->view->baseUrl($language->flag) . '" />';
                }
            } else {
                $str[] = '<img src="' . $this->view->baseUrl($language->flag) . '" />';
            }
        }
        
        return implode($str, ' ');
    }


    /**
     * Signal sent by the form after form generation
     * Add a field to signal to the crud controller during form submit it is a form for translation
     * @param Centurion_Signal_Abstract $signal
     * @param Centurion_Form_Model_Abstract $sender
     */
    public function postGenerateForm($signal, $sender){
        $_request = $this->getRequest();
        if($sender instanceof Translation_Traits_Form_Model_Interface
            && ('translate' == $_request->action //From btn language
                || isset($_request->_translating))){ //if post fail

            $sender->addElement('hidden', '_translating', array('value' => 'on'));
        }
    }

    /**
     * Signal sended by the form after the saving.
     * This method change the id of the object with the original id to not break the "save and continue"
     * (because the object returned is the localized object, and not the original object)
     * @param Centurion_Signal_Abstract $signal
     * @param Centurion_Form_Model_Abstract $sender
     */
    public function postSaveForm($signal, $sender){
        $_instance = $sender->getInstance();
        $_request = $this->getRequest();
        if(isset($_request->_translating) //Only on translate form
            && isset($_request->_continue) //and if the button "save and continue" was clicked
            && $_instance //and if it is a translated row
            && !empty($_instance->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD})){

            $_instance->id = $_instance->{Translation_Traits_Model_DbTable::ORIGINAL_FIELD};
        }
    }
}
