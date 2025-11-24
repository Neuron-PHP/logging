<?php
namespace Tests\Log\Format;

use Neuron\Log\Data;
use Neuron\Log\Format\Nightwatch;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class NightwatchArrayContextTest extends TestCase
{
	public function testExceptionHandling()
	{
		$exception = new \RuntimeException( 'Test error', 500 );
		$context = [
			'exception' => $exception,
			'userId'    => 123,
			'action'    => 'payment'
		];

		$data = new Data(
			time(),
			'Payment processing failed',
			RunLevel::ERROR,
			'ERROR',
			$context,
			'payments'
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// Exception should be extracted to top level
		$this->assertArrayHasKey( 'exception', $decoded );
		$this->assertEquals( 'RuntimeException', $decoded['exception']['class'] );
		$this->assertEquals( 'Test error', $decoded['exception']['message'] );
		$this->assertEquals( 500, $decoded['exception']['code'] );
		$this->assertArrayHasKey( 'file', $decoded['exception'] );
		$this->assertArrayHasKey( 'line', $decoded['exception'] );
		$this->assertArrayHasKey( 'trace', $decoded['exception'] );
		$this->assertIsArray( $decoded['exception']['trace'] );

		// Exception should be removed from context
		$this->assertArrayNotHasKey( 'exception', $decoded['context'] );

		// Other context should remain
		$this->assertEquals( 123, $decoded['context']['userId'] );
		$this->assertEquals( 'payment', $decoded['context']['action'] );

		// Channel should be included
		$this->assertEquals( 'payments', $decoded['channel'] );
	}

	public function testPerformanceMetrics()
	{
		$context = [
			'performance' => [
				'duration' => 234.5,
				'memory'   => 2048576,
				'cpu'      => 0.34
			],
			'endpoint' => '/api/users'
		];

		$data = new Data(
			time(),
			'API request completed',
			RunLevel::INFO,
			'INFO',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// Performance should be extracted to top level
		$this->assertArrayHasKey( 'performance', $decoded );
		$this->assertEquals( 234.5, $decoded['performance']['duration'] );
		$this->assertEquals( 2048576, $decoded['performance']['memory'] );
		$this->assertEquals( 0.34, $decoded['performance']['cpu'] );

		// Performance should be removed from context
		$this->assertArrayNotHasKey( 'performance', $decoded['context'] );

		// Other context should remain
		$this->assertEquals( '/api/users', $decoded['context']['endpoint'] );
	}

	public function testTagsHandling()
	{
		$context = [
			'tags'   => [ 'payment', 'critical', 'stripe' ],
			'amount' => 99.99
		];

		$data = new Data(
			time(),
			'Payment failed',
			RunLevel::ERROR,
			'ERROR',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// Tags should be extracted to top level
		$this->assertArrayHasKey( 'tags', $decoded );
		$this->assertEquals( [ 'payment', 'critical', 'stripe' ], $decoded['tags'] );

		// Tags should be removed from context
		$this->assertArrayNotHasKey( 'tags', $decoded['context'] );

		// Other context should remain
		$this->assertEquals( 99.99, $decoded['context']['amount'] );
	}

	public function testUserContext()
	{
		$context = [
			'user' => [
				'id'    => 456,
				'email' => 'john@example.com',
				'role'  => 'admin'
			],
			'action' => 'delete_records'
		];

		$data = new Data(
			time(),
			'Admin action performed',
			RunLevel::INFO,
			'INFO',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// User should be extracted to top level
		$this->assertArrayHasKey( 'user', $decoded );
		$this->assertEquals( 456, $decoded['user']['id'] );
		$this->assertEquals( 'john@example.com', $decoded['user']['email'] );
		$this->assertEquals( 'admin', $decoded['user']['role'] );

		// User should be removed from context
		$this->assertArrayNotHasKey( 'user', $decoded['context'] );

		// Other context should remain
		$this->assertEquals( 'delete_records', $decoded['context']['action'] );
	}

	public function testRequestContext()
	{
		$context = [
			'request' => [
				'id'         => 'req-123-456',
				'ip'         => '192.168.1.1',
				'url'        => '/api/v1/users',
				'method'     => 'POST',
				'user_agent' => 'Mozilla/5.0'
			],
			'response_time' => 145.2
		];

		$data = new Data(
			time(),
			'Request processed',
			RunLevel::DEBUG,
			'DEBUG',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// Request should be extracted to top level
		$this->assertArrayHasKey( 'request', $decoded );
		$this->assertEquals( 'req-123-456', $decoded['request']['id'] );
		$this->assertEquals( '192.168.1.1', $decoded['request']['ip'] );
		$this->assertEquals( '/api/v1/users', $decoded['request']['url'] );
		$this->assertEquals( 'POST', $decoded['request']['method'] );

		// Request should be removed from context
		$this->assertArrayNotHasKey( 'request', $decoded['context'] );

		// Other context should remain
		$this->assertEquals( 145.2, $decoded['context']['response_time'] );
	}

	public function testMultipleSpecialKeys()
	{
		$exception = new \InvalidArgumentException( 'Invalid input' );
		$context = [
			'exception'   => $exception,
			'user'        => [ 'id' => 789 ],
			'tags'        => [ 'validation', 'error' ],
			'performance' => [ 'duration' => 10.5 ],
			'custom_data' => 'some value'
		];

		$data = new Data(
			time(),
			'Validation failed',
			RunLevel::ERROR,
			'ERROR',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// All special keys should be extracted
		$this->assertArrayHasKey( 'exception', $decoded );
		$this->assertArrayHasKey( 'user', $decoded );
		$this->assertArrayHasKey( 'tags', $decoded );
		$this->assertArrayHasKey( 'performance', $decoded );

		// Only non-special keys should remain in context
		$this->assertArrayNotHasKey( 'exception', $decoded['context'] );
		$this->assertArrayNotHasKey( 'user', $decoded['context'] );
		$this->assertArrayNotHasKey( 'tags', $decoded['context'] );
		$this->assertArrayNotHasKey( 'performance', $decoded['context'] );
		$this->assertEquals( 'some value', $decoded['context']['custom_data'] );
	}

	public function testComplexArrayContext()
	{
		$context = [
			'order' => [
				'id'    => 'ORD-789',
				'items' => [
					[ 'sku' => 'ABC123', 'qty' => 2 ],
					[ 'sku' => 'XYZ789', 'qty' => 1 ]
				],
				'total' => 149.99
			],
			'customer_id' => 456
		];

		$data = new Data(
			time(),
			'Order processed',
			RunLevel::INFO,
			'INFO',
			$context
		);

		$format = new Nightwatch();
		$json = $format->format( $data );
		$decoded = json_decode( $json, true );

		// Complex arrays should remain in context
		$this->assertArrayHasKey( 'order', $decoded['context'] );
		$this->assertEquals( 'ORD-789', $decoded['context']['order']['id'] );
		$this->assertCount( 2, $decoded['context']['order']['items'] );
		$this->assertEquals( 456, $decoded['context']['customer_id'] );
	}
}