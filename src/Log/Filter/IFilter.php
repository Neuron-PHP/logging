<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

interface IFilter
{
	/**
	 * @param Data $Data
	 * @return Data|null Return null if no logging should be performed.
	 */
	public function filter( Data $Data ) : Data | null;
}
