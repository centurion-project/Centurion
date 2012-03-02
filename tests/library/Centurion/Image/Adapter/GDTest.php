<?php

require_once dirname(__FILE__) . '/../../../../../tests/TestHelper.php';

class Centurion_Image_Adapter_GDTest extends PHPUnit_Framework_TestCase
{
    protected $_path = null;
    
    protected $_buildPath = null;
    
    protected $_adapterClass = 'Centurion_Image_Adapter_GD';
    
    protected $_adapter = null;
    
    protected $_guinea = 'article.jpg';
    
    protected $_sizes = array(
        array(25, 25),
        array(50, 50),
        array(75, 75),
        array(100, 100),
        array(125, 125),
        array(150, 150),
        array(175, 175),
        array(200, 200),
        array(400, 400),
        array(800, 800),
    );
    
    public function setUp()
    {
        $this->_path = realpath(dirname(__FILE__) . '/_files');
        $this->_buildPath = $this->_path . '/build';
        
        if (!is_writable($this->_buildPath)) {
            throw new Centurion_Exception(sprintf('The path "%s" is not writable', $this->_buildPath));
        }
        
        $guineaPath = $this->_path . DIRECTORY_SEPARATOR . $this->_guinea;
        
        if (!file_exists($guineaPath) || !is_readable($guineaPath)) {
            throw new Centurion_Exception(sprintf('The guinea-pig "%s" is not readable', $guineaPath));
        }
    }
    
    public function testResize()
    {
        foreach ($this->_sizes as $key => $size) {
            list($width, $height) = $size;
            $this->_getAdapter()->open($this->_path . DIRECTORY_SEPARATOR . $this->_guinea)
                                ->resize($width, $height)
                                ->save($this->_buildPath
                                       . DIRECTORY_SEPARATOR
                                       . sprintf("resize_%d_%d_%s", $width, $height, $this->_guinea));
            
            $this->assertFalse($this->_getAdapter()->getThumbHeight() > $height);
            $this->assertFalse($this->_getAdapter()->getThumbWidth() > $width);
        }
    }
    
    public function testAdaptiveResize()
    {
        foreach ($this->_sizes as $key => $size) { 
            list($width, $height) = $size;
            $this->_getAdapter()->open($this->_path . DIRECTORY_SEPARATOR . $this->_guinea)
                                ->adaptiveResize($width, $height)
                                ->save($this->_buildPath
                                       . DIRECTORY_SEPARATOR
                                       . sprintf("adaptive_resize_%d_%d_%s", $width, $height, $this->_guinea));
            
            $this->assertFalse($this->_getAdapter()->getThumbHeight() === $height);
            $this->assertFalse($this->_getAdapter()->getThumbWidth() === $width);
        }
    }
    
    protected function _getAdapter()
    {
        if (null === $this->_adapter) {
            $this->_adapter = Centurion_Image::factory($this->_adapterClass);
        }
        
        return $this->_adapter;
    }
}
