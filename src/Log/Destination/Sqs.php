<?php

namespace Neuron\Log\Destination;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to Amazon SQS (Simple Queue Service).
 *
 * This destination sends log messages to an AWS SQS queue for centralized
 * processing, analysis, or forwarding to other systems. Supports batching
 * for improved performance and custom message attributes.
 *
 * @package Neuron\Log\Destination
 */
class Sqs extends DestinationBase
{
	private ?SqsClient $client = null;
	private string $queueUrl;
	private array $messageAttributes = [];
	private int $batchSize = 1;
	private array $batchBuffer = [];
	private bool $autoFlush = true;
	private int $maxRetries = 3;
	private float $retryDelay = 1.0; // seconds

	/**
	 * Configure the SQS connection.
	 *
	 * Parameters:
	 * - queue_url: Full SQS queue URL (required)
	 * - region: AWS region (required)
	 * - credentials: Array with 'key' and 'secret', or omit for IAM role (optional)
	 * - batch_size: Number of messages to batch (1-10, default 1)
	 * - auto_flush: Automatically flush on destruct (default true)
	 * - attributes: Default message attributes to include (optional)
	 * - max_retries: Maximum retry attempts on failure (default 3)
	 * - retry_delay: Initial delay between retries in seconds (default 1.0)
	 *
	 * @param array $params Configuration parameters
	 * @return bool
	 * @throws \Exception
	 */
	public function open( array $params ): bool
	{
		if( !isset( $params['queue_url'] ) || !isset( $params['region'] ) )
		{
			throw new \Exception( 'SQS queue_url and region are required' );
		}

		if( !class_exists( '\Aws\Sqs\SqsClient' ) )
		{
			throw new \Exception( 'AWS SDK is not installed. Run: composer require aws/aws-sdk-php' );
		}

		$this->queueUrl = $params['queue_url'];

		// Build AWS configuration
		$awsConfig = [
			'region' => $params['region'],
			'version' => 'latest'
		];

		// Add credentials if provided, otherwise use IAM role/environment
		if( isset( $params['credentials'] ) )
		{
			if( !isset( $params['credentials']['key'] ) || !isset( $params['credentials']['secret'] ) )
			{
				throw new \Exception( 'Credentials must include both key and secret' );
			}

			$awsConfig['credentials'] = [
				'key'    => $params['credentials']['key'],
				'secret' => $params['credentials']['secret']
			];
		}

		// Set optional parameters
		if( isset( $params['batch_size'] ) )
		{
			$batchSize = (int) $params['batch_size'];
			if( $batchSize < 1 || $batchSize > 10 )
			{
				throw new \Exception( 'Batch size must be between 1 and 10' );
			}
			$this->batchSize = $batchSize;
		}

		if( isset( $params['auto_flush'] ) )
		{
			$this->autoFlush = (bool) $params['auto_flush'];
		}

		if( isset( $params['attributes'] ) && is_array( $params['attributes'] ) )
		{
			$this->messageAttributes = $params['attributes'];
		}

		if( isset( $params['max_retries'] ) )
		{
			$this->maxRetries = max( 1, (int) $params['max_retries'] );
		}

		if( isset( $params['retry_delay'] ) )
		{
			$this->retryDelay = max( 0.1, (float) $params['retry_delay'] );
		}

		try
		{
			$this->client = new SqsClient( $awsConfig );

			// Test connection by getting queue attributes
			$this->client->getQueueAttributes( [
				'QueueUrl' => $this->queueUrl,
				'AttributeNames' => ['QueueArn']
			] );

			return true;
		}
		catch( AwsException $e )
		{
			throw new \Exception( 'Failed to connect to SQS: ' . $e->getMessage() );
		}
	}

	/**
	 * Write log data to SQS queue.
	 *
	 * @param string $text Formatted log message
	 * @param Data $data Log data object
	 * @return void
	 */
	public function write( string $text, Data $data ): void
	{
		if( $this->client === null )
		{
			return; // Connection failed during open
		}

		// Build the message
		$message = $this->buildMessage( $text, $data );

		if( $this->batchSize > 1 )
		{
			// Add to batch buffer
			$this->batchBuffer[] = $message;

			// Send batch if buffer is full
			if( count( $this->batchBuffer ) >= $this->batchSize )
			{
				$this->flushBatch();
			}
		}
		else
		{
			// Send immediately
			$this->sendSingleMessage( $message );
		}
	}

