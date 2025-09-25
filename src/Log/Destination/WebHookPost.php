<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Util\WebHook;
use Neuron\Validation\IsUrl;

/**
 * Sends individual log data to a webhook.
 */
class WebHookPost extends DestinationBase
{
	private string $_endPoint;

	/**
	 * @param array $params [ 'endpoint' => string ]
	 * @return bool
	 * @throws \Exception
	 */
	public function open( array $params ) : bool
	{
		$validator = new IsUrl();

		if( !$validator->isValid( $params[ 'endpoint' ] ) )
		{
			throw new \Exception( $params[ 'endpoint' ].' is not a valid url.' );
		}

		$this->_endPoint = $params[ 'endpoint' ];

		return true;
	}

	/**
	 * @param string $text
	 * @param Data $data
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		$hook = new WebHook();

		$hook->post(
			$this->_endPoint,
			[
				'level'      => $data->level->value,
				'level_text' => $data->levelText,
				'text'       => $text,
				'timestamp'  => $data->timeStamp
			]
		);
	}
}
