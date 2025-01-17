<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Socket;
use Neuron\Log\Format\JSON;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class SocketTest extends TestCase
{

	public function testOpen()
	{
		$Socket = new Socket( new JSON() );

		$this->assertTrue(
			$Socket->open(
				[
					'ip_address' => '127.0.0.1',
					'port' => 80
				]
			)
		);
	}

	public function testWrite()
	{
		$Socket = new Socket( new JSON() );

		$Socket->open(
			[
				'ip_address' => '127.0.0.1',
				'port' => 80
			]
		);

		$Socket->log( 'Test', ILogger::DEBUG );

		$this->assertTrue( true );

		$Socket->close();
	}

	public function testError()
	{
		$Pass = false;
		$Socket = new Socket( new JSON() );

		try
		{
			$Socket->error( 'Test' );
		}
		catch( \Exception $e )
		{
			$Pass = true;
		}

		$this->assertTrue( $Pass );
	}
}
