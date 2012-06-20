<?php
/**
 * Centurion
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@centurion-project.org so we can send you a copy immediately.
 *
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version     $Id$
 */

/**
 * @category    Centurion
 * @package     Centurion_Contrib
 * @subpackage  Translation
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 * @author      Laurent Chenay <lc@centurion-project.org>
 */
class Translation_Model_DbTable_Language extends Centurion_Db_Table_Abstract
{
    protected $_primary = 'id';
    protected $_name = 'translation_language';
    protected $_rowClass = 'Translation_Model_DbTable_Row_Language';
    
    protected $_manyDependentTables = array(
        'translation'        =>  array(
            'refTableClass'     =>  'Translation_Model_DbTable_Uid', 
            'intersectionTable' =>  'Translation_Model_DbTable_Translation',
            'columns'   =>  array(
                'local'         =>  'uid_id',
                'foreign'       =>  'language_id'
            )
        )
    );
    
    protected $_dependentTables = array(
        'Translations'    =>  'Translation_Model_DbTable_Translation'
    );
    
    static public function getCurrentLanguageInfo()
    {
        $currentLocale = Zend_Registry::get('Zend_Translate')->getLocale();

        if (defined("PHPUNIT")) {
            //In case of test unit, we do not have access to session
            $languageRow = Centurion_Db::getSingleton('translation/language')->get(array('locale' => $currentLocale));
            return $languageRow->toArray();
        }

        $session = new Zend_Session_Namespace('translation_current');
                
        if (!isset($session->language) || $session->language['locale'] != $currentLocale) {
            try {
                $languageRow = Centurion_Db::getSingleton('translation/language')->get(array('locale' => $currentLocale));
            } catch (Centurion_Db_Table_Row_Exception_DoesNotExist $e) {
                $languageRow = Translation_Traits_Common::getDefaultLanguage();
            }

            $session->language = $languageRow->toArray();
        }
                
        return $session->language;
    }
}
