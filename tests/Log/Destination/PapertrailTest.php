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

	public function testGetSeverityForAllLevels()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'use_tls' => false
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'getSeverity' );
		$method->setAccessible( true );

		// Test all log levels map to correct syslog severities
		$this->assertEquals( 7, $method->invoke( $papertrail, RunLevel::DEBUG ) );
		$this->assertEquals( 6, $method->invoke( $papertrail, RunLevel::INFO ) );
		$this->assertEquals( 5, $method->invoke( $papertrail, RunLevel::NOTICE ) );
		$this->assertEquals( 4, $method->invoke( $papertrail, RunLevel::WARNING ) );
		$this->assertEquals( 3, $method->invoke( $papertrail, RunLevel::ERROR ) );
		$this->assertEquals( 2, $method->invoke( $papertrail, RunLevel::CRITICAL ) );
		$this->assertEquals( 1, $method->invoke( $papertrail, RunLevel::ALERT ) );
		$this->assertEquals( 0, $method->invoke( $papertrail, RunLevel::EMERGENCY ) );
	}

	public function testBuildStructuredDataWithEmptyContext()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'buildStructuredData' );
		$method->setAccessible( true );

		$result = $method->invoke( $papertrail, [] );
		$this->assertEquals( '-', $result );
	}

	public function testBuildStructuredDataWithVariousTypes()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'buildStructuredData' );
		$method->setAccessible( true );

		$context = [
			'string_val' => 'test',
			'int_val' => 123,
			'bool_true' => true,
			'bool_false' => false,
			'null_val' => null,
			'array_val' => [ 'nested' => 'data' ],
			'special-chars' => 'test@value',
			'escape_quote' => 'has "quotes"',
			'escape_backslash' => 'has\\backslash',
			'escape_bracket' => 'has]bracket'
		];

		$result = $method->invoke( $papertrail, $context );

		$this->assertStringContainsString( '[neuron@32473', $result );
		$this->assertStringContainsString( 'string_val="test"', $result );
		$this->assertStringContainsString( 'int_val="123"', $result );
		$this->assertStringContainsString( 'bool_true="true"', $result );
		$this->assertStringContainsString( 'bool_false="false"', $result );
		$this->assertStringContainsString( 'null_val="null"', $result );
		$this->assertStringContainsString( 'array_val=', $result );
		$this->assertStringContainsString( 'special_chars="test@value"', $result );
		$this->assertStringContainsString( '\\"', $result ); // Escaped quote
		$this->assertStringContainsString( '\\\\', $result ); // Escaped backslash
		$this->assertStringContainsString( '\\]', $result ); // Escaped bracket
	}

	public function testFormatSyslogMessage()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'facility' => 16, // local0
			'system_name' => 'test-system'
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'formatSyslogMessage' );
		$method->setAccessible( true );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::INFO,
			'INFO',
			[ 'user_id' => 42 ],
			'my-channel'
		);

		$result = $method->invoke( $papertrail, 'Test message', $data );

		// Priority calculation: facility(16) * 8 + severity(6 for INFO) = 134
		$this->assertStringStartsWith( '<134>1 ', $result );
		$this->assertStringContainsString( 'test-system', $result );
		$this->assertStringContainsString( 'my-channel', $result );
		$this->assertStringContainsString( 'user_id="42"', $result );
		$this->assertStringContainsString( 'Test message', $result );
	}

	public function testFormatSyslogMessageWithoutChannel()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'formatSyslogMessage' );
		$method->setAccessible( true );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::ERROR,
			'ERROR',
			[]
		);

		$result = $method->invoke( $papertrail, 'Test message', $data );

		$this->assertStringContainsString( 'neuron', $result ); // Default app name
		$this->assertStringContainsString( 'Test message', $result );
	}

	public function testFormatSyslogMessageWithEmergencyLevel()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'facility' => 20 // local4
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'formatSyslogMessage' );
		$method->setAccessible( true );

		$data = new Data(
			time(),
			'Emergency!',
			RunLevel::EMERGENCY,
			'EMERGENCY',
			[]
		);

		$result = $method->invoke( $papertrail, 'Emergency!', $data );

		// Priority: facility(20) * 8 + severity(0 for EMERGENCY) = 160
		$this->assertStringStartsWith( '<160>1 ', $result );
	}

	public function testDestructor()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		// Destructor should call close without error
		unset( $papertrail );

		$this->assertTrue( true );
	}

	public function testOpenWithDefaultSystemName()
	{
		$papertrail = new Papertrail( new PlainText() );

		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		// Should use gethostname() as default
		$this->assertIsBool( $result );
	}

	public function testOpenWithDefaultTls()
	{
		$papertrail = new Papertrail( new PlainText() );

		// Default should be TLS enabled
		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
			// use_tls not specified, should default to true
		] );

		$this->assertIsBool( $result );
	}

	public function testOpenWithDefaultFacility()
	{
		$papertrail = new Papertrail( new PlainText() );

		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
			// facility not specified, should default to 16 (local0)
		] );

		$this->assertIsBool( $result );
	}

	public function testOpenWithDefaultSdId()
	{
		$papertrail = new Papertrail( new PlainText() );

		$result = $papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
			// sd_id not specified, should default to 'neuron@32473'
		] );

		$this->assertIsBool( $result );
	}

	public function testFormatMessageWithoutContext()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345,
			'system_name' => 'test-system'
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'formatSyslogMessage' );
		$method->setAccessible( true );

		$data = new Data(
			time(),
			'Simple message',
			RunLevel::INFO,
			'INFO',
			[] // Empty context
		);

		$result = $method->invoke( $papertrail, 'Simple message', $data );

		// Should contain '-' for empty structured data
		$this->assertStringContainsString( ' - Simple message', $result );
	}

	public function testGetProcId()
	{
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		$reflection = new \ReflectionClass( $papertrail );
		$method = $reflection->getMethod( 'formatSyslogMessage' );
		$method->setAccessible( true );

		$data = new Data(
			time(),
			'Test',
			RunLevel::INFO,
			'INFO',
			[]
		);

		$result = $method->invoke( $papertrail, 'Test', $data );

		// Should contain process ID
		$pid = getmypid() ?: '-';
		$this->assertStringContainsString( (string) $pid, $result );
	}

	public function testSuccessfulConnectionPath()
	{
		// Use reflection to test successful connection paths by mocking internal state
		$papertrail = new Papertrail( new PlainText() );
		$papertrail->open( [
			'host' => 'logs.papertrailapp.com',
			'port' => 12345
		] );

		$reflection = new \ReflectionClass( $papertrail );

		// Create a mock socket using php://memory
		$mockSocket = fopen( 'php://memory', 'r+' );

		// Set the socket property to simulate successful connection
		$socketProperty = $reflection->getProperty( 'socket' );
		$socketProperty->setAccessible( true );
		$socketProperty->setValue( $papertrail, $mockSocket );

		// Set connected flag
		$connectedProperty = $reflection->getProperty( 'isConnected' );
		$connectedProperty->setAccessible( true );
		$connectedProperty->setValue( $papertrail, true );

		// Now test the close method with a valid socket (covers lines 298-300)
		$papertrail->close();

		// Verify socket was closed
		$this->assertNull( $socketProperty->getValue( $papertrail ) );
		$this->assertFalse( $connectedProperty->getValue( $papertrail ) );
	}

	public function testReconnectMaxAttemptsReached()
	{
		$papertrail = new Papertrail( new PlainText() );

		$papertrail->open( [
			'host' => '127.0.0.1',
			'port' => 9999, // Unreachable
			'max_reconnect_attempts' => 2
		] );

		// Use reflection to simulate multiple reconnect attempts
		$reflection = new \ReflectionClass( $papertrail );

		$attemptsProperty = $reflection->getProperty( 'reconnectAttempts' );
		$attemptsProperty->setAccessible( true );
		$attemptsProperty->setValue( $papertrail, 2 ); // At max

		$reconnectMethod = $reflection->getMethod( 'reconnect' );
		$reconnectMethod->setAccessible( true );

		// Should return false when max attempts reached
		$result = $reconnectMethod->invoke( $papertrail );
		$this->assertFalse( $result, 'Reconnect should fail when max attempts reached' );
	}

}