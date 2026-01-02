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

	public function testWriteWithInvalidFileHandle()
	{
		$File = new File( new CSV() );

		// Open should fail
		$Pass = $File->open(
				[
					'file_name' => "//invalid//path/%DATE%"
				]
			);

		$this->assertFalse( $Pass );

		// Write should not crash even with invalid file handle
		$File->log( "Test message", RunLevel::ERROR );

		// Test passes if no exception is thrown
		$this->assertTrue( true );
	}

	public function testOpenCreatesDirectoryIfNotExists()
	{
		$tempDir = sys_get_temp_dir() . '/neuron_log_test_' . uniqid();
		$logFile = $tempDir . '/logs/test.log';

		$File = new File( new CSV() );

		// Directory should not exist yet
		$this->assertFalse( is_dir( dirname( $logFile ) ) );

		// Open should succeed and create directory
		$Pass = $File->open(
				[
					'file_name' => $logFile
				]
			);

		$this->assertTrue( $Pass );
		$this->assertTrue( is_dir( dirname( $logFile ) ) );
		$this->assertTrue( file_exists( $logFile ) );

		$File->close();

		// Clean up
		@unlink( $logFile );
		@rmdir( dirname( $logFile ) );
		@rmdir( $tempDir );
	}
}
