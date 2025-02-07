<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class EchoerTest extends TestCase
{
	public function testLog()
	{
		$File = new Echoer( new PlainText() );

		ob_start();
		$File->log( "Test", RunLevel::ERROR );

		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'Test', $s );
	}
}
