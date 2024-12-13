<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to STDERR.
 */

class StdErr extends DestinationBase
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
		if( !defined( 'STDERR') )
		{
			define( 'STDERR', fopen( 'php://stderr', 'w' ) );
		}

		fwrite( STDERR, $Text."\r\n" );
	}
}
