<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Centurion_CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $collection = new Centurion_Collection();

        $this->assertEquals(0, $collection->count());
        $this->assertEquals(0, count($collection));

        $original = array(1, 2);
        $collection->setData($original);
        $this->assertEquals($original, $collection->getData());
        $this->assertEquals($original, $collection->toArray());

        $this->assertEquals(1, $collection->getFirst());
        $this->assertEquals(2, $collection->getLast());

        $this->assertEquals(2, $collection->pop());
        $this->assertEquals(1, $collection->pop());

        $this->assertEquals(0, $collection->count());
        $this->assertEquals(0, count($collection));

        foreach ($original as $key => $val) {
            $collection[$key] = $val;
            $this->assertEquals($val, $collection[$key]);
        }

        $collection->remove(0);
        try {
            $collection[0];
            $this->fail('Centurion_Collection should throw an exception when we try to access something that doesn\'t exist.');
        }catch (Centurion_Exception $e) {
            //It's normal to be here
        }
    }
}
