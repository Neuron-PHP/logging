<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats log data as JSON.
 */

class JSON extends Base
{
	/**
	 * @param Log\Data $Data
	 * @return string
	 */
	public function format( Log\Data $Data ) : string
	{
		$aData = [
			'date'	 => date( "Y-m-d G:i:s", $Data->TimeStamp ),
			'level'	 => $Data->LevelText,
			'context' => $this->getContextString( $Data->Context ),
			'text'	 => $Data->Text
		];

		return json_encode( $aData );
	}
}
