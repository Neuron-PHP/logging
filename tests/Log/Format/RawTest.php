<?php

namespace Log\Format;

class RawTest extends \LogTestBase
{
	public function testFormat()
	{
		$Raw = new \Neuron\Log\Format\Raw();

		$Out = $Raw->format( $this->Data );

		$this->assertEquals(
			$this::INPUT,
			$Out
		);
	}

}
