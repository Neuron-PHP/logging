<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\PlainText;

class PlainTextTest extends LogTestBase
{
	public function testFormat()
	{
		$text = new PlainText();

		$out = $text->format( $this->data );

		$this->assertTrue(
			strstr( $out, self::INPUT ) != false
		);
	}
}
