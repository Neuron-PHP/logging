<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data using the php echo command. (non stdout)
 */

class Echoer extends DestinationBase
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
		echo $text."\r\n";
	}
}
