<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Locale_FormatTest extends PHPUnit_Framework_TestCase
{
    protected $_tabDatePicker = array(
                array(null, null),
                array('dd/MM/yy', 'dd/MMMM/yyyy'),
                array('DD', 'EEEE'),
            );
    
    protected $_tabPhp2Iso = array(
            array(null, null),
            array('d/m/y', 'dd/MM/yy'),
            );

    public function getDataTabDatePicker()
    {
        return $this->_tabDatePicker;
    }

    public function getDataTabPhp2Iso()
    {
        return $this->_tabPhp2Iso;
    }

    /**
     * @dataProvider getDataTabDatePicker
     */
    public function testConvertDatepickerToIsoFormat($key, $val)
    {
        $result = Centurion_Locale_Format::convertDatepickerToIsoFormat($key);
        $this->assertEquals($val, $result);
    }

    /**
     * @dataProvider getDataTabDatePicker
     */
    public function testConvertIsoToDatepickerFormat($key, $val)
    {
        $result = Centurion_Locale_Format::convertIsoToDatepickerFormat($val);
        $this->assertEquals($key, $result);
    }

    /**
     * @dataProvider getDataTabPhp2Iso
     */
    public function testConvertPhpToIsoFormat($key, $val)
    {
        $result = Centurion_Locale_Format::convertPhpToIsoFormat($key);
        $this->assertEquals($val, $result);
    }

    /**
     * @dataProvider getDataTabPhp2Iso
     */
    public function testConvertIsoToPhpFormat($key, $val)
    {
        $result = Centurion_Locale_Format::convertIsoToPhpFormat($val);
        $this->assertEquals($key, $result);
    }
}
