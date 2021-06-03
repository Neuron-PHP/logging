<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs to stdout
 */

class StdOut extends DestinationBase
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
	{}

	/**
	 * @param $Text
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
