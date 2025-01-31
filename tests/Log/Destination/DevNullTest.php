<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\DevNull;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class DevNullTest extends TestCase
{
	public function testLog()
	{
		$File = new DevNull( new PlainText() );

		ob_start();
		$File->log( "Test", ILogger::ERROR );

		$s = ob_get_contents();
		ob_end_clean();

		$this->assertEquals( '', $s );
	}

	public function testLevelText()
	{
		$Pass = false;
		$File = new DevNull( new PlainText() );

		try
		{
			$File->getLevelText( -1 );
		}
		catch( \Exception $e )
		{
			$Pass = true;
		}

		$this->assertTrue( $Pass );
	}
}
