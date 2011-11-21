<?php

class Centurion_Locale_Format extends Zend_Locale_Format
{
    public static function convertIsoToPhpFormat($format)
    {
        if ($format === null) {
            return null;
        }
        $convert = array('d' => 'dd'  , 'D' => 'EE'  , 'j' => 'd'   , 'l' => 'EEEE', 'N' => 'eee' , 'S' => 'SS'  ,
                         'w' => 'e'   , 'z' => 'D'   , 'W' => 'ww'  , 'F' => 'MMMM', 'm' => 'MM'  , 'M' => 'MMM' ,
                         'n' => 'M'   , 't' => 'ddd' , 'L' => 'l'   , 'o' => 'YYYY', 'Y' => 'yyyy', 'y' => 'yy'  ,
                         'a' => 'a'   , 'A' => 'a'   , 'B' => 'B'   , 'g' => 'h'   , 'G' => 'H'   , 'h' => 'hh'  ,
                         'H' => 'HH'  , 'i' => 'mm'  , 's' => 'ss'  , 'e' => 'zzzz', 'I' => 'I'   , 'O' => 'Z'   ,
                         'P' => 'ZZZZ', 'T' => 'z'   , 'Z' => 'X'   , 'c' => 'yyyy-MM-ddTHH:mm:ssZZZZ',
                         'r' => 'r'   , 'U' => 'U');

        $convert = array_flip($convert);
        asort($convert);


        $value = str_replace(array_keys($convert), array_values($convert), $format);

        $values = str_split($format);
        foreach ($values as $key => $value) {
            if (isset($convert[$value]) === true) {
                $values[$key] = $convert[$value];
            }
        }

        return join($values);
    }

    // Format déjà localisé
    public static function convertIsoToDatepickerFormat($format)
    {
        if ($format === null) {
            return null;
        }

        $convert = array(
            'dd' => 'dd',
            'd'  => 'd',
            'DD' => 'EEEE',
            'D'  => 'EEE',
            'oo' => 'DD',
            'o'  => 'D',
            'mm' => 'MM',
            'm'  => 'M',
            'MM' => 'MMMMM',
            'M'  => 'MMM',
            'yy' => 'yyyy',
            'y'  => 'yy',
            '@'  => 'U'
        );

        $convert = array_flip($convert);
        arsort($convert);

        $value = str_replace(array_keys($convert), array_values($convert), $format);

        return $value;
    }

    public static function convertDatepickerToIsoFormat($format)
    {
        if ($format === null) {
            return null;
        }

        $convert = array(
            'dd' => 'dd',
            'd'  => 'd',
            'DD' => 'EEEE',
            'D'  => 'EEE',
            'oo' => 'DD',
            'o'  => 'D',
            'mm' => 'MM',
            'm'  => 'M',
            'MM' => 'MMMMM',
            'M'  => 'MMM',
            'yy' => 'yyyy',
            'y'  => 'yy',
            '@'  => 'U'
        );

        arsort($convert);

        $value = str_replace(array_keys($convert), array_values($convert), $format);

        return $value;
    }
}