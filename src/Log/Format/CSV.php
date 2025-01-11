<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Formats log data into a CSV format.
 */

class CSV extends Base
{
	public function format( Log\Data $Data ) : string
	{
		return date( "Y-m-d G:i:s", $Data->TimeStamp ) . ",$Data->LevelText, {$this->getContextString( $Data->Context )}, $Data->Text";
	}
}

