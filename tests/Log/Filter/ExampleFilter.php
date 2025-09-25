<?php

namespace Tests\Log\Filter;

use Neuron\Log\Data;
use Neuron\Log\Filter\FilterBase;
use Neuron\Log\Filter\IFilter;

class ExampleFilter extends FilterBase
{
	public function filter( Data $data ): Data|null
	{
		$data->text = str_replace( "test", 'testing', $data->text );

		return $data;
	}
}
