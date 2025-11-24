<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

/**
 * Standard filter that excludes log data based on the data level vs run level.
 */
class RunLevel extends FilterBase
{
	/**
	 * @param Data $data
	 * @return Data|null
	 *
	 * Filters on run-level.
	 */
	public function filter( Data $data ): Data|null
	{
		if( $data->level->value >= $this->getParent()->getRunLevel()->value )
			return $data;

		return null;
	}
}
