<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Validation\Url;

/**
 * Sends individual log data to a webhook.
 */
class WebHookPost extends DestinationBase
{
	private string $_EndPoint;

	public function open( array $Params ) : bool
	{
		$Validator = new Url();

		if( !$Validator->isValid( $Params[ 'endpoint' ] ) )
		{
			throw new \Exception( $Params[ 'endpoint' ].' is not a valid url.' );
		}

		$this->_EndPoint = $Params[ 'endpoint' ];

		return true;
	}

	/**
	 * @param string $Text
	 * @param Data $Data
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		$Hook = new \Neuron\Util\WebHook();

		$Hook->post(
			$this->_EndPoint,
			[
				'level'      => $Data->Level,
				'level_text' => $Data->LevelText,
				'text'       => $Text,
				'timestamp'  => $Data->TimeStamp
			]
		);
	}
}
