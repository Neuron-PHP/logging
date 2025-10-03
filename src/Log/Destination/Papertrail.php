<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to Papertrail via remote syslog protocol.
 * Supports both plain TCP and TLS-encrypted connections.
 */
class Papertrail extends DestinationBase
{
	private $socket = null;
	private string $host;
	private int $port;
	private string $systemName;
	private bool $useTls;
	private int $facility = 16; // Default to local0
	private string $sdId = 'neuron@32473'; // Default SD-ID for structured data
	private bool $isConnected = false;
	private int $reconnectAttempts = 0;
	private int $maxReconnectAttempts = 5;

	/**
	 * Configure the Papertrail connection.
	 * Parameters:
	 * host - Papertrail host (e.g., "logs5.papertrailapp.com")
	 * port - Papertrail port
	 * system_name - System/app name for identification (optional)
	 * use_tls - Use TLS encryption (default: true)
	 * facility - Syslog facility (default: 16 for local0)
	 * sd_id - Structured data ID (default: 'neuron@32473')
	 *
	 * @param array $params
	 * @return bool
	 */
	public function open( array $params ): bool
	{
		if( !isset( $params['host'] ) || !isset( $params['port'] ) )
		{
			throw new \Exception( 'Papertrail host and port are required' );
		}

		$this->host = $params['host'];
		$this->port = (int) $params['port'];
		$this->systemName = $params['system_name'] ?? gethostname();
		$this->useTls = $params['use_tls'] ?? true;
		$this->facility = $params['facility'] ?? 16;
		$this->sdId = $params['sd_id'] ?? 'neuron@32473';

		return $this->connect();
	}

	/**
	 * Connect to Papertrail server.
	 *
	 * @return bool
	 */
	private function connect(): bool
	{
		try
		{
			$context = null;
			$protocol = 'tcp';

			if( $this->useTls )
			{
				$protocol = 'ssl';
				$context = stream_context_create( [
					'ssl' => [
						'verify_peer' => true,
						'verify_peer_name' => true,
						'allow_self_signed' => false
					]
				] );
			}

			$this->socket = @stream_socket_client(
				"{$protocol}://{$this->host}:{$this->port}",
				$errno,
				$errstr,
				5, // 5 second timeout
				STREAM_CLIENT_CONNECT,
				$context
			);

			if( $this->socket === false )
			{
				return false;
			}

			// Set timeout for read/write operations
			stream_set_timeout( $this->socket, 5 );

			$this->isConnected = true;
			$this->reconnectAttempts = 0;
			return true;
		}
		catch( \Exception $e )
		{
			return false;
		}
	}

	/**
	 * Attempt to reconnect to Papertrail.
	 *
	 * @return bool
	 */
	private function reconnect(): bool
	{
		if( $this->reconnectAttempts >= $this->maxReconnectAttempts )
		{
			return false;
		}

		$this->reconnectAttempts++;

		// Exponential backoff
		$delay = min( pow( 2, $this->reconnectAttempts - 1 ), 30 );
		sleep( $delay );

		return $this->connect();
	}

	/**
	 * Convert log level to syslog severity.
	 *
	 * @param Log\RunLevel $level
	 * @return int
	 */
	private function getSeverity( Log\RunLevel $level ): int
	{
		return match( $level ) {
			Log\RunLevel::DEBUG => 7,     // Debug
			Log\RunLevel::INFO => 6,      // Informational
			Log\RunLevel::NOTICE => 5,    // Notice
			Log\RunLevel::WARNING => 4,   // Warning
			Log\RunLevel::ERROR => 3,     // Error
			Log\RunLevel::CRITICAL => 2,  // Critical
			Log\RunLevel::ALERT => 1,     // Alert
			Log\RunLevel::EMERGENCY => 0, // Emergency
		};
	}

	/**
	 * Format message according to syslog RFC 5424.
	 *
	 * @param string $text
	 * @param Data $data
	 * @return string
	 */
	private function formatSyslogMessage( string $text, Data $data ): string
	{
		// Calculate priority: facility * 8 + severity
		$severity = $this->getSeverity( $data->level );
		$priority = $this->facility * 8 + $severity;

		// Format timestamp in RFC 5424 format
		$timestamp = date( 'c', $data->timeStamp );

		// Build the syslog message
		// Format: <priority>version timestamp hostname app-name procid msgid structured-data msg
		$appName = $data->channel ?? 'neuron';
		$procId = getmypid() ?: '-';
		$msgId = '-';

		// Build structured data if we have context
		$structuredData = '-';
		if( !empty( $data->context ) )
		{
			$structuredData = $this->buildStructuredData( $data->context );
		}

		// Combine into syslog message
		$syslogMessage = sprintf(
			"<%d>1 %s %s %s %s %s %s %s",
			$priority,
			$timestamp,
			$this->systemName,
			$appName,
			$procId,
			$msgId,
			$structuredData,
			$text
		);

		return $syslogMessage;
	}

	/**
	 * Build structured data section for syslog message.
	 *
	 * @param array $context
	 * @return string
	 */
	private function buildStructuredData( array $context ): string
	{
		if( empty( $context ) )
		{
			return '-';
		}

		// Use configured SD-ID for structured data
		$params = [];

		foreach( $context as $key => $value )
		{
			// Sanitize key to be valid SD-PARAM-NAME
			$key = preg_replace( '/[^a-zA-Z0-9_]/', '_', $key );

			// Convert value to string
			if( is_array( $value ) || is_object( $value ) )
			{
				$value = json_encode( $value );
			}
			elseif( is_bool( $value ) )
			{
				$value = $value ? 'true' : 'false';
			}
			elseif( is_null( $value ) )
			{
				$value = 'null';
			}
			else
			{
				$value = (string) $value;
			}

			// Escape special characters in value
			$value = str_replace( ['\\', '"', ']'], ['\\\\', '\\"', '\\]'], $value );

			// Add to params
			$params[] = sprintf( '%s="%s"', $key, $value );
		}

		return '[' . $this->sdId . ' ' . implode( ' ', $params ) . ']';
	}

	/**
	 * Write log data to Papertrail.
	 *
	 * @param string $text
	 * @param Data $data
	 * @return void
	 */
	public function write( string $text, Data $data ): void
	{
		// Check connection
		if( !$this->isConnected || $this->socket === null )
		{
			if( !$this->reconnect() )
			{
				// Failed to reconnect, drop the message
				return;
			}
		}

		try
		{
			// Format the message
			$message = $this->formatSyslogMessage( $text, $data );

			// Send via TCP (add newline for TCP syslog)
			$result = @fwrite( $this->socket, $message . "\n" );

			if( $result === false )
			{
				// Connection lost
				$this->isConnected = false;
				if( is_resource( $this->socket ) )
				{
					@fclose( $this->socket );
				}
				$this->socket = null;
			}
		}
		catch( \Exception $e )
		{
			// Connection error, try reconnect on next write
			$this->isConnected = false;
			if( $this->socket !== null && is_resource( $this->socket ) )
			{
				@fclose( $this->socket );
			}
			$this->socket = null;
		}
	}

	/**
	 * Close the connection.
	 */
	public function close(): void
	{
		if( $this->socket !== null && is_resource( $this->socket ) )
		{
			@fclose( $this->socket );
			$this->socket = null;
			$this->isConnected = false;
		}
	}

	/**
	 * Clean up on destruction.
	 */
	public function __destruct()
	{
		$this->close();
	}
}