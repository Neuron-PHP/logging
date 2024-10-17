<?php

namespace Tests\Log\Format;

class HTMLTest extends LogTestBase
{
	public function testFormat()
	{
		$Html = new \Neuron\Log\Format\HTML();

		$out = $Html->format( $this->Data );

		$this->assertTrue(
			strstr( $out, '<small>' ) != false
		);
	}
}
