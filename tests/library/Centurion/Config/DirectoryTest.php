<?php

require_once dirname(__FILE__) . '/../../../TestHelper.php';

class Centurion_Config_DirectoryTest extends PHPUnit_Framework_TestCase
{

    /**
     * @covers Centurion_Config_Directory::mergeArrays
     */
    public function testLoadConfig()
    {
        $configs = Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/../tests/support/configs', 'test');

        $this->assertArrayHasKey('test', $configs);

        $this->assertArrayHasKey('php', $configs['test']);
        $this->assertArrayHasKey('ini', $configs['test']);
    }

    /**
     * @covers Centurion_Config_Directory::loadConfig
     */
    public function testLoadConfigWithWrongParameter()
    {
        Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/../tests/support/configs/empty', 'test');

        $this->setExpectedException('Centurion_Exception');
        Centurion_Config_Directory::loadConfig(APPLICATION_PATH . '/../tests/support/this_diretory_does_not_exists', 'test');
    }
}
