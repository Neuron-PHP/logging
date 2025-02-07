<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\StdErr;
use Neuron\Log\Format\PlainText;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class StdErrTest extends TestCase
{
	public function testLog()
	{
		$File = new StdErr( new PlainText() );

		$File->log( "Test", RunLevel::ERROR );

		$this->assertTrue( true );
	}
}

