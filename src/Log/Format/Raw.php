<?php

namespace Neuron\Log\Format;

use Neuron\Log;

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
