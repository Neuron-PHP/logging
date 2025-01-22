<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\StdOutStdErr;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class StdOutStdErrTest extends TestCase
{
	public function testStdOut()
	{
		$File = new StdOutStdErr( new PlainText() );

		ob_start();
		$File->log( "Test", ILogger::INFO );

		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '', $s );
	}

	public function testStdErr()
	{
		$File = new StdOutStdErr( new PlainText() );

		ob_start();
		$File->log( "Test", ILogger::ERROR );

		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '', $s );
	}

}

