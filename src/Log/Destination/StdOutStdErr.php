<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs debug, info and warn to stdout.
 * Outputs error and fatal to stderr.
 */

class StdOutStdErr extends DestinationBase
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

		if( !defined( 'STDOUT') )
		{
			define( 'STDOUT', fopen( 'php://stdout', 'w' ) );
		}

		if( $Data->Level < Log\ILogger::ERROR )
		{
			fwrite( STDOUT, $Text."\r\n" );
			return;
		}

		fwrite( STDERR, $Text."\r\n" );
	}
}
