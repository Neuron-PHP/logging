<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log information to STDOUT.
 */

class StdOut extends DestinationBase
{
	/**
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		if( !defined( 'STDOUT') )
		{
			define( 'STDOUT', fopen( 'php://stdout', 'w' ) );
		}

		fwrite( STDOUT, $Text."\r\n" );
	}
}
