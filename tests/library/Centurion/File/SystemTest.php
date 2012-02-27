<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

class Centurion_File_SystemTest extends PHPUnit_Framework_TestCase
{
    public function testToto()
    {
        
    }
    
    public function testRmdir()
    {
	return;
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR;
        mkdir($dir . 'centurion_test');
        $dir .= 'centurion_test' . DIRECTORY_SEPARATOR;
        
        $initialDir = $dir;
        
        for ($i = 0 ; $i < 5 ; $i++) {
            $fp = fopen($dir . sha1(uniqid()), 'w+');
            fclose($fp);
            
            $dirName = sha1(uniqid());
            $dir .= $dirName . DIRECTORY_SEPARATOR;
            mkdir($dir);
        }
        //TODO: finish this test
	//$this->assertTrue(false, 'false');
        
    }
}
