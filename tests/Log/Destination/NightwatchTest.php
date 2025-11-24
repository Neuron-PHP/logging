<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

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
}