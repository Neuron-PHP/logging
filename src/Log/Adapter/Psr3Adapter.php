<?php

namespace Neuron\Log\Adapter;

use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * PSR-3 LoggerInterface adapter for Neuron Logger.
 *
 * This adapter allows Neuron's logging system to be used anywhere
 * a PSR-3 compliant logger is expected. It translates PSR-3 log
 * calls to Neuron's ILogger interface, maintaining full compatibility
 * while preserving Neuron's advanced features.
 *
 * @package Neuron\Log\Adapter
 *
 * @example
 * ```php
 * // Create a Neuron logger
 * $neuronLogger = new Logger($destination);
 *
 * // Wrap it with the PSR-3 adapter
 * $psr3Logger = new Psr3Adapter($neuronLogger);
 *
 * // Use with any PSR-3 expecting code
 * $symfonyComponent = new Component($psr3Logger);
 * ```
 */
class Psr3Adapter implements LoggerInterface
{
	private ILogger $logger;

	/**
	 * @param ILogger $logger The Neuron logger to wrap
	 */
	public function __construct( ILogger $logger )
	{
		$this->logger = $logger;
	}

	/**
	 * System is unusable.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function emergency( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->emergency( (string) $message, $context );
	}

	/**
	 * Action must be taken immediately.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function alert( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->alert( (string) $message, $context );
	}

	/**
	 * Critical conditions.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function critical( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->critical( (string) $message, $context );
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function error( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->error( (string) $message, $context );
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function warning( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->warning( (string) $message, $context );
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function notice( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->notice( (string) $message, $context );
	}

	/**
	 * Interesting events.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function info( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->info( (string) $message, $context );
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 */
	public function debug( string|\Stringable $message, array $context = [] ): void
	{
		$this->logger->debug( (string) $message, $context );
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string|\Stringable $message
	 * @param mixed[] $context
	 *
	 * @return void
	 *
	 * @throws \Psr\Log\InvalidArgumentException
	 */
	public function log( $level, string|\Stringable $message, array $context = [] ): void
	{
		$runLevel = $this->mapPsr3Level( $level );
		$this->logger->log( (string) $message, $runLevel, $context );
	}

	/**
	 * Maps PSR-3 log level to Neuron RunLevel.
	 *
	 * @param mixed $level
	 * @return RunLevel
	 *
	 * @throws \InvalidArgumentException
	 */
	private function mapPsr3Level( $level ): RunLevel
	{
		if( !is_string( $level ) )
		{
			throw new \InvalidArgumentException(
				'Log level must be a string, ' . gettype( $level ) . ' given'
			);
		}

		return match( $level ) {
			LogLevel::EMERGENCY => RunLevel::EMERGENCY,
			LogLevel::ALERT     => RunLevel::ALERT,
			LogLevel::CRITICAL  => RunLevel::CRITICAL,
			LogLevel::ERROR     => RunLevel::ERROR,
			LogLevel::WARNING   => RunLevel::WARNING,
			LogLevel::NOTICE    => RunLevel::NOTICE,
			LogLevel::INFO      => RunLevel::INFO,
			LogLevel::DEBUG     => RunLevel::DEBUG,
			default => throw new \InvalidArgumentException(
				"Invalid log level: $level"
			)
		};
	}

	/**
	 * Get the underlying Neuron logger.
	 *
	 * This allows access to Neuron-specific features while
	 * still maintaining PSR-3 compatibility.
	 *
	 * @return ILogger
	 */
	public function getNeuronLogger(): ILogger
	{
		return $this->logger;
	}
}