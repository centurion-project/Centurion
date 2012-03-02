<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Signal_PreInitTest extends PHPUnit_Framework_TestCase
{
    public function testAttach()
    {
        $signal = Centurion_Signal::factory('pre_init');
        $signal->connect(array($this, 'handler'));
        $signal->send('handler');
    }
    
    public function handler($signal, $arg1)
    {
        $this->assertEquals($arg1, __FUNCTION__);
    }
    
    public function handlerSender($arg1, $arg2)
    {
        
    }
}
