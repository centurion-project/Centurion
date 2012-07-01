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
class Translation_Model_DbTable_Row_Uid extends Centurion_Db_Table_Row_Abstract
{
    protected static $_languages = null;

    public function __construct($config)
    {
        $this->_specialGets['translations_remaining'] = 'getTranslationsRemaining';

        parent::__construct($config);
    }

    public static function getLanguages()
    {
        if (null === self::$_languages) {
            self::$_languages = Centurion_Db::getSingleton('translation/language')->all()
                                                                                  ->toArray();
        }

        return self::$_languages;
    }

    public function getTranslationFor($languageRow)
    {
        if ($languageRow !== null) {
            foreach ($this->translations as $translation) {
                if ($translation->language_id === $languageRow->id) {
                    return $translation->translation;
                }
            }
        }
        return $this->uid;
    }
    
    public function getTranslationsRemaining()
    {
        $translations = Centurion_Db::getSingleton('translation/translation')->fetchAll(sprintf('uid_id = %d', $this->id))
                                                                             ->toArray();

        $languages = self::getLanguages();
        foreach ($translations as $key => $translation) {
            foreach ($languages as $ind => &$language) {
                if ($translation['language_id'] !== $language['id']) {
                    continue;
                }

                unset($languages[$ind]);
            }
        }

        //Fix the @php#29992 bug
        unset($language);

        $locales = array();

        foreach ($languages as $key => $language) {
            array_push($locales, $language['locale']);
        }

        return sprintf("%s (%s)", count(self::getLanguages()) - count($translations),
                                  implode(', ', $locales));
    }
}
