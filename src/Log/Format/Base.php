<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\Format\IFormat;

abstract class Base implements IFormat
{
	protected function getContextString( array $ContextList ) : string
	{
		$Context = '';

		foreach( $ContextList as $Name => $Value )
		{
			if( strlen( $Context ) )
			{
				$Context .= '|';
			}

			$Context .= "$Name=$Value";
		}

		if( $Context )
		{
			return $Context;
		}

		return "";
	}

	abstract public function format( Log\Data $Data ): string;
}
