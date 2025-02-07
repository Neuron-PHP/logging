<?php

use Neuron\Log\Data;
use PHPUnit\Framework\TestCase;
use Neuron\Log\Destination\Email;
use phpmock\phpunit\PHPMock;

class EmailTest extends TestCase
{
    use PHPMock;

    public function testWrite()
    {
        $mail = $this->getFunctionMock('Neuron\Log\Destination', 'mail');
        $mail->expects($this->once())
             ->with(
                 $this->equalTo('test@example.com'),
                 $this->equalTo('Test Subject'),
                 $this->equalTo('Test message'),
                 $this->equalTo('From: sender@example.com')
             )
             ->willReturn(true);

        $email = new Email();
        $email->open([
            'to' => 'test@example.com',
            'from' => 'sender@example.com',
            'subject' => 'Test Subject'
        ]);

        $email->write('Test message', $this->createMock( Data::class));
    }
}
