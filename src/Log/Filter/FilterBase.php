<?php

namespace Neuron\Log\Filter;

use Neuron\Log\ILogger;

abstract class FilterBase implements IFilter
{
	private ILogger $_Parent;

	public function setParent( ILogger $_Parent ) : void
	{
		$this->_Parent = $_Parent;
	}

	public function getParent() : ILogger
	{
		return $this->_Parent;
	}
}
