<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Class StdErr
 * @package Neuron\Log\Destination
 */

class StdErr extends DestinationBase
{
	private $_StdErr;
	/**
	 * @param array $Params
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function open( array $Params ) : bool
	{
		return true;
	}

	public function close()
	{
	}

	/**
	 * @param $s
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data )
	{
		if( !defined( 'STDOUT') )
		{
			define( 'STDOUT', fopen( 'php://stdout', 'w' ) );
		}

		fwrite( STDOUT, $Text."\r\n" );
	}
}
