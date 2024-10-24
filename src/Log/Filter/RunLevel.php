<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

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
		if( $Data->Level >= $this->getParent()->getRunLevel() )
			return $Data;

		return null;
	}
}
