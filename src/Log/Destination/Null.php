<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Generates no output. Use as dev/null.
 */

class Null
	extends DestinationBase
{
	/**
	 * @param array $Params
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	/**
	 * @param string $Text
	 * @param Data $Data
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	/**
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 */
	public function write( string $Text, Log\Data $Data ) : void
	{
		// asm nop;
	}
}
