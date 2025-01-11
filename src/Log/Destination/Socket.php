<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to a socket.
 */
class Socket extends DestinationBase
{
	private string $_Address;
	private int    $_Port;

	/**
	 * Configure the socket.
	 * Parameters:
	 * ip_address - IP address of the socket.
	 * port       - Port of the socket.
	 *
	 * @param array $Params
	 * @return bool
	 */
	public function open( array $Params ) : bool
	{
		$this->_Address = $Params[ 'ip_address' ];
		$this->_Port    = $Params[ 'port' ];

		return true;
	}

	/**
	 * @param string $sMsg
	 * @throws \Exception
	 */

	protected function error( string $sMsg )
	{
		$errorcode = socket_last_error();
		$errormsg  = socket_strerror($errorcode);

		throw new \Exception( "$sMsg: [$errorcode] $errormsg\n" );
	}

	/**
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 *
	 * @throws \Exception
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		if( !( $sock = socket_create(AF_INET, SOCK_STREAM, 0 ) ) )
		{
			$this->error( 'Could not create socket' );
		}

		if( !socket_connect($sock , $this->_Address , $this->_Port ) )
		{
			$this->error( 'Could not connect' );
		}

		if( !socket_send ( $sock , $Text, strlen( $Text ) , 0))
		{
			$this->error( 'Write failed' );
		}
	}
}
