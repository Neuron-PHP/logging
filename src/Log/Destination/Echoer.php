<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs log information using the php echo command. (non stdout)
 */

class Echoer extends DestinationBase
{
	/**
	 * @param array $Params
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function open( array $Params ) : bool
	{
		return true;
	}

	public function close()
	{}

	/**
	 * @param $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data )
	{
		echo $Text."\r\n";
	}
}