	/**
	 * Build SQS message from log data.
	 *
	 * @param string $text
	 * @param Data $data
	 * @return array
	 */
	private function buildMessage( string $text, Data $data ): array
	{
		// Create message body as JSON
		$body = [
			'timestamp' => date( 'c', $data->timeStamp ),
			'level' => $data->level->name,
			'level_value' => $data->level->value,
			'message' => $text,
			'channel' => $data->channel ?? 'default',
			'context' => $data->context
		];

		// Build message attributes
		$attributes = [];

		// Add log level as attribute for filtering
		$attributes['LogLevel'] = [
			'DataType' => 'String',
			'StringValue' => $data->level->name
		];

		// Add channel as attribute
		if( $data->channel )
		{
			$attributes['Channel'] = [
				'DataType' => 'String',
				'StringValue' => $data->channel
			];
		}

		// Add custom attributes
		foreach( $this->messageAttributes as $key => $value )
		{
			$attributes[$key] = [
				'DataType' => 'String',
				'StringValue' => (string) $value
			];
		}

		// Build the message
		$message = [
			'MessageBody' => json_encode( $body ),
			'MessageAttributes' => $attributes
		];

		// Generate unique ID for batch sending
		if( $this->batchSize > 1 )
		{
			$message['Id'] = uniqid( 'log_', true );
		}

		return $message;
	}

	/**
	 * Send a single message to SQS.
	 *
	 * @param array $message
	 * @return void
	 */
	private function sendSingleMessage( array $message ): void
	{
		$message['QueueUrl'] = $this->queueUrl;

		$retries = 0;
		$delay = $this->retryDelay;

		while( $retries < $this->maxRetries )
		{
			try
			{
				$this->client->sendMessage( $message );
				return; // Success
			}
			catch( AwsException $e )
			{
				$retries++;
				if( $retries >= $this->maxRetries )
				{
					// Log the error internally (avoid infinite recursion)
					error_log( 'SQS: Failed to send message after ' . $this->maxRetries . ' attempts: ' . $e->getMessage() );
					return;
				}

				// Exponential backoff
				usleep( (int)( $delay * 1000000 ) );
				$delay = min( $delay * 2, 30 ); // Cap at 30 seconds
			}
		}
	}

	/**
	 * Flush the batch buffer to SQS.
	 *
	 * @return void
	 */
	public function flushBatch(): void
	{
		if( empty( $this->batchBuffer ) || $this->client === null )
		{
			return;
		}

		$retries = 0;
		$delay = $this->retryDelay;

		while( $retries < $this->maxRetries )
		{
			try
			{
				$result = $this->client->sendMessageBatch( [
					'QueueUrl' => $this->queueUrl,
					'Entries' => $this->batchBuffer
				] );

				// Check for failed messages
				if( !empty( $result['Failed'] ) )
				{
					foreach( $result['Failed'] as $failure )
					{
						error_log( 'SQS: Failed to send message: ' . $failure['Message'] );
					}
				}

				// Clear buffer after sending
				$this->batchBuffer = [];
				return;
			}
			catch( AwsException $e )
			{
				$retries++;
				if( $retries >= $this->maxRetries )
				{
					error_log( 'SQS: Failed to send batch after ' . $this->maxRetries . ' attempts: ' . $e->getMessage() );
					$this->batchBuffer = []; // Clear buffer to avoid memory issues
					return;
				}

				// Exponential backoff
				usleep( (int)( $delay * 1000000 ) );
				$delay = min( $delay * 2, 30 );
			}
		}
	}

	/**
	 * Close the SQS connection and flush any remaining messages.
	 */
	public function close(): void
	{
		if( $this->autoFlush )
		{
			$this->flushBatch();
		}

		$this->client = null;
	}

	/**
	 * Ensure batch is flushed on destruction.
	 */
	public function __destruct()
	{
		$this->close();
	}
}