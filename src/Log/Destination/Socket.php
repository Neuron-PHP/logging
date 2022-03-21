<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Class Socket
 * @package Neuron\Log\Destination
 */
class Socket extends DestinationBase
{
	private string $_Address;
	private int    $_Port;

	/**
	 * @param array $Params
	 * @return bool
	 */

	public function open( array $Params ) : bool
	{
		$this->_Address = $Params[ 'ip_address' ];
		$this->_Port    = $Params[ 'port' ];

		return true;
	}

	public function close()
	{
	}

	/**
	 * @param $sMsg
	 * @throws \Exception
	 */

	protected function error( string $sMsg )
	{
		$errorcode = socket_last_error();
		$errormsg  = socket_strerror($errorcode);

		throw new \Exception( "$sMsg: [$errorcode] $errormsg\n" );
	}

	/**
	 * @param $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data )
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
