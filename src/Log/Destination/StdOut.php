<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to STDOUT.
 */

class StdOut extends DestinationBase
{
	/**
	 * @param string $text
	 * @param Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		fwrite( $this->getStdOut(), $text."\r\n" );
	}
}
