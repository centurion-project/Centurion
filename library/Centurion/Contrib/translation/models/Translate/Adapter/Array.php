<?php 

class Translation_Model_Translate_Adapter_Array extends Zend_Translate_Adapter_Array
{
    protected $_checkedWord = array();
    protected $_checkedTag = array();
    protected $_checkedWordTag = array();
    
    protected $_uidTable = null;
    protected $_tagTable = null;
    protected $_tagUidTable = null;
    protected $_languageTable = null;
    
    public function __construct($options)
    {
        $this->_uidTable = Centurion_Db::getSingleton('translation/uid');
        $this->_tagTable = Centurion_Db::getSingleton('translation/tag');
        $this->_languageTable = Centurion_Db::getSingleton('translation/language');
        $this->_tagUidTable = Centurion_Db::getSingleton('translation/tagUid');
        parent::__construct($options);
        
        if ($cached = self::getCache()->load('Translation_Model_Translate_Adapter_Array_Cache')) {
            list($this->_checkedWord, $this->_checkedTag, $this->_checkedWordTag) = $cached;
        }
    }
    
    public function __destruct()
    {
        $cached = array($this->_checkedWord, $this->_checkedTag, $this->_checkedWordTag);
        
        $tags = array();
        $tags[] = Centurion_Cache_TagManager::getTagOf($this->_uidTable);
        $tags[] = Centurion_Cache_TagManager::getTagOf($this->_tagTable);
        $tags[] = Centurion_Cache_TagManager::getTagOf($this->_tagUidTable);
        
        if ($this->_languageTable !== null)
        	$tags[] = Centurion_Cache_TagManager::getTagOf($this->_languageTable);
        
        self::getCache()->save($cached, 'Translation_Model_Translate_Adapter_Array_Cache', $tags);
    }
    
    public function getUidId($uid)
    {
        $md5 = md5($uid);
        if (!isset($this->_checkedWord[$md5])) {
            list($messageIdRow, ) = $this->_uidTable->getOrCreate(array('uid' => $uid));
            $this->_checkedWord[$md5] = $messageIdRow->id;
        }
        return $this->_checkedWord[$md5];
    }
    
    public function getTagId($tag)
    {
        $md5 = md5($tag);
        if (!isset($this->_checkedTag[$md5])) {
            list($tagRow, ) = $this->_tagTable->getOrCreate(array('tag' => $tag));
            $this->_checkedTag[$md5] = $tagRow->id;
        }
        return $this->_checkedTag[$md5];
    }
    
    public function translate($messageId, $locale = null)
    {
        if ($locale === null) {
            $locale = $this->_options['locale'];
        }

        $plural = null;
        if (is_array($messageId)) {
            if (count($messageId) > 2) {
                $number = array_pop($messageId);
                if (!is_numeric($number)) {
                    $plocale = $number;
                    $number  = array_pop($messageId);
                } else {
                    $plocale = 'en';
                }

                $plural    = $messageId;
                $messageId = $messageId[0];
            } else {
                $messageId = $messageId[0];
            }
        }
        
        $result = preg_split('`(?<!\\\)\@`', $messageId, 2, PREG_SPLIT_DELIM_CAPTURE);
        
        if (count($result) == 2) {
            list($messageId, $tags) = $result;
        }
        
        if (!Zend_Locale::isLocale($locale, true, false)) {
            if (!Zend_Locale::isLocale($locale, false, false)) {
                // language does not exist, return original string
                
                //TODO: save untranslated language
                $this->_log($messageId, $locale);
                // use rerouting when enabled
                if (!empty($this->_options['route'])) {
                    if (array_key_exists($locale, $this->_options['route']) &&
                        !array_key_exists($locale, $this->_routed)) {
                        $this->_routed[$locale] = true;
                        return $this->translate($messageId, $this->_options['route'][$locale]);
                    }
                }

                $this->_routed = array();
                if ($plural === null) {
                    return $messageId;
                }

                $rule = Zend_Translate_Plural::getPlural($number, $plocale);
                if (!isset($plural[$rule])) {
                    $rule = 0;
                }

                return $plural[$rule];
            }

            $locale = new Zend_Locale($locale);
        }

        $locale = (string) $locale;
        if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
            // return original translation
            if ($plural === null) {
                $this->_routed = array();
                return $this->_translate[$locale][$messageId];
            }

            $rule = Zend_Translate_Plural::getPlural($number, $locale);
            if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                $this->_routed = array();
                return $this->_translate[$locale][$plural[0]][$rule];
            }
        } else if (strlen($locale) != 2) {
            // faster than creating a new locale and separate the leading part
            $locale = substr($locale, 0, -strlen(strrchr($locale, '_')));

            if ((is_string($messageId) || is_int($messageId)) && isset($this->_translate[$locale][$messageId])) {
                // return regionless translation (en_US -> en)
                if ($plural === null) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$messageId];
                }

                $rule = Zend_Translate_Plural::getPlural($number, $locale);
                if (isset($this->_translate[$locale][$plural[0]][$rule])) {
                    $this->_routed = array();
                    return $this->_translate[$locale][$plural[0]][$rule];
                }
            }
        }

        //TODO: log unexistant message

        $uidId = $this->getUidId($messageId);

        if (isset($tags)) {
            $tags = explode(',', $tags);
            foreach ($tags as $tag) {
                $tag = trim($tag);
                $tagId = $this->getTagId($tag);

                $this->_tagUidTable->getOrCreate(array('uid_id' => $uidId, 'tag_id' => $tagId));
            }
        }
        
        $this->_log($messageId, $locale);
        // use rerouting when enabled
        if (!empty($this->_options['route'])) {
            if (array_key_exists($locale, $this->_options['route']) &&
                !array_key_exists($locale, $this->_routed)) {
                $this->_routed[$locale] = true;
                return $this->translate($messageId, $this->_options['route'][$locale]);
            }
        }

        $this->_routed = array();
        if ($plural === null) {
            return $messageId;
        }

        $rule = Zend_Translate_Plural::getPlural($number, $plocale);
        if (!isset($plural[$rule])) {
            $rule = 0;
        }

        return $plural[$rule];
    }
}