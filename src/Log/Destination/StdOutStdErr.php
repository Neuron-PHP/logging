<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs debug, info, notice, and warning to STDOUT.
 * Outputs error, critical, alert, and emergency to STDERR.
 */

class StdOutStdErr extends DestinationBase
{
	/**
	 * Writes log data to stdout or stderr.
	 *
	 * @param string $text
	 * @param Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		if( $data->level->value < Log\RunLevel::ERROR->value )
		{
			fwrite( $this->getStdOut(), $text."\r\n" );
			return;
		}

		fwrite( $this->getStdErr(), $text."\r\n" );
	}
}
