<?php
class Translation_Traits_Common
{

    const DEFAULT_LOCALE_KEY = 'resources.translate.locale';
    const GET_DEFAULT_CONFIG_KEY = 'translation.notExistsGetDefault';
    const NOT_EXISTS_GET_DEFAULT = false;

    public static function getDefaultLanguage()
    {
        return Centurion_Db::getSingleton('translation/language')->fetchRow(array('locale = ?' => Centurion_Config_Manager::get(self::DEFAULT_LOCALE_KEY)));
    }

}