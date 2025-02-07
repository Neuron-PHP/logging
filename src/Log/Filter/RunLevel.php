<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

/**
 * Standard filter that excludes log data based on the data level vs run level.
 */
class RunLevel extends FilterBase
{
	/**
	 * @param Data $Data
	 * @return Data|null
	 *
	 * Filters on run-level.
	 */
	public function filter( Data $Data ): Data|null
	{
		if( $Data->Level->value >= $this->getParent()->getRunLevel()->value )
			return $Data;

		return null;
	}
}
