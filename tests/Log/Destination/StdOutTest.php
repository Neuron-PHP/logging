<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\StdOut;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class StdOutTest extends TestCase
{
	public function testWrite()
	{
		$File = new StdOut( new PlainText() );

		ob_start();
		$File->log( "Test", ILogger::INFO );

		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '', $s );
	}
}
