<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs log information using the php echo command. (non stdout)
 */

class Echoer extends DestinationBase
{
	/**
	 * @param $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		echo $Text."\r\n";
	}
}
