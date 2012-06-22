<?php

require_once dirname(__FILE__) . '/../../../../tests/TestHelper.php';

/**
 * @covers Centurion_Mail_Transport_Blackhole
 */
class Centurion_Signal_BlackHoleTest extends PHPUnit_Framework_TestCase
{
    public function testSendMail()
    {
        $mail = new Zend_Mail();
        $mail->setBodyText('This mail should never be sent.');
        $mailTransport = new Centurion_Mail_Transport_Blackhole();

        $mailTransport->send($mail);
    }
}
