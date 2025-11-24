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

		$text = "Test";

		$Mem->log( $text, RunLevel::ERROR );
		$Mem->log( $text, RunLevel::ERROR );

		$this->assertEquals(
			$text."\n".$text."\n",
			$Mem->getData()
		);
	}
}
