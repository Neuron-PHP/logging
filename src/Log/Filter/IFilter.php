<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

interface IFilter
{
	/**
	 * @param int $RunLevel
	 * @param Data $Data
	 * @return Data|null Return null if no logging should be performed.
	 */
	public function filter( int $RunLevel, Data $Data ) : Data | null;
}
