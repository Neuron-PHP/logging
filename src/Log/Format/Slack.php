<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\Format\IFormat;

/**
 * Formats log data for Slack.
 */
class Slack implements IFormat
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

	/**
	 * @inheritDoc
	 */
	public function format( Log\Data $Data ): string
	{
		$Output = date( "[Y-m-d G:i:s]", $Data->TimeStamp );

		$Context = $this->getContextString( $Data->Context );

		$Output .= " *$Data->LevelText* ";

		if( $Context )
			$Output .= "_({$this->getContextString( $Data->Context )})_";

		return $Output." `".$Data->Text."`";
	}
}
