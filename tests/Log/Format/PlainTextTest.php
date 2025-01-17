<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\PlainText;

class PlainTextTest extends LogTestBase
{
	public function testFormat()
	{
		$Text = new PlainText();

		$out = $Text->format( $this->Data );

		$this->assertTrue(
			strstr( $out, self::INPUT ) != false
		);
	}
}
