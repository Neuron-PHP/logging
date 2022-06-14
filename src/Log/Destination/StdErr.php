<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs log information to STDERR.
 */

class StdErr extends DestinationBase
{
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
		if( !defined( 'STDERR') )
		{
			define( 'STDERR', fopen( 'php://stderr', 'w' ) );
		}

		fwrite( STDERR, $Text."\r\n" );
	}
}
