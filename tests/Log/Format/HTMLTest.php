<?php

namespace Tests\Log\Format;

use Neuron\Log\Format\HTML;

class HTMLTest extends LogTestBase
{
	public function testFormat()
	{
		$Html = new HTML();

		$out = $Html->format( $this->Data );

		$this->assertTrue(
			strstr( $out, '<small>' ) != false
		);
	}
}
