<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\Papertrail;
use Neuron\Log\Format\PlainText;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class PapertrailTest extends TestCase
{
	public function testOpenRequiresHostAndPort()
	{
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Papertrail host and port are required' );

		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [] );
	}

	public function testOpenWithOnlyHost()
	{
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Papertrail host and port are required' );

		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [ 'host' => 'logs.papertrailapp.com' ] );
	}

	public function testOpenWithValidConfig()
	{
		$papertrail = new Papertrail( new PlainText() );

		// This will fail to connect but shouldn't throw an exception
		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'system_name' => 'test-system',
			'use_tls' => false
		] );

		// Connection will fail (no valid endpoint) but open should handle it gracefully
		$this->assertIsBool( $result );
	}

	public function testWriteHandlesDisconnectionGracefully()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'use_tls' => false
		] );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::INFO,
			'INFO',
			[ 'test' => 'value' ],
			'test-channel'
		);

		// This should not throw an exception even if connection fails
		$papertrail->write( 'Test message', $data );

		// No exception means the test passed
		$this->assertTrue( true );
	}

	public function testMultipleWritesWithoutConnection()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'use_tls' => false
		] );

		// Test different log levels
		$levels = [
			RunLevel::DEBUG,
			RunLevel::INFO,
			RunLevel::WARNING,
			RunLevel::ERROR,
			RunLevel::CRITICAL
		];

		foreach( $levels as $level )
		{
			$data = new Data(
				time(),
				"Test {$level->name} message",
				$level,
				$level->name,
				[ 'level' => $level->value ]
			);

			$papertrail->write( "Test {$level->name} message", $data );
		}

		// No exception means the test passed
		$this->assertTrue( true );
	}

	public function testContextHandling()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'use_tls' => false
		] );

		// Test with complex context
		$context = [
			'user_id' => 123,
			'action' => 'login',
			'metadata' => [
				'ip' => '192.168.1.1',
				'browser' => 'Chrome'
			],
			'success' => true,
			'null_value' => null
		];

		$data = new Data(
			time(),
			'User action',
			RunLevel::INFO,
			'INFO',
			$context
		);

		$papertrail->write( 'User action', $data );

		// No exception means the test passed
		$this->assertTrue( true );
	}

	public function testTlsConfiguration()
	{
		$papertrail = new Papertrail( new PlainText() );

		// Test with TLS enabled (default)
		$result = $papertrail->open( [
			'host' => 'logs5.papertrailapp.com',
			'port' => 12345,
			'system_name' => 'test-app',
			'use_tls' => true
		] );

		$this->assertIsBool( $result );
	}

	public function testCustomFacility()
	{
		$papertrail = new Papertrail( new PlainText() );

		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'facility' => 20 // local4
		] );

		$this->assertIsBool( $result );
	}

	public function testCustomSdId()
	{
		$papertrail = new Papertrail( new PlainText() );

		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'sd_id' => 'mycompany@12345' // Custom SD-ID
		] );

		$this->assertIsBool( $result );

		// Write a message with context to test structured data
		$data = new Data(
			time(),
			'Test with custom SD-ID',
			RunLevel::INFO,
			'INFO',
			[ 'user' => 'test', 'action' => 'login' ]
		);

		// Should not throw exception
		$papertrail->write( 'Test with custom SD-ID', $data );

		$this->assertTrue( true );
	}

	public function testCloseMethod()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		// Should not throw exception
		$papertrail->close();

		// Calling close again should also be safe
		$papertrail->close();

		$this->assertTrue( true );
	}
}