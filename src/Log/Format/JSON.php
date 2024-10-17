<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats data as JSON.
 */

class JSON implements IFormat
{
	/**
	 * @param Log\Data $Data
	 * @return string
	 */
	public function format( Log\Data $Data ) : string
	{
		$aData = [
			'date'	=> date( "[Y-m-d G:i:s]", $Data->TimeStamp ),
			'level'	=> $Data->LevelText,
			'text'	=> $Data->Text
		];

		return json_encode( $aData );
	}
}
