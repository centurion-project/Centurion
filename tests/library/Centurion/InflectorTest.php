<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * @covers Centurion_Inflector
 */
class Centurion_InflectorTest extends PHPUnit_Framework_TestCase
{
    public function testClassifyAndTableizeFunction()
    {
        $start = 'auth_user';
        $middle = 'AuthUser';

        $this->assertEquals($middle, Centurion_Inflector::classify($start));

        $this->assertEquals($start, Centurion_Inflector::tableize($middle));
    }

    public function testModelizeFunction()
    {
        $this->markTestIncomplete();
    }

    public function testPluralizeFunction()
    {
        $this->markTestIncomplete();
    }

    public function dataForTestExtensionFunction()
    {
        return array(
            array('index.php', false, 'php'),
            array('index.php', true, '.php'),
            array('index', false, false),
        );
    }

    /**
     * @dataProvider dataForTestExtensionFunction
     */
    public function testExtensionFunction($fileName, $withDot, $expectedResult)
    {
        $this->assertEquals($expectedResult, Centurion_Inflector::extension($fileName, $withDot));
    }


    public function dataForTestUrlEncodeAndDecode()
    {
        return array(
            array('This is a test'),
            array('Some people use char like this : éè`\'"§%ù$£¤µ^`'),
        );
    }

    /**
     * @dataProvider dataForTestUrlEncodeAndDecode
     */
    public function testUrlEncodeAndDecode($str)
    {
        $encoded = Centurion_Inflector::urlEncode($str);

        $scheme = Zend_Uri::factory();

        $scheme->setQuery($encoded);

        $this->assertEquals($str, Centurion_Inflector::urlDecode($encoded));
    }

    public function dataForTestFunctionRoundUpTo()
    {
        return array(
            array(1,   1,   1),
            array(1,   2,   2),
            array(0.4, 0.5, 0.5),
            array(0.6, 0.5, 1),
            array(1.6, 1.5, 3),
        );
    }

    /**
     * @dataProvider dataForTestFunctionRoundUpTo
     */
    public function testFunctionRoundUpTo($number, $increment, $expectedResult)
    {
        $this->assertEquals($expectedResult, Centurion_Inflector::roundUpTo($number, $increment));
    }

    public function dataForTestFunctionRoundDownTo()
    {
        return array(
            array(1,   1,   1),
            array(1,   2,   0),
            array(0.4, 0.5, 0),
            array(0.6, 0.5, 0.5),
            array(1.6, 1.5, 1.5),
        );
    }

    /**
     * @dataProvider dataForTestFunctionRoundDownTo
     */
    public function testFunctionRoundDownTo($number, $increment, $expectedResult)
    {
        $this->assertEquals($expectedResult, Centurion_Inflector::roundDowTo($number, $increment));
    }

    public function dataForTestFunctionRoundTo()
    {
        return array(
            array(1,   1,   1),
            array(1,   2,   2),
            array(0.4, 0.5, 0.5),
            array(0.6, 0.5, 0.5),
            array(1.6, 1.5, 1.5),
        );
    }

    /**
     * @dataProvider dataForTestFunctionRoundTo
     */
    public function testFunctionRound4nTo($number, $increment, $expectedResult)
    {
        $this->assertEquals($expectedResult, Centurion_Inflector::roundTo($number, $increment));
    }

}
