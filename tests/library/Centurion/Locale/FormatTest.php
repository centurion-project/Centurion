<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Locale_FormatTest extends PHPUnit_Framework_TestCase
{
    protected $tabDatePicker = array(
            'dd/MM/yy' => 'dd/MMMM/yyyy',
            'DD' => 'EEEE',
            );
    
    protected $tabPhp2Iso = array(
            'd/m/y' => 'dd/MM/yy',
            );
    
    public function testConvertDatepickerToIsoFormat()
    {
        foreach ($this->tabDatePicker as $key => $val) {
            $result = Centurion_Locale_Format::convertDatepickerToIsoFormat($key);
            $this->assertEquals($val, $result);
        }
        
    }
    
    public function testConvertIsoToDatepickerFormat()
    {
        foreach ($this->tabDatePicker as $key => $val) {
            $result = Centurion_Locale_Format::convertIsoToDatepickerFormat($val);
            $this->assertEquals($key, $result);
        }
    }
    
    public function testConvertPhpToIsoFormat()
    {
        foreach ($this->tabPhp2Iso as $key => $val) {
            $result = Centurion_Locale_Format::convertPhpToIsoFormat($key);
            $this->assertEquals($val, $result);
        }
    }
    
    public function testConvertIsoToPhpFormat()
    {
        foreach ($this->tabPhp2Iso as $key => $val) {
            $result = Centurion_Locale_Format::convertIsoToPhpFormat($val);
            $this->assertEquals($key, $result);
        }
    }
}