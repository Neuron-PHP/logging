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
	public function format( Log\Data $data ): string
	{
		$output = date( "[Y-m-d G:i:s]", $data->timeStamp );

		$context = $this->getContextString( $data->context );

		$output .= " *$data->levelText* ";

		if( $context )
			$output .= "_({$this->getContextString( $data->context )})_";

		return $output." `".$data->text."`";
	}
}
