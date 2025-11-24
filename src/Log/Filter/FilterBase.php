<?php

namespace Neuron\Log\Filter;

use Neuron\Log\ILogger;

abstract class FilterBase implements IFilter
{
	private ILogger $_parent;

	public function setParent( ILogger $_parent ) : void
	{
		$this->_parent = $_parent;
	}

	public function getParent() : ILogger
	{
		return $this->_parent;
	}
}
