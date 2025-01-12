<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\JSON;

class JSONTest extends LogTestBase
{
	public function testFormat()
	{
		$Json = new JSON();

		$out = $Json->format( $this->Data );

		$decoded = json_decode( $out, true );

		$this->assertTrue(
			is_array( $decoded )
		);
	}
}
