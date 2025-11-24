<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to a WebSocket connection.
 * Maintains a persistent connection and automatically reconnects if disconnected.
 *
 * This is a simple implementation using raw sockets with WebSocket protocol handshake.
 */
class WebSocket extends DestinationBase
{
	private $socket = null;
	private string $host;
	private int $port;
	private string $path;
	private int $reconnectAttempts = 0;
	private int $maxReconnectAttempts = 5;
	private float $reconnectDelay = 1.0; // seconds
	private bool $isConnected = false;

	/**
	 * Configure the WebSocket connection.
	 * Parameters:
	 * url - WebSocket URL (ws://host:port/path)
	 *
	 * @param array $params
	 * @return bool
	 */
	public function open( array $params ): bool
	{
		if( !isset( $params['url'] ) )
		{
			throw new \Exception( 'WebSocket URL is required' );
		}

		// Parse WebSocket URL
		$parts = parse_url( $params['url'] );
		if( $parts === false || !isset( $parts['host'] ) )
		{
			throw new \Exception( 'Invalid WebSocket URL' );
		}

		$this->host = $parts['host'];
		$this->port = $parts['port'] ?? 80;
		$this->path = $parts['path'] ?? '/';

		// Optional parameters
		if( isset( $params['max_reconnect_attempts'] ) )
		{
			$this->maxReconnectAttempts = (int) $params['max_reconnect_attempts'];
		}

		if( isset( $params['reconnect_delay'] ) )
		{
			$this->reconnectDelay = (float) $params['reconnect_delay'];
		}

		return $this->connect();
	}

	/**
	 * Connect to the WebSocket server and perform handshake.
	 *
	 * @return bool
	 */
	private function connect(): bool
	{
		try
		{
			// Create socket
			$this->socket = @socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
			if( $this->socket === false )
			{
				return false;
			}

			// Set timeout
			socket_set_option( $this->socket, SOL_SOCKET, SO_RCVTIMEO, [ 'sec' => 5, 'usec' => 0 ] );
			socket_set_option( $this->socket, SOL_SOCKET, SO_SNDTIMEO, [ 'sec' => 5, 'usec' => 0 ] );

			// Connect to server
			if( @socket_connect( $this->socket, $this->host, $this->port ) === false )
			{
				@socket_close( $this->socket );
				$this->socket = null;
				return false;
			}

			// Perform WebSocket handshake
			if( !$this->performHandshake() )
			{
				@socket_close( $this->socket );
				$this->socket = null;
				return false;
			}

			$this->isConnected = true;
			$this->reconnectAttempts = 0;
			return true;
		}
		catch( \Exception $e )
		{
			if( $this->socket !== null )
			{
				@socket_close( $this->socket );
				$this->socket = null;
			}
			return false;
		}
	}

	/**
	 * Perform WebSocket handshake.
	 *
	 * @return bool
	 */
	private function performHandshake(): bool
	{
		$key = base64_encode( random_bytes( 16 ) );

		$headers = [
			"GET {$this->path} HTTP/1.1",
			"Host: {$this->host}",
			"Upgrade: websocket",
			"Connection: Upgrade",
			"Sec-WebSocket-Key: {$key}",
			"Sec-WebSocket-Version: 13",
			"",
			""
		];

		$request = implode( "\r\n", $headers );

		// Send handshake request
		if( @socket_write( $this->socket, $request ) === false )
		{
			return false;
		}

		// Read response (simplified - just check if we get something back)
		$response = @socket_read( $this->socket, 1024 );
		if( $response === false || strpos( $response, 'HTTP/1.1 101' ) === false )
		{
			return false;
		}

		return true;
	}

	/**
	 * Attempt to reconnect to the WebSocket server.
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

		// Exponential backoff with jitter
		$delay = $this->reconnectDelay * pow( 2, $this->reconnectAttempts - 1 );
		$delay = min( $delay, 30 ); // Cap at 30 seconds
		$delay += mt_rand( 0, 1000 ) / 1000; // Add jitter

		usleep( (int)( $delay * 1000000 ) );

		return $this->connect();
	}

	/**
	 * Create a WebSocket frame for the message.
	 *
	 * @param string $message
	 * @return string
	 */
	private function createFrame( string $message ): string
	{
		$length = strlen( $message );

		// Create frame header
		$frame = chr( 0x81 ); // FIN = 1, opcode = 1 (text frame)

		if( $length < 126 )
		{
			$frame .= chr( $length | 0x80 ); // Mask bit = 1
		}
		elseif( $length < 65536 )
		{
			$frame .= chr( 126 | 0x80 );
			$frame .= pack( 'n', $length );
		}
		else
		{
			$frame .= chr( 127 | 0x80 );
			$frame .= pack( 'NN', 0, $length );
		}

		// Add masking key
		$mask = random_bytes( 4 );
		$frame .= $mask;

		// Mask the payload
		for( $i = 0; $i < $length; $i++ )
		{
			$frame .= $message[$i] ^ $mask[$i % 4];
		}

		return $frame;
	}

	/**
	 * Write log data to the WebSocket connection.
	 *
	 * @param string $text
	 * @param Data $data
	 * @return void
	 */
	public function write( string $text, Log\Data $data ): void
	{
		// Check if we have a connection
		if( !$this->isConnected || $this->socket === null )
		{
			// Try to reconnect
			if( !$this->reconnect() )
			{
				// Failed to reconnect after max attempts, silently drop the message
				return;
			}
		}

		try
		{
			// Create WebSocket frame
			$frame = $this->createFrame( $text );

			// Send the frame
			if( @socket_write( $this->socket, $frame ) === false )
			{
				// Connection lost, mark as disconnected
				$this->isConnected = false;
				@socket_close( $this->socket );
				$this->socket = null;
			}
		}
		catch( \Exception $e )
		{
			// Connection lost, try to reconnect on next write
			$this->isConnected = false;
			if( $this->socket !== null )
			{
				@socket_close( $this->socket );
				$this->socket = null;
			}
		}
	}

	/**
	 * Close the WebSocket connection.
	 */
	public function __destruct()
	{
		if( $this->socket !== null && $this->isConnected )
		{
			try
			{
				// Send close frame
				$closeFrame = chr( 0x88 ) . chr( 0x80 ) . random_bytes( 4 );
				@socket_write( $this->socket, $closeFrame );
				@socket_close( $this->socket );
			}
			catch( \Exception $e )
			{
				// Ignore close errors
			}
		}
	}
}