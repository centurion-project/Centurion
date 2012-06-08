<?php
class Translation_Model_Manager
{
    protected static function _getTranslation($languageId)
    {
        $uidTable = Centurion_Db::getSingleton('translation/uid');
        
        $select = $uidTable->select(false)
                ->reset(Zend_Db_Select::COLUMNS)
                ->from(array('a' => 'translation_uid', 'a.uid'))
                ->setIntegrityCheck(false)
                ->joinLeft(array('b' => 'translation_translation'), 'b.uid_id = a.id and b.language_id = '.$languageId, 'b.translation');
            
         return $uidTable->fetchAll($select);
    }
    
    
    public static function generate()
    {
        self::generatePhp();
    }
    
    public static function generatePhp()
    {
        $languageRowSet = Centurion_Db::getSingleton('translation/language')->all();
        
        $translationTable = Centurion_Db::getSingleton('translation/translation');
        
        
        foreach ($languageRowSet as $languageRow) {
            
            $str = '<?php' . PHP_EOL . ' return array(' . PHP_EOL;
            
            foreach (self::_getTranslation($languageRow->id) as $translationRow) { 
                $str .= '\'' . addcslashes($translationRow->uid, '\\\'') . '\' => \'' . addcslashes((null !== $translationRow->translation)?$translationRow->translation:$translationRow->uid, '\\\'') . '\',' . PHP_EOL;
            }
            
            $str .= ');' . PHP_EOL;
            
            $filename = Centurion_Config_Manager::get('resources.translate.data')
                       . DIRECTORY_SEPARATOR
                       . $languageRow->locale
                       . DIRECTORY_SEPARATOR
                       . $languageRow->locale
                       . '.php';
            
            if (!file_exists(Centurion_Config_Manager::get('resources.translate.data') . DIRECTORY_SEPARATOR . $languageRow->locale)) {
                mkdir(Centurion_Config_Manager::get('resources.translate.data') . DIRECTORY_SEPARATOR . $languageRow->locale, 0755, true);
            }
            //file_put_contents($filename, $str, LOCK_EX);
            
            $fp = fopen($filename,'w');
            flock($fp, LOCK_EX);
            # Now UTF-8 - Add byte order mark
            fwrite($fp, pack('CCC',0xef,0xbb,0xbf));
            fwrite($fp, $str);
            fclose($fp);
        }
    }
    
    public static function generateTmx()
    {
        $languageRowSet = Centurion_Db::getSingleton('translation/language')->all();
        
        $translationTable = Centurion_Db::getSingleton('translation/translation');
        $uidTable = Centurion_Db::getSingleton('translation/uid');
        
        foreach ($languageRowSet as $languageRow) {
            $implementation = new DOMImplementation();
            $dtd = $implementation->createDocumentType('tmx', '', 'http://www.lisa.org/fileadmin/standards/tmx14.dtd.txt');
            $dom = $implementation->createDocument('', '', $dtd);
            $dom->encoding = 'UTF-8';
            $dom->version  = '1.0';
            
            $dom->formatOutput = true;
            $root = $dom->createElement('tmx');
            $root->setAttribute('version', '1.4');
            
            $header = $dom->createElement('header');
            $header->setAttribute('creationtool', 'Centurion');
            $header->setAttribute('creationtoolversion', '1.0.0');
            $header->setAttribute('datatype', 'winres');
            $header->setAttribute('segtype', 'sentence');
            $header->setAttribute('adminlang', 'en-us');
            $header->setAttribute('srclang', 'en-us');
            $header->setAttribute('o-tmf', 'abc');
            $root->appendChild($header);
            
            $body = $dom->createElement('body');
            
            $select = $uidTable->select(false)
                ->reset(Zend_Db_Select::COLUMNS)
                ->from(array('a' => 'translation_uid', 'a.uid'))
                ->setIntegrityCheck(false)
                ->join(array('b' => 'translation_translation'), 'b.uid_id = a.id and b.language_id = '.$languageRow->id, 'b.translation');
            
            $translationRowset = $uidTable->fetchAll($select);
                        
            foreach ($translationRowset as $translationRow) { 
                
                $tu = $dom->createElement('tu');
                $tu->setAttribute('tuid', $translationRow->uid);
                $tuv = $dom->createElement('tuv');
                $tuv->setAttribute('xml:lang', $languageRow->locale);
                $seg = $dom->createElement('seg');
                $seg->appendChild($dom->createCDATASection((null !== $translationRow->translation)?$translationRow->translation:$translationRow->uid));
                $tuv->appendChild($seg);
                $tu->appendChild($tuv);
                $body->appendChild($tu);
            }
            
            $root->appendChild($body);
            $dom->appendChild($root);
            
            $dom->save(Centurion_Config_Manager::get('resources.translate.data')
                       . DIRECTORY_SEPARATOR
                       . sprintf("%s.xml", $languageRow->locale));
        }
    }
}