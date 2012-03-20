<?php

require_once dirname(__FILE__) . '/../../TestHelper.php';

/**
 * TODO: change the url that is on 404
 */
class Centurion_VideopianTest extends PHPUnit_Framework_TestCase
{
    protected $_services = array(
        array('vimeo', array('2884813'    =>  'http://www.vimeo.com/2884813',
                           '7022191'    =>  'http://vimeo.com/moogaloop.swf?clip_id=7022191')
        ),
        array('youtube', array('NCauq7LFOAQ'    =>  'http://www.youtube.com/watch?v=NCauq7LFOAQ',
                           'SToOccPytl8'    =>  'http://www.youtube.com/watch?v=SToOccPytl8')
        ),
        array('dailymotion', array('xbcrrc'             =>  'http://www.dailymotion.com/swf/xbcrrc',
                       'x2bein_vwater_ads'  =>  'http://www.dailymotion.com/video/x2bein_vwater_ads')
        )
    );

    public function getServices()
    {
        return $this->_services;
    }

    /**
     * @dataProvider getServices
     */

    public function testService($key, $urls)
    {
        foreach ($urls as $id => $url) {
            $data = Centurion_Videopian::get($url);
            $this->assertTrue($data instanceof stdClass);
            $this->assertEquals($key, $data->site);
            $this->assertEquals($url, $data->url);
        }
    }
    
    public function testException()
    {
        $this->setExpectedException('Centurion_Videopian_Exception');
        
        Centurion_Videopian::get('http://revver.com/video/330155/beer-beer-beer/');
        Centurion_Videopian::get('http://en.sevenload.com/shows/Holiday-Kitchen-TV/episodes/AQtqbMh-Sesame-Noodles');
    }
}
