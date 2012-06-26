<?php
/**
 * @class Translation_Test_Traits_Common_Abstract
 * @package Tests
 * @subpackage Translation
 * @author Richard Déloge, rd@octaveoctave.com
 *
 * Abstract class for all tests of Traits translations to generate the connection of the DB to use in this tests
 */
abstract class Translation_Test_Traits_Common_Abstract extends Zend_Test_PHPUnit_DatabaseTestCase{
    /**
     * Connection to use in these testws
     * @var Zend_Test_PHPUnit_Db_Connection
     */
    protected $_connection = null;

    /**
     * Mandatory methods called by Zend_Test_PHPUnit_DatabaseTestCase during its initialization to connect to the
     *  db of test. This method reuse configuration define in the application config, part testing
     *
     * @return null|Zend_Test_PHPUnit_Db_Connection
     */
    public function getConnection(){
        if(null === $this->_connection){
            //Build the connection in first call

            //get DB Resource to get the DB adapter for this instance
            $_front = Zend_Controller_Front::getInstance();
            $_dbResource = $_front->getParam('bootstrap')
                                  ->getPluginResource('db');
            $_dbAdapter = $_dbResource->getDbAdapter();

            //Get the db config for this instance to retrieve the name of the db to use
            $_dbConfig = $_dbAdapter->getConfig();

            $_dbAdapter->getProfiler()->setEnabled(true);

            //Create the Zend_Test_PHPUnit_Db_Connection to use in these tests
            $this->_connection = $this->createZendDbConnection(
                    $_dbAdapter,
                    $_dbConfig['dbname']
                );

            //Set a default the adapter in model to tests thems
            Zend_Db_Table_Abstract::setDefaultAdapter($_dbAdapter);
        }

        return $this->_connection;
    }

    /**
     * To disable foreign key in Mysql during database initialization
     */
    public function setUp() {
        $this->getConnection()->getConnection()->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
        $this->getConnection()->getConnection()->query('SET FOREIGN_KEY_CHECKS=0;');
        parent::setUp();
        $this->getConnection()->getConnection()->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * To disable foreign key in Mysql during database initialization
     */
    public function tearDown() {
        $this->getConnection()->getConnection()->query('SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";');
        $this->getConnection()->getConnection()->query('SET FOREIGN_KEY_CHECKS=0;');
        parent::tearDown();
        $this->getConnection()->getConnection()->query('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Method to change of locale in follow tests to test the translation mechanism
     * @param string $locale
     */
    protected function _switchLocale($locale){
        //Switch to required language
        Zend_Registry::get('Zend_Translate')->setLocale($locale);
        Zend_Locale::setDefault($locale);
        Zend_Registry::set('Zend_Locale', $locale);
        Centurion_Config_Manager::set(Translation_Traits_Common::DEFAULT_LOCALE_KEY, $locale);
    }
}
