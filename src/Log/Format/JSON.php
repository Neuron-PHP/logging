<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats log data as JSON.
 */

class JSON extends Base
{
	/**
	 * @param Log\Data $data
	 * @return string
	 */
	public function format( Log\Data $data ) : string
	{
		$aData = [
			'date'	 => date( "Y-m-d G:i:s", $data->timeStamp ),
			'level'	 => $data->levelText,
			'context' => $this->getContextString( $data->context ),
			'text'	 => $data->text
		];

		return json_encode( $aData );
	}
}
