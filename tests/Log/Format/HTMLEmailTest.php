<?php

namespace Tests\Log\Format;

use Neuron\Log\Format\HTMLEmail;
use PHPUnit\Framework\TestCase;

class HTMLEmailTest extends LogTestBase
{

	public function testFormat()
	{
		$Html = new HTMLEmail();

		$out = $Html->format( $this->Data );

		$this->assertTrue(
			strstr( $out, 'DOCTYPE' ) != false
		);

	}
}
