<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Formats log data as html.
 */

class HTML extends Base
{
	public function format( Log\Data $Data ) : string
	{
		return '<small>'.date( "Y-m-d G:i:s", $Data->TimeStamp )."</small> $Data->LevelText {$this->getContextString($Data->Context)} $Data->Text<br>";
	}
}
