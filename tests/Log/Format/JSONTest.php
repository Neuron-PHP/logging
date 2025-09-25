<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\JSON;

class JSONTest extends LogTestBase
{
	public function testFormat()
	{
		$json = new JSON();

		$out = $json->format( $this->data );

		$decoded = json_decode( $out, true );

		$this->assertTrue(
			is_array( $decoded )
		);
	}
}
