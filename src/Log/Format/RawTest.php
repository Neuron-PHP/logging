<?php

namespace Neuron\Log\Format;

use Neuron\Log;

class RawTest implements IFormat
{

	/**
	 * @inheritDoc
	 */
	public function format( Log\Data $data ): string
	{
		return $data->Text;
	}
}
