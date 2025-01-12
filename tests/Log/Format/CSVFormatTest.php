<?php
namespace Tests\Log\Format;

use Tests\Log\Format\LogTestBase;

class CSVFormatTest extends LogTestBase
{
	public function testFormat()
	{
		$Csv = new \Neuron\Log\Format\CSV();

		$out = $Csv->format( $this->Data );

		$aParts = explode( ',', $out );

		$this->assertEquals(
			count( $aParts ),
			4
		);
	}
}
