<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class EchoerTest extends TestCase
{
	public function testLog()
	{
		$File = new Echoer( new PlainText() );

		$File->log( "Test", ILogger::ERROR );

		$this->assertTrue( true );
	}
}
