<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\RunLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Forwards log records to Laravel Nightwatch through an existing PSR-3 logger.
 *
 * Nightwatch ingests logs from Laravel's `nightwatch` Monolog channel. Pass
 * that channel as `logger`. This destination does not call the Nightwatch
 * HTTP API. Target the `nightwatch` channel itself, never `stack` or the
 * default channel: if Laravel's default driver writes back into Neuron, a
 * stack write would recurse.
 */
class Nightwatch extends DestinationBase
{
	private const DEFAULT_CHANNEL_KEY = 'neuron_channel';

	private LoggerInterface $_logger;
	private string $_channelKey = self::DEFAULT_CHANNEL_KEY;

	/**
	 * Opens the destination.
	 *
	 * @param array $params Configuration array with the following keys:
	 *                      - 'logger' (required): PSR-3 logger, typically Laravel's nightwatch channel
	 *                      - 'channel_key' (optional): Context key for the Neuron channel name
	 * @return bool
	 * @throws \Exception If logger is missing or channel_key is invalid
	 */
	public function open( array $params ): bool
	{
		if( !isset( $params['logger'] ) || !$params['logger'] instanceof LoggerInterface )
		{
			throw new \Exception( 'Nightwatch destination requires a PSR-3 logger parameter' );
		}

		$this->_logger = $params['logger'];

		if( array_key_exists( 'channel_key', $params ) )
		{
			$key = $params['channel_key'];

			if( !is_string( $key ) || $key === '' )
			{
				throw new \Exception( 'Nightwatch channel_key must be a non-empty string' );
			}

			$this->_channelKey = $key;
		}

		return true;
	}

	/**
	 * Forwards the log record to the PSR-3 logger.
	 *
	 * The formatted string is ignored. Nightwatch reads the message, level,
	 * and context from the PSR-3 call.
	 *
	 * @param string $text
	 * @param Log\Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function write( string $text, Log\Data $data ): void
	{
		try
		{
			$context = $data->context;

			if( $data->channel !== null && $data->channel !== '' )
			{
				$context[ $this->_channelKey ] = $data->channel;
			}

			$this->_logger->log(
				$this->mapLevel( $data->level ),
				$data->text,
				$context
			);
		}
		catch( \Throwable $exception )
		{
			$this->reportFailure( $exception );
		}
	}

	/**
	 * Maps a Neuron run level to a PSR-3 level name.
	 *
	 * @param RunLevel $level
	 * @return string
	 */
	private function mapLevel( RunLevel $level ): string
	{
		return match( $level )
		{
			RunLevel::DEBUG     => LogLevel::DEBUG,
			RunLevel::INFO      => LogLevel::INFO,
			RunLevel::NOTICE    => LogLevel::NOTICE,
			RunLevel::WARNING   => LogLevel::WARNING,
			RunLevel::ERROR     => LogLevel::ERROR,
			RunLevel::CRITICAL  => LogLevel::CRITICAL,
			RunLevel::ALERT     => LogLevel::ALERT,
			RunLevel::EMERGENCY => LogLevel::EMERGENCY
		};
	}

	/**
	 * Records a forward failure without letting it escape write().
	 *
	 * @param \Throwable $exception
	 * @return void
	 */
	private function reportFailure( \Throwable $exception ): void
	{
		try
		{
			fwrite( $this->getStdErr(), '[Nightwatch] Exception: ' . $exception->getMessage() . "\n" );
		}
		catch( \Throwable )
		{
			// stderr is unavailable; write() still must not throw
		}
	}
}
