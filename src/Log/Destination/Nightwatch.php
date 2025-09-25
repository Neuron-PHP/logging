<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;
use Neuron\Util\WebHook;
use Neuron\Validation\IsUrl;

/**
 * Sends log data to Laravel Nightwatch monitoring service.
 *
 * This destination transmits logs to the Laravel Nightwatch API for
 * centralized monitoring and analysis. It supports authentication via
 * API tokens, configurable endpoints, and optional log batching for
 * improved performance.
 */
class Nightwatch extends DestinationBase
{
	private string $token;
	private string $endPoint;
	private int $batchSize;
	private int $timeout;
	private array $logBatch = [];
	private ?string $applicationName = null;

	/**
	 * Default Nightwatch API endpoint
	 */
	private const DEFAULT_ENDPOINT = 'https://nightwatch.laravel.com/api/logs';

	/**
	 * Opens the Nightwatch destination with configuration parameters.
	 *
	 * @param array $params Configuration array with the following keys:
	 *                      - 'token' (required): Nightwatch API authentication token
	 *                      - 'endpoint' (optional): API endpoint URL (defaults to Nightwatch service)
	 *                      - 'batch_size' (optional): Number of logs to batch before sending (default: 1)
	 *                      - 'timeout' (optional): Request timeout in seconds (default: 10)
	 *                      - 'application_name' (optional): Application identifier
	 * @return bool True if configuration is valid
	 * @throws \Exception If token is missing or endpoint URL is invalid
	 */
	public function open( array $params ): bool
	{
		// Validate required token
		if( !isset( $params['token'] ) || empty( $params['token'] ) )
		{
			throw new \Exception( 'Nightwatch destination requires a token parameter' );
		}

		$this->token = $params['token'];

		// Set endpoint with default fallback
		$this->endPoint = $params['endpoint'] ?? self::DEFAULT_ENDPOINT;

		// Validate endpoint URL
		$validator = new IsUrl();
		if( !$validator->isValid( $this->endPoint ) )
		{
			throw new \Exception( $this->endPoint . ' is not a valid URL' );
		}

		// Set optional parameters
		$this->batchSize = (int)( $params['batch_size'] ?? 1 );
		$this->timeout = (int)( $params['timeout'] ?? 10 );

		if( isset( $params['application_name'] ) )
		{
			$this->applicationName = $params['application_name'];
		}

		// Update format if it's a Nightwatch format and application name is provided
		if( $this->applicationName )
		{
			$format = $this->getFormat();
			if( $format instanceof NightwatchFormat )
			{
				// Recreate format with application name
				$this->setFormat( new NightwatchFormat( 'neuron', $this->applicationName ) );
			}
		}

		return true;
	}

	/**
	 * Writes log data to the Nightwatch service.
	 *
	 * If batching is enabled, logs are accumulated until the batch size
	 * is reached before being sent. Otherwise, logs are sent immediately.
	 *
	 * @param string $text The formatted log text (typically JSON from Nightwatch format)
	 * @param Data $data The original log data object
	 * @return void
	 */
	protected function write( string $text, Log\Data $data ): void
	{
		// If batching is disabled, send immediately
		if( $this->batchSize <= 1 )
		{
			$this->sendToNightwatch( [ $text ] );
			return;
		}

		// Add to batch
		$this->logBatch[] = $text;

		// Send batch if size reached
		if( count( $this->logBatch ) >= $this->batchSize )
		{
			$this->flushBatch();
		}
	}

	/**
	 * Sends accumulated logs to Nightwatch and clears the batch.
	 *
	 * @return void
	 */
	private function flushBatch(): void
	{
		if( empty( $this->logBatch ) )
		{
			return;
		}

		$this->sendToNightwatch( $this->logBatch );
		$this->logBatch = [];
	}

	/**
	 * Sends log data to the Nightwatch API endpoint.
	 *
	 * @param array $logs Array of JSON-formatted log entries
	 * @return void
	 */
	private function sendToNightwatch( array $logs ): void
	{
		try
		{
			$hook = new WebHook();

			// Prepare payload
			$payload = [
				'logs' => array_map( 'json_decode', $logs )
			];

			// Create custom WebHook instance with timeout
			$curlHandle = curl_init();
			curl_setopt_array(
				$curlHandle,
				[
					CURLOPT_URL            => $this->endPoint,
					CURLOPT_CUSTOMREQUEST  => 'POST',
					CURLOPT_TIMEOUT        => $this->timeout,
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_POSTFIELDS     => json_encode( $payload ),
					CURLOPT_HTTPHEADER     => [
						'Content-Type: application/json',
						'Accept: application/json',
						'Authorization: Bearer ' . $this->token,
						'User-Agent: Neuron-PHP/1.0'
					]
				]
			);

			$response = curl_exec( $curlHandle );
			$httpCode = curl_getinfo( $curlHandle, CURLINFO_HTTP_CODE );
			$error = curl_error( $curlHandle );
			curl_close( $curlHandle );

			// Log error to stderr if request failed (but don't throw exception)
			if( $httpCode >= 400 || !empty( $error ) )
			{
				$errorMessage = sprintf(
					"[Nightwatch] Failed to send logs: HTTP %d %s\n",
					$httpCode,
					$error ?: $response
				);
				fwrite( $this->getStdErr(), $errorMessage );
			}
		}
		catch( \Exception $e )
		{
			// Silently fail to avoid breaking the application
			// Optionally log to stderr for debugging
			$errorMessage = "[Nightwatch] Exception: " . $e->getMessage() . "\n";
			fwrite( $this->getStdErr(), $errorMessage );
		}
	}

	/**
	 * Closes the destination and sends any remaining batched logs.
	 *
	 * @return void
	 */
	public function close(): void
	{
		$this->flushBatch();
	}

	/**
	 * Gets the current format object.
	 * This is a helper method to access the private format property.
	 *
	 * @return \Neuron\Log\Format\IFormat
	 */
	private function getFormat(): \Neuron\Log\Format\IFormat
	{
		// Access the private $_format property through reflection
		$reflection = new \ReflectionClass( parent::class );
		$property = $reflection->getProperty( '_format' );
		$property->setAccessible( true );
		return $property->getValue( $this );
	}
}