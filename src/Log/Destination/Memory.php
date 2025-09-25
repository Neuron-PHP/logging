<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Appends log data to a string.
 * Access via the getData() method.
 */
class Memory extends DestinationBase
{
	private string $_data = '';

	/**
	 * @return string
	 */
	public function getData(): string
	{
		return $this->_data;
	}

	/**
	 * @param string $data
	 * @return Memory
	 */
	public function setData( string $data ): Memory
	{
		$this->_data = $data;
		return $this;
	}


	/**
	 * @param string $text
	 * @param Log\Data $data
	 * @return void
	 */
	protected function write( string $text, Log\Data $data ): void
	{
		$this->setData(
			$this->getData().$text."\n"
		);
	}
}
