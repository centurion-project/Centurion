<?php
class Translation_Controller_Action_Helper_ManageLanguageParam extends Zend_Controller_Action_Helper_Abstract
{
    public function init()
    {
        $controller = $this->getActionController();
        if (!($requestedLocale = $controller->getRequest()->getParam('language', false))) {
            $local = new Zend_Locale();
            $requestedLocale = $local->getLanguage();
        }

        if (is_array($requestedLocale)) {
            $requestedLocale = current($requestedLocale);
        }
        
        $requestedLocale = strtolower($requestedLocale);

        try {
            Centurion_Db::getSingleton('translation/language')->get(array('locale' => $requestedLocale));
        } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
            $requestedLocale = Translation_Traits_Common::getDefaultLanguage();
            $requestedLocale = $requestedLocale->locale;
        }

        Zend_Registry::get('Zend_Translate')->setLocale($requestedLocale);
        Zend_Locale::setDefault($requestedLocale);
        Zend_Registry::set('Zend_Locale', $requestedLocale);
        $options = Centurion_Db_Table_Abstract::getDefaultFrontendOptions();

        if (!isset($options['cache_id_prefix']))
            $options['cache_id_prefix'] = '';

        $options['cache_id_prefix'] = $requestedLocale . '_' . $options['cache_id_prefix'];

        Centurion_Db_Table_Abstract::setDefaultFrontendOptions($options);
        $this->getActionController()->getFrontController()->getParam('bootstrap')->getResource('cachemanager')->addIdPrefix($requestedLocale . '_');
        
        if (Centurion_Config_Manager::get('translation.global_param')) {
            $this->getFrontController()->getRouter()->setGlobalParam('language', $requestedLocale);
        }

        //Restore the cache in the adapter (now that the language is found, we can retrieve it ;) )
        if (Zend_Registry::isRegistered('Zend_Translate')){
            $translate = Zend_Registry::get('Zend_Translate');

            if ($translate instanceof Translation_Model_Translate_Adapter_Array) {
                $translate->restoreCache();
            } else if ($translate instanceof Zend_Translate) {
                $adapter = $translate->getAdapter();
                if ($adapter instanceof Translation_Model_Translate_Adapter_Array) {
                    $adapter->restoreCache();
                }
            }
        }
    }
}
