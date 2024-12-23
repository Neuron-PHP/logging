<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Util\WebHook;
use Neuron\Validation\Url;

/**
 * Outputs log data to a Slack channel.
 */
class Slack extends DestinationBase
{
	private string $_Webhook;
	private array $_Params;

	/**
	 * 'channel'     => $channel,
	 * 'username'    => $bot_name,
	 * 'text'        => $message,
	 * 'icon_emoji'  => $icon,
	 * 'attachments' => $attachments
	 *
	 * @param array $Params
	 * @return bool
	 *
	 * @throws \Exception
	 */
	public function open( array $Params ) : bool
	{
		$Validator = new Url();

		if( !$Validator->isValid( $Params[ 'endpoint' ] ) )
		{
			throw new \Exception( $Params[ 'endpoint' ].' is not a valid url.' );
		}

		$this->_Webhook = $Params[ 'endpoint' ];
		$this->_Params  = $Params[ 'params' ];

		return true;
	}

	/**
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		$this->_Params[ 'text' ] = $Text;

		$DataString = json_encode( $this->_Params );

		$WebHook = (new WebHook())
			->postJson( $this->_Webhook, $DataString );
	}
}
