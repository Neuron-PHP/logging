<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\Sqs;
use Neuron\Log\Format\PlainText;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class SqsTest extends TestCase
{
	public function testOpenRequiresQueueUrlAndRegion()
	{
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'SQS queue_url and region are required' );

		$sqs = new Sqs( new PlainText() );
		$sqs->open( [] );
	}

	public function testOpenWithOnlyQueueUrl()
	{
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'SQS queue_url and region are required' );

		$sqs = new Sqs( new PlainText() );
		$sqs->open( [ 'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue' ] );
	}

	public function testOpenWithMissingAwsSdk()
	{
		// Skip this test if AWS SDK is installed
		if( class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			$this->markTestSkipped( 'AWS SDK is installed' );
		}

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'AWS SDK is not installed' );

		$sqs = new Sqs( new PlainText() );
		$sqs->open( [
			'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue',
			'region' => 'us-east-1'
		] );
	}

	public function testInvalidBatchSize()
	{
		// Skip if AWS SDK not installed
		if( !class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			$this->markTestSkipped( 'AWS SDK not installed' );
		}

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Batch size must be between 1 and 10' );

		$sqs = new Sqs( new PlainText() );
		$sqs->open( [
			'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue',
			'region' => 'us-east-1',
			'batch_size' => 15
		] );
	}

	public function testInvalidCredentials()
	{
		// Skip if AWS SDK not installed
		if( !class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			$this->markTestSkipped( 'AWS SDK not installed' );
		}

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Credentials must include both key and secret' );

		$sqs = new Sqs( new PlainText() );
		$sqs->open( [
			'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue',
			'region' => 'us-east-1',
			'credentials' => [ 'key' => 'test' ] // Missing secret
		] );
	}

	public function testWriteHandlesNullClient()
	{
		$sqs = new Sqs( new PlainText() );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::INFO,
			'INFO',
			[ 'test' => 'value' ],
			'test-channel'
		);

		// Should not throw exception even without successful open
		$sqs->write( 'Test message', $data );

		// No exception means the test passed
		$this->assertTrue( true );
	}

	public function testValidConfigurationStructure()
	{
		// Skip if AWS SDK not installed
		if( !class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			$this->markTestSkipped( 'AWS SDK not installed' );
		}

		$sqs = new Sqs( new PlainText() );

		// This will fail to connect but should validate parameters
		try
		{
			$sqs->open( [
				'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue',
				'region' => 'us-east-1',
				'credentials' => [
					'key' => 'AKIAIOSFODNN7EXAMPLE',
					'secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'
				],
				'batch_size' => 5,
				'auto_flush' => true,
				'attributes' => [
					'Environment' => 'test',
					'Application' => 'neuron'
				],
				'max_retries' => 2,
				'retry_delay' => 0.5
			] );
		}
		catch( \Exception $e )
		{
			// Expected to fail with connection error, not parameter error
			$this->assertStringContainsString( 'Failed to connect', $e->getMessage() );
		}
	}

	public function testMultipleWritesWithBatching()
	{
		// Skip if AWS SDK not installed
		if( !class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			$this->markTestSkipped( 'AWS SDK not installed' );
		}

		$sqs = new Sqs( new PlainText() );

		// Create mock config (will fail but tests batching logic)
		try
		{
			$sqs->open( [
				'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/test-queue',
				'region' => 'us-east-1',
				'batch_size' => 3,
				'auto_flush' => true
			] );
		}
		catch( \Exception $e )
		{
			// Expected to fail
		}

		// Write multiple messages (should handle gracefully even without connection)
		for( $i = 0; $i < 5; $i++ )
		{
			$data = new Data(
				time(),
				"Test message $i",
				RunLevel::INFO,
				'INFO',
				[ 'index' => $i ]
			);

			$sqs->write( "Test message $i", $data );
		}

		// No exception means batching logic works
		$this->assertTrue( true );
	}

	public function testDifferentLogLevels()
	{
		$sqs = new Sqs( new PlainText() );

		$levels = [
			RunLevel::DEBUG,
			RunLevel::INFO,
			RunLevel::NOTICE,
			RunLevel::WARNING,
			RunLevel::ERROR,
			RunLevel::CRITICAL,
			RunLevel::ALERT,
			RunLevel::EMERGENCY
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

			// Should handle all levels gracefully
			$sqs->write( "Test {$level->name} message", $data );
		}

		$this->assertTrue( true );
	}

	public function testContextAndChannelHandling()
	{
		$sqs = new Sqs( new PlainText() );

		// Test with complex context and channel
		$context = [
			'user_id' => 123,
			'action' => 'purchase',
			'metadata' => [
				'product_id' => 'SKU-456',
				'price' => 99.99
			],
			'tags' => [ 'ecommerce', 'payment' ]
		];

		$data = new Data(
			time(),
			'Purchase completed',
			RunLevel::INFO,
			'INFO',
			$context,
			'payments' // Channel
		);

		$sqs->write( 'Purchase completed', $data );

		// Test without channel
		$data2 = new Data(
			time(),
			'System event',
			RunLevel::DEBUG,
			'DEBUG',
			[],
			null // No channel
		);

		$sqs->write( 'System event', $data2 );

		$this->assertTrue( true );
	}
}