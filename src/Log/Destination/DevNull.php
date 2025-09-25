<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Generates no output. Use as dev/null.
 */

class DevNull extends DestinationBase
{
	/**
	 * @param string $text
	 * @param Data $data
	 * @return void
	 */
	public function write( string $text, Log\Data $data ) : void
	{
		// asm nop;
	}
}
