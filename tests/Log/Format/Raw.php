<?php

namespace Log\Format;

class Raw extends \LogTestBase
{
	public function testFormat()
	{
		$Raw = new \Neuron\Log\Format\RawTest();

		$Out = $Raw->format( $this->Data );

		$this->assertEquals(
			$this::INPUT,
			$Out
		);
	}

}
