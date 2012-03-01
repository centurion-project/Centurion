<?php

require_once dirname(__FILE__) . '/../../../../../TestHelper.php';

class Centurion_Controller_Action_Helper_TicketTest extends PHPUnit_Framework_TestCase
{
    public function testTicketIsValid()
    {
        $ticketHelper = new Centurion_Controller_Action_Helper_Ticket();

        $ticket = $ticketHelper->getkey('/test');

        $this->assertTrue($ticketHelper->isValid($ticket, '/test'));
        $this->assertFalse($ticketHelper->isValid('false_ticket', '/test'));
    }

    public function testTicketIsValidEvenWithFullUrl()
    {
        $ticketHelper = new Centurion_Controller_Action_Helper_Ticket();

        $fullUrl = $ticketHelper->direct('/test');
        $ticket = $ticketHelper->getkey('/test');

        $this->assertTrue($ticketHelper->isValid($ticket, $fullUrl));
        $this->assertFalse($ticketHelper->isValid('false_ticket', $fullUrl));
    }
}