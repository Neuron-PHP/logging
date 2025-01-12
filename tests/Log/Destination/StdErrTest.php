<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\StdErr;
use Neuron\Log\Format\PlainText;
use PHPUnit\Framework\TestCase;

class StdErrTest extends TestCase
{
	public function testLog()
	{
		$File = new StdErr( new PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}
}

