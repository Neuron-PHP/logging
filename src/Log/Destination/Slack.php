<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;
use Neuron\Util\WebHook;
use Neuron\Util\WebHookResponse;
use Neuron\Validation\IsUrl;

/**
 * Outputs log data to a Slack channel.
 *
 * A log destination never throws from write(): a Slack outage must not turn
 * every Log::error() into an exception. The outcome of the last post is kept
 * instead - getLastResponse() / lastWriteSucceeded() - so a caller that posts
 * deliberately (a notifier, a queued job) can detect the failure and retry.
 */
class Slack extends DestinationBase
{
	private string $_webhook;
	private array $_params;
	private ?WebHook $_client = null;
	private ?WebHookResponse $_lastResponse = null;

	/**
	 * Use a specific HTTP client for the posts (tests, or a shared client).
	 *
	 * @param WebHook $client
	 * @return $this
	 */
	public function setWebHook( WebHook $client ): static
	{
		$this->_client = $client;

		return $this;
	}

	/**
	 * The response to the most recent write(), or null before the first.
	 *
	 * @return WebHookResponse|null
	 */
	public function getLastResponse(): ?WebHookResponse
	{
		return $this->_lastResponse;
	}

	/**
	 * Whether the most recent write() reached Slack: no transport error and a
	 * 2xx status. False before the first write.
	 *
	 * @return bool
	 */
	public function lastWriteSucceeded(): bool
	{
		$response = $this->_lastResponse;

		if( $response === null )
		{
			return false;
		}

		$code = (int)$response->getHttpCode();

		return !$response->getError() && $code >= 200 && $code < 300;
	}

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

		$this->_lastResponse = ( $this->_client ?? new WebHook() )
			->postJson( $this->_webhook, $dataString );
	}
}
