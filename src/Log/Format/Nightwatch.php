<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\RunLevel;

/**
 * Formats log data for Laravel Nightwatch API.
 *
 * This formatter produces JSON output specifically structured for the
 * Laravel Nightwatch monitoring service. It maps Neuron's log levels
 * to Laravel/Monolog standard levels and includes all necessary metadata
 * for proper integration with Nightwatch's logging system.
 */
class Nightwatch extends Base
{
	private string $defaultChannel;
	private string $applicationName;

	/**
	 * @param string $defaultChannel Default channel if none provided by Data (default: 'neuron')
	 * @param string $applicationName Optional application identifier
	 */
	public function __construct( string $defaultChannel = 'neuron', string $applicationName = '' )
	{
		$this->defaultChannel = $defaultChannel;
		$this->applicationName = $applicationName;
	}

	/**
	 * Formats log data into Nightwatch-compatible JSON structure.
	 *
	 * @param Log\Data $data The log data to format
	 * @return string JSON-encoded log data for Nightwatch API
	 */
	public function format( Log\Data $data ): string
	{
		// Use channel from Data object if available, otherwise use default
		$channel = $data->channel ?? $this->defaultChannel;

		$nightwatchData = [
			'level'     => $this->mapLogLevel( $data->level ),
			'message'   => $data->text,
			'context'   => $data->context,
			'channel'   => $channel,
			'datetime'  => date( 'Y-m-d\TH:i:s.uP', $data->timeStamp ),
			'extra'     => [
				'neuron_level' => $data->levelText,
				'timestamp'    => $data->timeStamp
			]
		];

		// Add application name if provided
		if( $this->applicationName )
		{
			$nightwatchData['extra']['application'] = $this->applicationName;
		}

		// Include formatted context string if context exists
		if( !empty( $data->context ) )
		{
			$nightwatchData['extra']['context_string'] = $this->getContextString( $data->context );
		}

		return json_encode( $nightwatchData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Maps Neuron RunLevel to Laravel/Monolog log level strings.
	 *
	 * @param RunLevel $level The Neuron log level
	 * @return string The corresponding Laravel/Monolog level
	 */
	private function mapLogLevel( RunLevel $level ): string
	{
		return match( $level )
		{
			RunLevel::DEBUG   => 'debug',
			RunLevel::INFO    => 'info',
			RunLevel::WARNING => 'warning',
			RunLevel::ERROR   => 'error',
			RunLevel::FATAL   => 'critical'
		};
	}
}