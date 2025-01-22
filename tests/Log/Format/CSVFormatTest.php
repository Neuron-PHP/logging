<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\CSV;

class CSVFormatTest extends LogTestBase
{
	public function testFormat()
	{
		$Csv = new CSV();

		$out = $Csv->format( $this->Data );

		$aParts = explode( ',', $out );

		$this->assertEquals(
			count( $aParts ),
			4
		);
	}
}
