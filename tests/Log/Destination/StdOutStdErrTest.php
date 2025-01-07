<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\StdOutStdErr;
use Neuron\Log\Format\PlainText;
use PHPUnit\Framework\TestCase;

class StdOutStdErrTest extends TestCase
{
	public function testLog()
	{
		$File = new StdOutStdErr( new PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::INFO );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}
}

