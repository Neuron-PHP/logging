<?php

namespace Tests\Log\Format;

use Neuron\Log\Data;
use Neuron\Log\Format\Base;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

/**
 * Test concrete class extending Base for testing.
 */
class TestFormat extends Base
{
	public function format( Data $data ): string
	{
		return $this->getContextString( $data->context );
	}

	public function testValueToString( $value ): string
	{
		return $this->valueToString( $value );
	}
}

/**
 * Tests for Format\Base protected methods.
 */
class BaseTest extends TestCase
{
	private TestFormat $format;

	protected function setUp(): void
	{
		parent::setUp();
		$this->format = new TestFormat();
	}

	public function testGetContextStringWithMultipleValues(): void
	{
		$data = new Data(
			time(),
			'test',
			RunLevel::INFO,
			'INFO',
			[
				'user_id' => 123,
				'action' => 'login',
				'ip' => '192.168.1.1'
			]
		);

		$result = $this->format->format( $data );

		$this->assertStringContainsString( 'user_id=123', $result );
		$this->assertStringContainsString( '|', $result );
		$this->assertStringContainsString( 'action=login', $result );
		$this->assertStringContainsString( 'ip=192.168.1.1', $result );
	}

	public function testValueToStringWithThrowable(): void
	{
		$exception = new \Exception( 'Test error message' );
		$result = $this->format->testValueToString( $exception );

		$this->assertStringContainsString( 'Exception', $result );
		$this->assertStringContainsString( 'Test error message', $result );
	}

	public function testValueToStringWithNonStringableObject(): void
	{
		$object = new \stdClass();
		$result = $this->format->testValueToString( $object );

		$this->assertEquals( 'stdClass', $result );
	}

	public function testValueToStringWithResource(): void
	{
		$resource = fopen( 'php://memory', 'r' );
		$result = $this->format->testValueToString( $resource );

		$this->assertEquals( 'resource', $result );

		fclose( $resource );
	}
}
