<?php

namespace Tests\Log\Destination;
use Neuron\Log\Format\Raw;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

use Neuron\Log\Destination\Memory;

class MemoryTest extends TestCase
{
	public function testLog()
	{
		$Mem = new Memory( new Raw() );

		$Text = "Test";

		$Mem->log( $Text, RunLevel::ERROR );
		$Mem->log( $Text, RunLevel::ERROR );

		$this->assertEquals(
			$Text."\n".$Text."\n",
			$Mem->getData()
		);
	}
}
