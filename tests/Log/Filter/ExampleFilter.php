<?php

namespace Tests\Log\Filter;

use Neuron\Log\Data;
use Neuron\Log\Filter\IFilter;

class ExampleFilter implements IFilter
{
	public function filter( int $RunLevel, Data $Data ): Data|null
	{
		$Data->Text = str_replace( "test", 'testing', $Data->Text );

		return $Data;
	}
}
