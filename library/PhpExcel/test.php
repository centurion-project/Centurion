<?php

// load library
require 'php-excel.class.php';

// create a simple 2-dimensional array
$data = array(
        1 => array ('Name', 'Surname'),
        array('Schwarz', 'Oliver'),
        array('Test', 'Peter')
        );

// generate file (constructor parameters are optional)
$xls = new Excel_XML('UTF-8', false, 'My Test Sheet');
$xls->addArray($data);
$xls->generateXML('my-test');

?>