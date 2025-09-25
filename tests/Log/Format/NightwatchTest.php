<?php
namespace Tests\Log\Format;

use Neuron\Log\Data;
use Neuron\Log\Format\Nightwatch;
use Neuron\Log\RunLevel;

class NightwatchTest extends LogTestBase
{
	public function testFormatBasic()
	{
		$format = new Nightwatch();
		$output = $format->format( $this->data );

		$decoded = json_decode( $output, true );

		$this->assertTrue( is_array( $decoded ), 'Output should be valid JSON' );
		$this->assertArrayHasKey( 'level', $decoded );
		$this->assertArrayHasKey( 'message', $decoded );
		$this->assertArrayHasKey( 'context', $decoded );
		$this->assertArrayHasKey( 'channel', $decoded );
		$this->assertArrayHasKey( 'datetime', $decoded );
		$this->assertArrayHasKey( 'extra', $decoded );

		$this->assertEquals( 'debug', $decoded['level'] );
		$this->assertEquals( self::INPUT, $decoded['message'] );
		$this->assertEquals( 'neuron', $decoded['channel'] );
	}

	public function testFormatWithApplicationName()
	{
		$format = new Nightwatch( 'custom-channel', 'test-app' );
		$output = $format->format( $this->data );

		$decoded = json_decode( $output, true );

		$this->assertEquals( 'custom-channel', $decoded['channel'] );
		$this->assertEquals( 'test-app', $decoded['extra']['application'] );
	}

	public function testLogLevelMapping()
	{
		$format = new Nightwatch();

		// Test all log levels
		$levels = [
			[ RunLevel::DEBUG, 'debug' ],
			[ RunLevel::INFO, 'info' ],
			[ RunLevel::WARNING, 'warning' ],
			[ RunLevel::ERROR, 'error' ],
			[ RunLevel::FATAL, 'critical' ]
		];

		foreach( $levels as $level )
		{
			$data = new Data(
				time(),
				'Test message',
				$level[0],
				$level[0]->getLevel(),
				[]
			);

			$output = $format->format( $data );
			$decoded = json_decode( $output, true );

			$this->assertEquals(
				$level[1],
				$decoded['level'],
				"RunLevel::{$level[0]->name} should map to '{$level[1]}'"
			);
		}
	}

	public function testContextHandling()
	{
		$context = [
			'user_id' => 123,
			'action'  => 'login',
			'ip'      => '192.168.1.1'
		];

		$data = new Data(
			time(),
			'User action logged',
			RunLevel::INFO,
			'INFO',
			$context
		);

		$format = new Nightwatch();
		$output = $format->format( $data );
		$decoded = json_decode( $output, true );

		$this->assertEquals( $context, $decoded['context'] );
		$this->assertArrayHasKey( 'context_string', $decoded['extra'] );
		$this->assertStringContainsString( 'user_id=123', $decoded['extra']['context_string'] );
	}

	public function testDateTimeFormat()
	{
		$timestamp = 1609459200; // 2021-01-01 00:00:00 UTC
		$data = new Data(
			$timestamp,
			'Test',
			RunLevel::INFO,
			'INFO',
			[]
		);

		$format = new Nightwatch();
		$output = $format->format( $data );
		$decoded = json_decode( $output, true );

		// Check datetime is in ISO 8601 format
		$this->assertMatchesRegularExpression(
			'/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/',
			$decoded['datetime']
		);

		// Verify timestamp is preserved in extra
		$this->assertEquals( $timestamp, $decoded['extra']['timestamp'] );
	}

	public function testJsonFlags()
	{
		$data = new Data(
			time(),
			'Test with special chars: / & "quotes" and unicode: 中文',
			RunLevel::INFO,
			'INFO',
			[]
		);

		$format = new Nightwatch();
		$output = $format->format( $data );

		// Check that slashes are not escaped and unicode is preserved
		$this->assertStringNotContainsString( '\/', $output );
		$this->assertStringContainsString( '中文', $output );
		$this->assertStringContainsString( '\\"quotes\\"', $output ); // JSON will escape quotes
	}
}