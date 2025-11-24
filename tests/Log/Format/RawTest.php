<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\Raw;

class RawTest extends LogTestBase
{
	public function testFormat()
	{
		$Raw = new Raw();

		$Out = $Raw->format( $this->data );

		$this->assertEquals(
			$this::INPUT,
			$Out
		);
	}

}
