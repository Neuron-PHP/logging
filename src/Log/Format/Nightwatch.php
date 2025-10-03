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

		// Start with base structure
		$nightwatchData = [
			'level'     => $this->mapLogLevel( $data->level ),
			'message'   => $data->text,
			'context'   => [],
			'channel'   => $channel,
			'datetime'  => date( 'Y-m-d\TH:i:s.uP', $data->timeStamp ),
			'extra'     => [
				'neuron_level' => $data->levelText,
				'timestamp'    => $data->timeStamp
			]
		];

		// Process context, extracting special keys
		$context = $data->context;

		// Handle exception if present
		if( isset( $context['exception'] ) && $context['exception'] instanceof \Throwable )
		{
			$nightwatchData['exception'] = $this->formatException( $context['exception'] );
			unset( $context['exception'] );
		}

		// Handle performance metrics if present
		if( isset( $context['performance'] ) && is_array( $context['performance'] ) )
		{
			$nightwatchData['performance'] = $context['performance'];
			unset( $context['performance'] );
		}

		// Handle tags if present
		if( isset( $context['tags'] ) && is_array( $context['tags'] ) )
		{
			$nightwatchData['tags'] = $context['tags'];
			unset( $context['tags'] );
		}

		// Handle user context if present
		if( isset( $context['user'] ) && is_array( $context['user'] ) )
		{
			$nightwatchData['user'] = $context['user'];
			unset( $context['user'] );
		}

		// Handle request context if present
		if( isset( $context['request'] ) && is_array( $context['request'] ) )
		{
			$nightwatchData['request'] = $context['request'];
			unset( $context['request'] );
		}

		// Remaining context goes in the context field
		$nightwatchData['context'] = $context;

		// Add application name if provided
		if( $this->applicationName )
		{
			$nightwatchData['extra']['application'] = $this->applicationName;
		}

		// Include formatted context string if context exists
		if( !empty( $context ) )
		{
			$nightwatchData['extra']['context_string'] = $this->getContextString( $context );
		}

		return json_encode( $nightwatchData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	}

	/**
	 * Formats an exception for Nightwatch.
	 *
	 * @param \Throwable $exception
	 * @return array
	 */
	private function formatException( \Throwable $exception ): array
	{
		return [
			'class'   => get_class( $exception ),
			'message' => $exception->getMessage(),
			'code'    => $exception->getCode(),
			'file'    => $exception->getFile(),
			'line'    => $exception->getLine(),
			'trace'   => explode( "\n", $exception->getTraceAsString() )
		];
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
			RunLevel::DEBUG     => 'debug',
			RunLevel::INFO      => 'info',
			RunLevel::NOTICE    => 'notice',
			RunLevel::WARNING   => 'warning',
			RunLevel::ERROR     => 'error',
			RunLevel::CRITICAL  => 'critical',
			RunLevel::ALERT     => 'alert',
			RunLevel::EMERGENCY => 'emergency'
		};
	}
}