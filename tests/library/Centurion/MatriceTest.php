<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

class Centurion_MatriceTest extends PHPUnit_Framework_TestCase
{
    public function testConnection()
    {
        $matrice = new Centurion_Matrice();
        
        $matrice->connect(1, 2);
        
        $this->assertTrue($matrice->isConnected(1, 2));
        $this->assertFalse($matrice->isConnected(2, 1));
        
        $matrice->removeConnection(1, 2);
        $this->assertFalse($matrice->isConnected(1, 2));
        $this->assertFalse($matrice->isConnected(2, 1));
        
        $matrice->connect(1, 2, true);
        
        $this->assertTrue($matrice->isConnected(1, 2));
        $this->assertTrue($matrice->isConnected(2, 1));
        
        $matrice->removeConnection(1, 2, true);
        $this->assertFalse($matrice->isConnected(1, 2));
        $this->assertFalse($matrice->isConnected(2, 1));
        
        
    }
    
    public function testPath()
    {
        $matrice = new Centurion_Matrice();
        
        $matrice->connect(1, 2);
        $matrice->connect(1, 3);
        $matrice->connect(1, 4);

        $matrice->connect(3, 5);
        $matrice->connect(2, 5);

        $matrice->connect(5, 6);


        $this->assertEquals(array(array(1, 1)), $matrice->findPath(1, 1));

        $result = array();
        $result[] = array(1, 2, 5, 6);

        $this->assertEquals($result, $matrice->findPath(1, 6, 3, false));

        $result[] = array(1, 3, 5, 6);

        $this->assertEquals($result, $matrice->findPath(1, 6));
    }
    
    /*
    public function testPuissance()
    {
        //It's not a real test unit, but a function to test how much time it will do to generate a matrice and find path in it
        $this->markTestSkipped();

        $nbNode = 2000;
        $nbLinkByNode = 50;
        
        $nbTest = 5;
        
        $matrice = new Centurion_Matrice();
        
        
        $startTime = microtime(true);
        
        for ($i = 0 ; $i < $nbNode ; $i ++) {
            for ($j = 0 ; $j < $nbLinkByNode ; $j++) {
                $matrice->connect(mt_rand(0, $i), mt_rand(0, $i), mt_rand(0, 1));
            }
        }
        
        echo 'Time to generate the matrice: ' . "\n";
        var_dump(microtime(true)-$startTime);
        
        for ($i = 0 ; $i < $nbTest ; $i ++) {
            $time = microtime(true);
            $a = mt_rand(0, $nbNode);
            $b = mt_rand(0, $nbNode);
            $paths = $matrice->findPath($a, $b);
            
            echo "Path from $a to $b \n";
            
            echo "\n Time to find path from ";
            var_dump(microtime(true)-$time);
            echo "\n";
            var_dump($paths);
        }
        
    }
    */
}
