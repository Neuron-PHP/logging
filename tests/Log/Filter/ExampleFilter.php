<?php

namespace Tests\Log\Filter;

use Neuron\Log\Data;
use Neuron\Log\Filter\FilterBase;
use Neuron\Log\Filter\IFilter;

class ExampleFilter extends FilterBase
{
	public function filter( Data $Data ): Data|null
	{
		$Data->Text = str_replace( "test", 'testing', $Data->Text );

		return $Data;
	}
}
