<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Formats log data as html.
 */

class HTML extends Base
{
	public function format( Log\Data $data ) : string
	{
		return '<small>'.date( "Y-m-d G:i:s", $data->timeStamp )."</small> $data->levelText {$this->getContextString($data->context)} $data->text<br>";
	}
}
