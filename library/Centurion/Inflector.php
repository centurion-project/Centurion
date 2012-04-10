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
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Inflector for Zend_Db, inspired by Doctrine_Inflector.
 *
 * This classe uses static methods for inflecting text.
 *
 * @category    Centurion
 * @copyright   Copyright (c) 2008-2011 Octave & Octave (http://www.octaveoctave.com)
 * @license     http://centurion-project.org/license/new-bsd     New BSD License
 * @author      Florent Messa <florent.messa@gmail.com>
 */
class Centurion_Inflector
{
    protected static $_characterMap = array(
                        array(
                            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'AE', 'Ç' =>
                            'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I',
                            'Ï' => 'I', 'Ð' => 'D', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' =>
                            'O', 'Ő' => 'O', 'Ø' => 'O', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ű' => 'U',
                            'Ý' => 'Y', 'Þ' => 'TH', 'ß' => 'ss', 'à' =>'a', 'á' =>'a', 'â' => 'a', 'ã' => 'a', 'ä' =>
                            'a', 'å' => 'a', 'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
                            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'd', 'ñ' => 'n', 'ò' => 'o', 'ó' =>
                            'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ő' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
                            'û' => 'u', 'ü' => 'u', 'ű' => 'u', 'ý' => 'y', 'þ' => 'th', 'ÿ' => 'y'
                        ),
                        array(
                            '©' =>'(c)'
                        ),
                        array(
                            'α' =>'a', 'β' =>'b', 'γ' =>'g', 'δ' =>'d', 'ε' =>'e', 'ζ' =>'z', 'η' =>'h', 'θ' =>'8',
                            'ι' =>'i', 'κ' =>'k', 'λ' =>'l', 'μ' =>'m', 'ν' =>'n', 'ξ' =>'3', 'ο' =>'o', 'π' =>'p',
                            'ρ' =>'r', 'σ' =>'s', 'τ' =>'t', 'υ' =>'y', 'φ' =>'f', 'χ' =>'x', 'ψ' =>'ps', 'ω' =>'w',
                            'ά' =>'a', 'έ' =>'e', 'ί' =>'i', 'ό' =>'o', 'ύ' =>'y', 'ή' =>'h', 'ώ' =>'w', 'ς' =>'s',
                            'ϊ' =>'i', 'ΰ' =>'y', 'ϋ' =>'y', 'ΐ' =>'i',
                            'Α' =>'A', 'Β' =>'B', 'Γ' =>'G', 'Δ' =>'D', 'Ε' =>'E', 'Ζ' =>'Z', 'Η' =>'H', 'Θ' =>'8',
                            'Ι' =>'I', 'Κ' =>'K', 'Λ' =>'L', 'Μ' =>'M', 'Ν' =>'N', 'Ξ' =>'3', 'Ο' =>'O', 'Π' =>'P',
                            'Ρ' =>'R', 'Σ' =>'S', 'Τ' =>'T', 'Υ' =>'Y', 'Φ' =>'F', 'Χ' =>'X', 'Ψ' =>'PS', 'Ω' =>'W',
                            'Ά' =>'A', 'Έ' =>'E', 'Ί' =>'I', 'Ό' =>'O', 'Ύ' =>'Y', 'Ή' =>'H', 'Ώ' =>'W', 'Ϊ' =>'I',
                            'Ϋ' =>'Y'
                        ),
                        array(
                            'ş' =>'s', 'Ş' =>'S', 'ı' =>'i', 'İ' =>'I', 'ç' =>'c', 'Ç' =>'C', 'ü' =>'u', 'Ü' =>'U',
                            'ö' =>'o', 'Ö' =>'O', 'ğ' =>'g', 'Ğ' =>'G'
                        ),
                        array(
                            'а' =>'a', 'б' =>'b', 'в' =>'v', 'г' =>'g', 'д' =>'d', 'е' =>'e', 'ё' =>'yo', 'ж' =>'zh',
                            'з' =>'z', 'и' =>'i', 'й' =>'j', 'к' =>'k', 'л' =>'l', 'м' =>'m', 'н' =>'n', 'о' =>'o',
                            'п' =>'p', 'р' =>'r', 'с' =>'s', 'т' =>'t', 'у' =>'u', 'ф' =>'f', 'х' =>'h', 'ц' =>'c',
                            'ч' =>'ch', 'ш' =>'sh', 'щ' =>'sh', 'ъ' =>'', 'ы' =>'y', 'ь' =>'', 'э' =>'e', 'ю' =>'yu',
                            'я' =>'ya',
                            'А' =>'A', 'Б' =>'B', 'В' =>'V', 'Г' =>'G', 'Д' =>'D', 'Е' =>'E', 'Ё' =>'Yo', 'Ж' =>'Zh',
                            'З' =>'Z', 'И' =>'I', 'Й' =>'J', 'К' =>'K', 'Л' =>'L', 'М' =>'M', 'Н' =>'N', 'О' =>'O',
                            'П' =>'P', 'Р' =>'R', 'С' =>'S', 'Т' =>'T', 'У' =>'U', 'Ф' =>'F', 'Х' =>'H', 'Ц' =>'C',
                            'Ч' =>'Ch', 'Ш' =>'Sh', 'Щ' =>'Sh', 'Ъ' =>'', 'Ы' =>'Y', 'Ь' =>'', 'Э' =>'E', 'Ю' =>'Yu',
                            'Я' =>'Ya'
                        ),
                        array(
                            'Є' =>'Ye', 'І' =>'I', 'Ї' =>'Yi', 'Ґ' =>'G', 'є' =>'ye', 'і' =>'i', 'ї' =>'yi', 'ґ' =>'g'
                        ),
                        array(
                            'č' =>'c', 'ď' =>'d', 'ě' =>'e', 'ň' => 'n', 'ř' =>'r', 'š' =>'s', 'ť' =>'t', 'ů' =>'u',
                            'ž' =>'z', 'Č' =>'C', 'Ď' =>'D', 'Ě' =>'E', 'Ň' => 'N', 'Ř' =>'R', 'Š' =>'S', 'Ť' =>'T',
                            'Ů' =>'U', 'Ž' =>'Z'
                        ),

                        array(
                            'ą' =>'a', 'ć' =>'c', 'ę' =>'e', 'ł' =>'l', 'ń' =>'n', 'ó' =>'o', 'ś' =>'s', 'ź' =>'z',
                            'ż' =>'z', 'Ą' =>'A', 'Ć' =>'C', 'Ę' =>'e', 'Ł' =>'L', 'Ń' =>'N', 'Ó' =>'o', 'Ś' =>'S',
                            'Ź' =>'Z', 'Ż' =>'Z'
                        ),

                        array(
                            'ā' =>'a', 'č' =>'c', 'ē' =>'e', 'ģ' =>'g', 'ī' =>'i', 'ķ' =>'k', 'ļ' =>'l', 'ņ' =>'n',
                            'š' =>'s', 'ū' =>'u', 'ž' =>'z', 'Ā' =>'A', 'Č' =>'C', 'Ē' =>'E', 'Ģ' =>'G', 'Ī' =>'i',
                            'Ķ' =>'k', 'Ļ' =>'L', 'Ņ' =>'N', 'Š' =>'S', 'Ū' =>'u', 'Ž' =>'Z'
                        )
                    );


    /**
     * Convert word in to the format for a Zend_Db table name. Converts 'ModelName' to 'model_name'
     *
     * @param  string $word  Word to tableize
     * @return string $word  Tableized word
     */
    public static function tableize($word, $separator = '_')
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', $separator . '$1', $word));
    }

    /**
     * Convert a word in to the format for a Zend_Db class name. Converts 'table_name' to 'TableName'
     *
     * @param string  $word  Word to classify
     * @return string $word  Classified word
     */
    public static function classify($word)
    {
        $inflector = new Zend_Filter_Word_UnderscoreToCamelCase();

        return $inflector->filter($word);
    }

    public static function modelize($word, $separator = '_')
    {
        return self::classify(substr($word, strpos($word, $separator) + 1));
    }

    public static function nonLatinCharacter($text)
    {
        foreach (self::$_characterMap as $characters) {
            $text = str_replace(array_keys($characters), array_values($characters), $text);
        }

        return $text;
    }

    public static function slugify($text)
    {
        $original = $text;

        $text = self::nonLatinCharacter($text);
        $text = self::urlize($text);

        if ($text == null) {
            return md5($original);
        }

        return $text;
    }

    /**
     * Check if a string has utf7 characters in it
     *
     * By bmorel at ssi dot fr
     *
     * @param  string $string
     * @return boolean $bool
     */
    public static function seemsUtf8($string)
    {
        for ($i = 0; $i < strlen($string); $i++) {
            if (ord($string[$i]) < 0x80) continue; # 0bbbbbbb
            elseif ((ord($string[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
            elseif ((ord($string[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
            elseif ((ord($string[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
            elseif ((ord($string[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
            elseif ((ord($string[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; # Does not match any model
            for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                if ((++$i == strlen($string)) || ((ord($string[$i]) & 0xC0) != 0x80))
                return false;
            }
        }

        return true;
    }

    /**
     * Remove any illegal characters, accents, etc.
     *
     * @param  string $string  String to unaccent
     * @return string $string  Unaccented string
     */
    public static function unaccent($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string)) {
            return $string;
        }

        if (self::seemsUtf8($string)) {
            $chars = array(
            // Decompositions for Latin-1 Supplement
            chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
            chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
            chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
            chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
            chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
            chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
            chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
            chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
            chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
            chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
            chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
            chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
            chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
            chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
            chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
            chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
            chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
            chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
            chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
            chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
            chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
            chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
            chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
            chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
            chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
            chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
            chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
            chr(195).chr(191) => 'y',
            // Decompositions for Latin Extended-A
            chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
            chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
            chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
            chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
            chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
            chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
            chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
            chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
            chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
            chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
            chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
            chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
            chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
            chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
            chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
            chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
            chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
            chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
            chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
            chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
            chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
            chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
            chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
            chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
            chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
            chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
            chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
            chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
            chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
            chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
            chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
            chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
            chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
            chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
            chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
            chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
            chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
            chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
            chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
            chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
            chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
            chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
            chr(197).chr(148) => 'R', chr(197).chr(149) => 'r',
            chr(197).chr(150) => 'R', chr(197).chr(151) => 'r',
            chr(197).chr(152) => 'R', chr(197).chr(153) => 'r',
            chr(197).chr(154) => 'S', chr(197).chr(155) => 's',
            chr(197).chr(156) => 'S', chr(197).chr(157) => 's',
            chr(197).chr(158) => 'S', chr(197).chr(159) => 's',
            chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
            chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
            chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
            chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
            chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
            chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
            chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
            chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
            chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
            chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
            chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
            chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
            chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
            chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
            chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
            chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
            // Euro Sign
            chr(226).chr(130).chr(172) => 'E',
            // GBP (Pound) Sign
            chr(194).chr(163) => '',
            'Ã„' => 'Ae', 'Ã¤' => 'ae', 'Ãœ' => 'Ue', 'Ã¼' => 'ue',
            'Ã–' => 'Oe', 'Ã¶' => 'oe', 'ÃŸ' => 'ss');

            $string = strtr($string, $chars);
        } else {
            // Assume ISO-8859-1 if not UTF-8
            $chars['in'] = chr(128).chr(131).chr(138).chr(142).chr(154).chr(158)
                .chr(159).chr(162).chr(165).chr(181).chr(192).chr(193).chr(194)
                .chr(195).chr(196).chr(197).chr(199).chr(200).chr(201).chr(202)
                .chr(203).chr(204).chr(205).chr(206).chr(207).chr(209).chr(210)
                .chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218)
                .chr(219).chr(220).chr(221).chr(224).chr(225).chr(226).chr(227)
                .chr(228).chr(229).chr(231).chr(232).chr(233).chr(234).chr(235)
                .chr(236).chr(237).chr(238).chr(239).chr(241).chr(242).chr(243)
                .chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251)
                .chr(252).chr(253).chr(255);

            $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

            $string = strtr($string, $chars['in'], $chars['out']);
            $doubleChars['in'] = array(chr(140), chr(156), chr(198), chr(208), chr(222), chr(223), chr(230), chr(240), chr(254));
            $doubleChars['out'] = array('OE', 'oe', 'AE', 'DH', 'TH', 'ss', 'ae', 'dh', 'th');
            $string = str_replace($doubleChars['in'], $doubleChars['out'], $string);
        }

        return $string;
    }

    /**
     * Convert any passed string to a url friendly string. Converts 'My first blog post' to 'my-first-blog-post'
     *
     * @param  string $text  Text to urlize
     * @return string $text  Urlized text
     */
    public static function urlize($text)
    {
        // Remove all non url friendly characters with the unaccent function
        $text = self::unaccent($text);

        if (function_exists('mb_strtolower')) {
            $text = mb_strtolower($text);
        } else {
            $text = strtolower($text);
        }

        // Remove all none word characters
        $text = preg_replace('/\W/', ' ', $text);

        // More stripping. Replace spaces with dashes
        $text = preg_replace('/[^A-Z^a-z^0-9^\/]+/', '-',
                           preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
                           preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
                           preg_replace('/::/', '/', $text))));

        return trim($text, '-');
    }

    /**
     * Pluralize a word, for example "category" will be "categories", etc.
     *
     * @param string $word
     * @return string The pluralized word
     */
    public static function pluralize($word)
    {
        if (substr($word, -1) == 'y') {
            return sprintf('%s%s', substr($word, 0, -1), 'ies');
        }

        return sprintf('%s%s', $word, 's');
    }

    /**
     * Retrieve the extension of a filename.
     *
     * @param string $filename  The filename
     * @param string $withDot   If true, the return value will be with dot
     * @return string The extension of the specified filename
     */
    public static function extension($filename, $withDot = true)
    {
        if (preg_match('`.*(\.[a-z]*)(\?.*)?(#.*)?`i', $filename, $matches)) {
            $ext = $matches[1];
            if ($withDot === false) {
                return substr($ext, 1);
            }
            return $ext;
        }

        return false;
    }

    /**
     * Camelize a text.
     *
     * @param string $text
     * @return string The camelized text
     * @TODO: this function is never used, and seems not be usefull
     */
    public static function camelize($text)
    {
      return self::pregtr($text, array('#/(.?)#e' => "'::'.strtoupper('\\1')", '/(^|_|-)+(.)/e' => "strtoupper('\\2')"));
    }


    public static function sortArrayByArray(array $toSort, array $sortByValuesAsKeys)
    {
        $commonKeysInOrder = array_intersect_key(array_flip($sortByValuesAsKeys), $toSort);
        $commonKeysWithValue = array_intersect_key($toSort, $commonKeysInOrder);
        $sorted = array_merge($commonKeysInOrder, $commonKeysWithValue);

        return $sorted;
    }

    /**
     * @see http://www.php.net/manual/fr/function.substr.php#80247
     */
    public static function cuttext($value, $length, $separator = '...')
    {
        if (strlen($value) < 1)
            return $value;

        if (is_array($value))
            list($string, $matchTo) = $value;
        else {
            $string = $value;
            $matchTo = $value{0};
        }

        $matchStart = stristr($string, $matchTo);
        $matchCompute = strlen($string) - strlen($matchStart);

        if (strlen($string) > $length) {
            if ($matchCompute < ($length - strlen($matchTo))) {
                $preString = substr($string, 0, $length);
                $posEnd = strrpos($preString, ' ');

                if (false === $posEnd)
                    $string = $preString . $separator;
                else
                    $string = substr($preString, 0, $posEnd) . $separator;
            } else if ($matchCompute > (strlen($string) - ($length - strlen($matchTo)))) {
                $preString = substr($string, (strlen($string) - ($length - strlen($matchTo))));
                $posStart = strpos($preString, ' ');
                $string = $separator.substr($preString, $posStart);

                if (false === $posStart)
                    $string = $separator . $preString;
                else
                    $string = $separator . substr($preString, $posStart);
            } else {
                $preString = substr($string, ($matchCompute - round(($length / 3))), $length);
                $posStart = strpos($preString, ' ');
                $posEnd = strrpos($preString, ' ');
                $string = $separator . substr($preString, $posStart, $posEnd) . $separator;

                if (false === $posStart && false === $posEnd)
                    $string = $separator . $preString . $separator;
                else
                    $string = $separator . substr($preString, $posStart, $posEnd) . $separator;
            }

            $matchStart = stristr($string, $matchTo);
            $matchCompute = strlen($string) - strlen($matchStart);
        }

        return $string;
    }

    /**
     * Returns subject replaced with regular expression matchs
     *
     * @param mixed $search        subject to search
     * @param array $replacePairs  array of search => replace pairs
     */
    public static function pregtr($search, $replacePairs)
    {
      return preg_replace(array_keys($replacePairs), array_values($replacePairs), $search);
    }

    public static function uniq($name)
    {
        return sha1($name) . time() . md5(mt_rand(0, 999999));
    }

    /**
     * Retrieve the id of an object.
     *
     * @param mixed $target
     * @return string
     */
    public static function id($target)
    {
        if (is_string($target))
            return $target;
        return sprintf("%s_%s", get_class($target), spl_object_hash($target));
    }

    public static function entityDecode($str)
    {
        if (null === $str)
            return '';

        $str = strip_tags($str);
        $str = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);
        $str = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $str);
        $str = htmlspecialchars_decode($str);
        $str = html_entity_decode($str, ENT_COMPAT, 'UTF-8');

        return $str;
    }

    public static function urlEncode($str)
    {
        $str = base64_encode($str);
        return strtr($str, array('+' => '-', '/' => '_', '=' => ''));
    }

    public static function urlDecode($str)
    {
        $str = strtr($str, array('-' => '+', '_' => '/'));
        return base64_decode($str);
    }

    public static function md5UrlEncode($str)
    {
        return self::UrlEncode(md5($str));
    }

    public static function sha1UrlEncode($str)
    {
        return self::UrlEncode(sha1($str));
    }

    public static function crypt($str)
    {
        return base64_encode($str);
    }

    public static function uncrypt($str)
    {
        return base64_decode($str);
    }

    public static function roundUpTo($number, $increments) {
        return (ceil($number / $increments) * $increments);
    }

    public static function roundTo($number, $increments) {
        return (round($number / $increments) * $increments);
    }

    public static function roundDowTo($number, $increments) {
        return (floor($number / $increments) * $increments);
    }
}
