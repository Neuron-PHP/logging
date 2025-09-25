<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Formats log data into a CSV format.
 */

class CSV extends Base
{
	public function format( Log\Data $data ) : string
	{
		return date( "Y-m-d G:i:s", $data->timeStamp ) . ",$data->levelText, {$this->getContextString( $data->context )}, $data->text";
	}
}

