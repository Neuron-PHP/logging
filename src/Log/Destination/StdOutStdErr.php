<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs debug, info and warn to STDOUT.
 * Outputs error and fatal to STDERR.
 */

class StdOutStdErr extends DestinationBase
{
	/**
	 * Writes log data to stdout or stderr.
	 *
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		if( $Data->Level < Log\ILogger::ERROR )
		{
			fwrite( STDOUT, $Text."\r\n" );
			return;
		}

		fwrite( STDERR, $Text."\r\n" );
	}
}
