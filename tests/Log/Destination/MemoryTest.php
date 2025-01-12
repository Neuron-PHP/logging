<?php

namespace Tests\Log\Destination;
use Neuron\Log\Format\Raw;
use PHPUnit\Framework\TestCase;

use Neuron\Log\Destination\Memory;

class MemoryTest extends TestCase
{
	public function testLog()
	{
		$Mem = new Memory( new Raw() );

		$Text = "Test";

		$Mem->log( $Text, \Neuron\Log\ILogger::ERROR );
		$Mem->log( $Text, \Neuron\Log\ILogger::ERROR );

		$this->assertEquals(
			$Text."\n".$Text."\n",
			$Mem->getData()
		);
	}
}
