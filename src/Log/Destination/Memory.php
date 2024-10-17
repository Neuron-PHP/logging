<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Stores log in formation as a string.
 * Access via the getData() method.
 */
class Memory extends DestinationBase
{
	private string $_Data = '';

	/**
	 * @return string
	 */
	public function getData(): string
	{
		return $this->_Data;
	}

	/**
	 * @param string $Data
	 * @return Memory
	 */
	public function setData( string $Data ): Memory
	{
		$this->_Data = $Data;
		return $this;
	}


	/**
	 * @inheritDoc
	 */
	protected function write( string $Text, Log\Data $Data ): void
	{
		$this->setData(
			$this->getData().$Text."\n"
		);
	}
}
