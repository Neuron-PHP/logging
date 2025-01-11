<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\Format\IFormat;

/**
 * Formats log data for Slack.
 */
class Slack extends Base
{
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
