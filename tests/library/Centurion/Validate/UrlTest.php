<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Signal_UrlTest extends PHPUnit_Framework_TestCase
{


    public function getDataForTestIsValidFunction()
    {
        return array(
            array('http://www.centurion-project.org', true),
            array('http://www.octaveoctave.org', true),
            array('htp://www.centurion-project.org', false), //Without a good http
            array('http://www.centur%ion-project.prg', false), //Without a good domain extension
        );
    }

    /**
     * @param $url
     * @param $trueOrFalse
     * @dataProvider getDataForTestIsValidFunction
     */
    public function testIsValidFunction($url, $trueOrFalse)
    {
        $validator = new Centurion_Validate_Url();

        if ($trueOrFalse) {
            $this->assertTrue($validator->isValid($url));
        } else {
            $this->assertFalse($validator->isValid($url));
        }
    }
}
