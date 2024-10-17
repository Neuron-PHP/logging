<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Formatter interface.
 */

interface IFormat
{
	/**
	 * @param Log\Data $Data
	 * @return string
	 */
	public function format( Log\Data $Data ) : string;
}
