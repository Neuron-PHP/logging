<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\Format\IFormat;

abstract class Base implements IFormat
{
	protected function getContextString( array $contextList ) : string
	{
		$context = '';

		foreach( $contextList as $name => $value )
		{
			if( strlen( $context ) )
			{
				$context .= '|';
			}

			$context .= "$name=$value";
		}

		if( $context )
		{
			return $context;
		}

		return "";
	}

	abstract public function format( Log\Data $data ): string;
}
