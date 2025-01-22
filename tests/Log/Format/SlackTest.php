<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\Slack;

class SlackTest extends LogTestBase
{
	public function testFormat()
	{
		$Text = new Slack();

		$out = $Text->format( $this->Data );

		$this->assertTrue(
			strstr( $out, self::INPUT ) != false
		);
	}
}
