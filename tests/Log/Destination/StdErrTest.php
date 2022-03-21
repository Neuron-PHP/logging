<?php

use Neuron\Log\Destination\StdErr;
use PHPUnit\Framework\TestCase;

class StdErrTest extends TestCase
{
	public function testLog()
	{
		$File = new StdErr( new \Neuron\Log\Format\PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}
}

