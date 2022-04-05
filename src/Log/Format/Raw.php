<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Raw log format. Only includes the text. No date/time or level.
 */
class Raw implements IFormat
{

	/**
	 * @inheritDoc
	 */
	public function format( Log\Data $data ): string
	{
		return $data->Text;
	}
}
