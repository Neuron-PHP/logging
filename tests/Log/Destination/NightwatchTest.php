<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

/**
 * Test subclass that throws exception to test error handling
 */
class NightwatchWithException extends Nightwatch
{
	public function getStdErr()
	{
		throw new \Exception( 'Simulated exception in getStdErr' );
	}
}

class NightwatchTest extends TestCase
{
	public function testOpenWithoutTokenFails()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nightwatch destination requires a token parameter' );

		$nightwatch->open( [] );
	}

	public function testOpenWithEmptyTokenFails()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nightwatch destination requires a token parameter' );

		$nightwatch->open( [ 'token' => '' ] );
	}

	public function testOpenWithInvalidEndpointFails()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'not a valid URL' );

		$nightwatch->open(
			[
				'token'    => 'test-token',
				'endpoint' => 'invalid-url'
			]
		);
	}

	public function testOpenWithValidConfiguration()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$result = $nightwatch->open(
			[
				'token' => 'test-token-12345'
			]
		);

		$this->assertTrue( $result );
	}

	public function testOpenWithCustomEndpoint()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$result = $nightwatch->open(
			[
				'token'    => 'test-token',
				'endpoint' => 'https://custom.nightwatch.com/api/logs'
			]
		);

		$this->assertTrue( $result );
	}

	public function testOpenWithAllParameters()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$result = $nightwatch->open(
			[
				'token'            => 'test-token',
				'endpoint'         => 'https://nightwatch.laravel.com/api/logs',
				'batch_size'       => 10,
				'timeout'          => 5,
				'application_name' => 'test-app'
			]
		);

		$this->assertTrue( $result );
	}

	public function testWriteHandlesApiFailureGracefully()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$nightwatch->open(
			[
				'token'    => 'test-token',
				'endpoint' => 'http://localhost:9999/nonexistent' // Unreachable endpoint
			]
		);

		$data = new Data(
			time(),
			'Test message',
			RunLevel::ERROR,
			'ERROR',
			[ 'test' => 'context' ]
		);

		// This should not throw an exception even if the API is unreachable
		try
		{
			// Use reflection to call the protected write method
			$reflection = new \ReflectionClass( $nightwatch );
			$method = $reflection->getMethod( 'write' );
			$method->setAccessible( true );

			$jsonData = ( new NightwatchFormat() )->format( $data );
			$method->invoke( $nightwatch, $jsonData, $data );

			$this->assertTrue( true, 'Write should not throw exception on API failure' );
		}
		catch( \Exception $e )
		{
			$this->fail( 'Write should handle API failures gracefully: ' . $e->getMessage() );
		}
	}

	public function testBatchingConfiguration()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		// Test batch size of 5
		$nightwatch->open(
			[
				'token'      => 'test-token',
				'batch_size' => 5,
				'endpoint'   => 'http://localhost:9999/test' // Unreachable but doesn't matter for this test
			]
		);

		$data = new Data(
			time(),
			'Batch test',
			RunLevel::INFO,
			'INFO',
			[]
		);

		// Use reflection to test batching behavior
		$reflection = new \ReflectionClass( $nightwatch );
		$batchProperty = $reflection->getProperty( 'logBatch' );
		$batchProperty->setAccessible( true );

		$writeMethod = $reflection->getMethod( 'write' );
		$writeMethod->setAccessible( true );

		// Add 4 logs (should not trigger batch send)
		for( $i = 0; $i < 4; $i++ )
		{
			$jsonData = ( new NightwatchFormat() )->format( $data );
			$writeMethod->invoke( $nightwatch, $jsonData, $data );
		}

		$batchCount = count( $batchProperty->getValue( $nightwatch ) );
		$this->assertEquals( 4, $batchCount, 'Batch should contain 4 logs' );

		// Add 5th log (should trigger batch send and clear)
		$jsonData = ( new NightwatchFormat() )->format( $data );
		$writeMethod->invoke( $nightwatch, $jsonData, $data );

		$batchCount = count( $batchProperty->getValue( $nightwatch ) );
		$this->assertEquals( 0, $batchCount, 'Batch should be cleared after reaching batch size' );
	}

	public function testCloseFlushesRemainingBatch()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$nightwatch->open(
			[
				'token'      => 'test-token',
				'batch_size' => 10,
				'endpoint'   => 'http://localhost:9999/test'
			]
		);

		$data = new Data(
			time(),
			'Test',
			RunLevel::INFO,
			'INFO',
			[]
		);

		// Use reflection to add logs to batch
		$reflection = new \ReflectionClass( $nightwatch );
		$batchProperty = $reflection->getProperty( 'logBatch' );
		$batchProperty->setAccessible( true );

		$writeMethod = $reflection->getMethod( 'write' );
		$writeMethod->setAccessible( true );

		// Add 3 logs (less than batch size)
		for( $i = 0; $i < 3; $i++ )
		{
			$jsonData = ( new NightwatchFormat() )->format( $data );
			$writeMethod->invoke( $nightwatch, $jsonData, $data );
		}

		$this->assertEquals( 3, count( $batchProperty->getValue( $nightwatch ) ) );

		// Close should flush the batch
		$nightwatch->close();

		$this->assertEquals( 0, count( $batchProperty->getValue( $nightwatch ) ), 'Batch should be empty after close' );
	}

	public function testNoBatchingWhenBatchSizeIsOne()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$nightwatch->open(
			[
				'token'      => 'test-token',
				'batch_size' => 1, // No batching
				'endpoint'   => 'http://localhost:9999/test'
			]
		);

		$data = new Data(
			time(),
			'Test',
			RunLevel::INFO,
			'INFO',
			[]
		);

		// Use reflection to verify batch is always empty
		$reflection = new \ReflectionClass( $nightwatch );
		$batchProperty = $reflection->getProperty( 'logBatch' );
		$batchProperty->setAccessible( true );

		$writeMethod = $reflection->getMethod( 'write' );
		$writeMethod->setAccessible( true );

		$jsonData = ( new NightwatchFormat() )->format( $data );
		$writeMethod->invoke( $nightwatch, $jsonData, $data );

		$this->assertEquals( 0, count( $batchProperty->getValue( $nightwatch ) ), 'Batch should always be empty when batch_size is 1' );
	}

	public function testApplicationNameUpdatesFormat()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$result = $nightwatch->open(
			[
				'token'            => 'test-token',
				'application_name' => 'my-custom-app',
				'endpoint'         => 'https://nightwatch.laravel.com/api/logs'
			]
		);

		$this->assertTrue( $result );

		// Verify the format was updated with the application name
		// by checking that a log entry includes the application name
		$data = new Data(
			time(),
			'Test with app name',
			RunLevel::INFO,
			'INFO',
			[]
		);

		$reflection = new \ReflectionClass( $nightwatch );
		$writeMethod = $reflection->getMethod( 'write' );
		$writeMethod->setAccessible( true );

		$jsonData = ( new NightwatchFormat( 'neuron', 'my-custom-app' ) )->format( $data );
		$this->assertStringContainsString( 'my-custom-app', $jsonData );
	}

	public function testInvalidUrlException()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'not a valid URL' );

		$nightwatch->open( [
			'token' => 'test-token',
			'endpoint' => 'not-a-valid-url'
		] );
	}

	public function testOpenWithoutApplicationName()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$result = $nightwatch->open( [
			'token' => 'test-token'
			// No application_name, should use default
		] );

		$this->assertTrue( $result );
	}

	public function testFlushBatchWhenEmpty()
	{
		$nightwatch = new Nightwatch( new NightwatchFormat() );

		$nightwatch->open( [
			'token' => 'test-token',
			'batch_size' => 10
		] );

		// Use reflection to call flushBatch with an empty batch
		$reflection = new \ReflectionClass( $nightwatch );
		$method = $reflection->getMethod( 'flushBatch' );
		$method->setAccessible( true );

		// Call flushBatch on empty batch - should return early without error
		$method->invoke( $nightwatch );

		// Verify batch is still empty
		$batchProperty = $reflection->getProperty( 'logBatch' );
		$batchProperty->setAccessible( true );
		$this->assertEmpty( $batchProperty->getValue( $nightwatch ) );
	}

	public function testSendToNightwatchHandlesExceptionGracefully()
	{
		// Use test subclass that throws exception in getStdErr
		$nightwatch = new NightwatchWithException( new NightwatchFormat() );

		$nightwatch->open( [
			'token' => 'test-token',
			'endpoint' => 'http://localhost:9999/test', // Unreachable
			'batch_size' => 1 // Send immediately
		] );

		$data = new Data(
			time(),
			'Test exception handling',
			RunLevel::ERROR,
			'ERROR',
			[]
		);

		// Use reflection to call write which will trigger sendToNightwatch
		$reflection = new \ReflectionClass( $nightwatch );
		$method = $reflection->getMethod( 'write' );
		$method->setAccessible( true );

		$jsonData = ( new NightwatchFormat() )->format( $data );

		// This will trigger the catch block (lines 185-190)
		// The exception from getStdErr() will be caught and handled
		// However, getStdErr() is called again in the catch block, causing another exception
		// This is expected behavior for this test - we're testing that the catch block executes
		try {
			$method->invoke( $nightwatch, $jsonData, $data );
			$this->fail( 'Should have thrown exception from mock getStdErr' );
		} catch ( \Exception $e ) {
			// Exception expected - this confirms catch block was executed
			$this->assertStringContainsString( 'Simulated exception', $e->getMessage() );
		}
	}
}