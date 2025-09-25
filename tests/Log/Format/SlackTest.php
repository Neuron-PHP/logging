<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\Slack;

class SlackTest extends LogTestBase
{
	public function testFormat()
	{
		$text = new Slack();

		$out = $text->format( $this->data );

		$this->assertTrue(
			strstr( $out, self::INPUT ) != false
		);
	}
}
