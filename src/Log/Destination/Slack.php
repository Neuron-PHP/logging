<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Util\WebHook;
use Neuron\Validation\IsUrl;

/**
 * Outputs log data to a Slack channel.
 */
class Slack extends DestinationBase
{
	private string $_webhook;
	private array $_params;

	/**
	 * Setup slack logging.
	 *
	 * ```
	 * [
	 *    'endpoint' => 'Slack webhook url',
	 *    'params' => [
	 *        'channel' => 'Slack channel',
	 *        'username' => 'Slack user/bot name',
	 *        'text' => 'Slack message',
	 *        'icon_emoji' => 'Slack emoji icon',
	 *        'attachments' => 'Slack attachments'
	 *    ]
	 * ]
	 * ```
	 * @param array{endpoint: string, params: array{channel: string, username: string, text: string, icon_emoji?: string, attachments?: array}} $params
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

		$this->_webhook = $params[ 'endpoint' ];
		$this->_params  = $params[ 'params' ];

		return true;
	}

	/**
	 * @param string $text
	 * @param Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		$this->_params[ 'text' ] = $text;

		$dataString = json_encode( $this->_params );

		$webHook = (new WebHook())
			->postJson( $this->_webhook, $dataString );
	}
}
