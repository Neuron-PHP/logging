<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\File;
use Neuron\Log\Format\CSV;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	public function testOpenPass()
	{
		$File = new File( new CSV() );

		$Pass = $File->open(
				[
					'file_name' => sys_get_temp_dir() . "/%DATE%"
				]
			);

		$this->assertTrue( $Pass );

		$File->log( "Test", RunLevel::ERROR );

		$this->assertTrue(
			file_exists( $File->getFileName() )
		);

		$File->close();
	}

	public function testOpenFail()
	{
		$File = new File( new CSV() );

		$Pass = $File->open(
				[
					'file_name' => "//invalid//path/%DATE%"
				]
			);

		$this->assertFalse( $Pass );
	}
}
