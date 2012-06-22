<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * @TODO: Test all service
 */
class Centurion_VideopianTest extends PHPUnit_Framework_TestCase
{
    protected $_services = array(
        array('vimeo', 'http://vimeo.com/35323174'),
        array('vimeo', 'http://vimeo.com/moogaloop.swf?clip_id=35323174'),
        array('youtube', 'http://www.youtube.com/watch?v=NCauq7LFOAQ'),
        array('youtube', 'http://www.youtube.com/watch?v=SToOccPytl8'),
        array('dailymotion', 'http://www.dailymotion.com/swf/x2bein_vwater_ads'),
        array('dailymotion', 'http://www.dailymotion.com/video/x2bein_vwater_ads'),
    );

    public function getServices()
    {
        return $this->_services;
    }

    /**
     * @dataProvider getServices
     */

    public function testService($key, $url)
    {
            try {
                $data = Centurion_Videopian::get($url);
            } catch (PHPUnit_Framework_Error_Warning $e) {
                if (strpos($e->getMessage(), 'HTTP/1.0 404 Not Found')) {
                    //TODO: this not appear. We should use mock object.
                    $this->markTestSkipped('404 error can appear with Vimeo when too much request.');
                } else {
                    throw $e;
                }
            }
            $this->assertTrue($data instanceof stdClass);
            $this->assertEquals($key, $data->site);
            $this->assertEquals($url, $data->url);
    }
    
    public function testException()
    {
        $this->setExpectedException('Centurion_Videopian_Exception');
        
        Centurion_Videopian::get('http://revver.com/video/330155/beer-beer-beer/');
        Centurion_Videopian::get('http://en.sevenload.com/shows/Holiday-Kitchen-TV/episodes/AQtqbMh-Sesame-Noodles');
    }
}
