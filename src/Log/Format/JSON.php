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
			'message' => $data->text
		];

		// Add channel if present
		if( $data->channel !== null )
		{
			$aData['channel'] = $data->channel;
		}

		// Add context as structured data if present
		if( !empty( $data->context ) )
		{
			$aData['context'] = $data->context;
		}

		return json_encode( $aData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}
}
