<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_Signal_PreInitTest extends PHPUnit_Framework_TestCase
{
    protected $i = 0;

    public function testAttach()
    {
        $signal = Centurion_Signal::factory('pre_init');
        $signal->connect(array($this, 'handler'));
        $signal->send('handler');
    }

    public function testClean()
    {
        $signal = Centurion_Signal::factory('pre_init');
        $signal->connect(array($this, 'shouldNotBeCalled'));

        $signal->clean();
        $signal->send('test');
    }

    public function shouldNotBeCalled()
    {
        $this->fail('This function should not be called');
    }

    /**
     *
     */
    public function testDoOnlyOneCall()
    {
        $signal = Centurion_Signal::factory('postInit');
        $signal->connect(array($this, 'increment'));
        $signal->connect(array($this, 'increment'));
        $signal->send('increment');

        $this->assertEquals(2, $this->i);
        $signal->clean();

        $signal->send('increment');
        $this->assertEquals(2, $this->i);

        $signal->connectOnce(array($this, 'increment'));
        $signal->connectOnce(array($this, 'increment'));
        $signal->send('increment');

        $this->assertEquals(3, $this->i);
    }
    
    public function handler($signal, $arg1)
    {
        $this->assertEquals($arg1, __FUNCTION__);
    }

    public function increment()
    {
        $this->i++;
    }
}
