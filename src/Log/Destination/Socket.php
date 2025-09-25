<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to a socket.
 */
class Socket extends DestinationBase
{
	private string $_address;
	private int    $_port;

	/**
	 * Configure the socket.
	 * Parameters:
	 * ip_address - IP address of the socket.
	 * port       - Port of the socket.
	 *
	 * @param array $params
	 * @return bool
	 */
	public function open( array $params ) : bool
	{
		$this->_address = $params[ 'ip_address' ];
		$this->_port    = $params[ 'port' ];

		return true;
	}

	/**
	 * @param string $sMsg
	 * @throws \Exception
	 */

	public function error( string $sMsg )
	{
		$errorCode = socket_last_error();
		$errorMsg  = socket_strerror($errorCode);

		throw new \Exception( "$sMsg: [$errorCode] $errorMsg\n" );
	}

	/**
	 * @param string $text
	 * @param Data $data
	 * @return void
	 *
	 * @throws \Exception
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		try
		{
			if( !( $sock = socket_create(AF_INET, SOCK_STREAM, 0 ) ) )
			{
				$this->error( 'Could not create socket' );
			}

			if( !socket_connect($sock , $this->_address , $this->_port ) )
			{
				$this->error( 'Could not connect' );
			}

			if( !socket_send ( $sock , $text, strlen( $text ) , 0))
			{
				$this->error( 'Write failed' );
			}
		}
		catch( \Exception $e )
		{
			// Ignore errors
			return;
		}
	}
}
