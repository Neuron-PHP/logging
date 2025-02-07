<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Socket;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;

class SocketTest extends TestCase
{
    use PHPMock;

	 public function testError()
	 {
		 $Pass = false;

		 $Socket = new Socket(new PlainText());

		 try
		 {
			 $Socket->error('Test error');
		 }
		 catch( \Exception $e )
		 {
			 $Pass = true;
		 }

		 $this->assertTrue( $Pass );
	 }

    public function testWriteSuccess()
    {
        $socketCreate = $this->getFunctionMock('Neuron\Log\Destination', 'socket_create');
        $socketCreate->expects($this->once())->willReturn(true);

        $socketConnect = $this->getFunctionMock('Neuron\Log\Destination', 'socket_connect');
        $socketConnect->expects($this->once())->willReturn(true);

        $socketSend = $this->getFunctionMock('Neuron\Log\Destination', 'socket_send');
        $socketSend->expects($this->once())->willReturn(true);

        $Socket = new Socket(new PlainText());
        $Socket->open(['ip_address' => '127.0.0.1', 'port' => 12345]);
        $Socket->log('Test message', RunLevel::ERROR);
    }

    public function testWriteSocketCreateError()
    {

        $socketCreate = $this->getFunctionMock('Neuron\Log\Destination', 'socket_create');
        $socketCreate->expects($this->once())->willReturn(false);

        $Socket = $this->getMockBuilder(Socket::class)
            ->setConstructorArgs([new PlainText()])
            ->onlyMethods(['error'])
            ->getMock();

        $Socket->expects($this->once())
            ->method('error')
            ->with('Could not create socket')
            ->will($this->throwException(new \Exception('Could not create socket')));

        $Socket->open(['ip_address' => '127.0.0.1', 'port' => 12345]);
		 $Socket->log('Test message', RunLevel::ERROR);
    }

    public function testWriteSocketConnectError()
    {

        $socketCreate = $this->getFunctionMock('Neuron\Log\Destination', 'socket_create');
        $socketCreate->expects($this->once())->willReturn(true);

        $socketConnect = $this->getFunctionMock('Neuron\Log\Destination', 'socket_connect');
        $socketConnect->expects($this->once())->willReturn(false);

        $Socket = $this->getMockBuilder(Socket::class)
            ->setConstructorArgs([new PlainText()])
            ->onlyMethods(['error'])
            ->getMock();

        $Socket->expects($this->once())
            ->method('error')
            ->with('Could not connect')
            ->will($this->throwException(new \Exception('Could not connect')));

        $Socket->open(['ip_address' => '127.0.0.1', 'port' => 12345]);
		 $Socket->log('Test message', RunLevel::ERROR);
    }

    public function testWriteSocketSendError()
    {

        $socketCreate = $this->getFunctionMock('Neuron\Log\Destination', 'socket_create');
        $socketCreate->expects($this->once())->willReturn(true);

        $socketConnect = $this->getFunctionMock('Neuron\Log\Destination', 'socket_connect');
        $socketConnect->expects($this->once())->willReturn(true);

        $socketSend = $this->getFunctionMock('Neuron\Log\Destination', 'socket_send');
        $socketSend->expects($this->once())->willReturn(false);

        $Socket = $this->getMockBuilder(Socket::class)
            ->setConstructorArgs([new PlainText()])
            ->onlyMethods(['error'])
            ->getMock();

        $Socket->expects($this->once())
            ->method('error')
            ->with('Write failed')
            ->will($this->throwException(new \Exception('Write failed')));

        $Socket->open(['ip_address' => '127.0.0.1', 'port' => 12345]);
		 $Socket->log('Test message', RunLevel::ERROR );
    }
}
