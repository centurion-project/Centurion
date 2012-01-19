<?php

require_once '../../../TestHelper.php';

class Centurion_Locale_FormatTest extends PHPUnit_Framework_TestCase
{
    /**
     * @todo: test all case
     */
    public function testConvertDatepickerToIsoFormat()
    {
        $result = Centurion_Locale_Format::convertDatepickerToIsoFormat('dd/MM/yy');
        $this->assertEquals('dd/MMMM/yyyy', $result);
        
    }
    
    /**
     * @todo: test all case
     */
    public function testConvertIsoToDatepickerFormat()
    {
        $result = Centurion_Locale_Format::convertIsoToDatepickerFormat('dd/MMMM/yyyy');
        $this->assertEquals('dd/MM/yy', $result);
        
    }
}