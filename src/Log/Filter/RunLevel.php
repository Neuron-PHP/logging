<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

class RunLevel implements IFilter
{

	/**
	 * @param int $RunLevel
	 * @param Data $Data
	 * @return Data|null
	 *
	 * Filters on run-level.
	 */
	public function filter( int $RunLevel, Data $Data ): Data|null
	{
		if( $Data->Level >= $RunLevel )
			return $Data;

		return null;
	}
}
