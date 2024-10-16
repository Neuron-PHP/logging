<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\File;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
	public function testOpenPass()
	{
		$File = new File( new \Neuron\Log\Format\CSV() );

		$Pass = true;
		try
		{
			$File->open(
				[
					'file_name' => sys_get_temp_dir() . "/%DATE%"
				]
			);
		}
		catch( Exception $Exception )
		{
			$Pass = false;
		}

		$this->assertTrue( $Pass );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue(
			file_exists( $File->getFileName() )
		);
	}
}
