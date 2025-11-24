<?php

namespace Neuron\Log;

/**
 * Log multiplexer. Allows writing to multiple logs simultaneously.
 */
class LogMux implements ILogger
{
	private array 		$_logs     = [];
	private RunLevel	$_runLevel = RunLevel::DEBUG;
	private ?string		$channel   = null;

	/**
	 * @param ILogger $log
	 */

	/**
	 * @param ILogger $log
	 * @return void
	 *
	 * Adds a logger.
	 */
	public function addLog( ILogger $log ): void
	{
		// Set channel on the logger if we have one
		if( $this->channel !== null && method_exists( $log, 'setChannel' ) )
		{
			$log->setChannel( $this->channel );
		}

		$this->_logs[] = $log;
	}

	/**
	 * Add a filter to all attached loggers destinations.
	 *
	 * @param Filter\IFilter $filter
	 * @return bool
	 */
	public function addFilter( Filter\IFilter $filter ): bool
	{
		$added  = false;
		foreach( $this->_logs as $log )
		{
			if( $log->addFilter( $filter ) )
				$added = true;
		}

		return $added;
	}

	/**
	 * Removes a filter from all attached loggers destinations.
	 *
	 * @param Filter\IFilter $filter
	 * @return bool
	 */
	public function removeFilter( Filter\IFilter $filter ): bool
	{
		$removed = false;

		foreach( $this->_logs as $log )
		{
			if( $log->removeFilter( $filter ) )
				$removed = true;
		}

		return $removed;
	}

	/**
	 * Clears all attached loggers.
	 */

	public function reset(): void
	{
		$this->_logs = [];
	}

	/**
	 * Returns an array of all attached loggers.
	 *
	 * @return array
	 */

	public function getLogs(): array
	{
		return $this->_logs;
	}

	/**
	 * Adds context for all loggers.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function setContext( string $name, mixed $value ): void
	{
		foreach( $this->getLogs() as $log )
		{
			$log->setContext( $name, $value );
		}
	}

	/**
	 * Returns an array of all contexts.
	 *
	 * @return array
	 */
	public function getContext() : array
	{
		foreach( $this->getLogs() as $log )
		{
			return $log->getContext();
		}

		return [];
	}

	/**
	 * Set the channel name for this LogMux and all attached loggers.
	 *
	 * @param string|null $channel
	 * @return void
	 */
	public function setChannel( ?string $channel ): void
	{
		$this->channel = $channel;

		// Propagate to all child loggers
		foreach( $this->_logs as $log )
		{
			if( method_exists( $log, 'setChannel' ) )
			{
				$log->setChannel( $channel );
			}
		}
	}

	/**
	 * Get the channel name for this LogMux.
	 *
	 * @return string|null
	 */
	public function getChannel(): ?string
	{
		return $this->channel;
	}

	/**
	 * Sync run levels for all loggers.
	 *
	 * @param mixed $level
	 */

	public function setRunLevel( mixed $level ): void
	{
		foreach( $this->getLogs() as $log )
		{
			$log->setRunLevel( $level );

			$this->_runLevel = $log->getRunLevel();
		}
	}

	public function setRunLevelText( string $level ): void
	{
		foreach( $this->getLogs() as $log )
		{
			$log->setRunLevelText( $level );

			$this->_runLevel = $log->getRunLevel();
		}
	}

	/**
	 * @return RunLevel
	 */
	public function getRunLevel(): RunLevel
	{
		return $this->_runLevel;
	}

	// region ILogger

	/**
	 * @param string $text
	 * @param RunLevel $level
	 * @param array $context
	 */

	public function log( string $text, RunLevel $level, array $context = [] ): void
	{
		foreach( $this->getLogs() as $log )
		{
			$log->log( $text, $level, $context );
		}
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function debug( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::DEBUG, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function info( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::INFO, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function notice( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::NOTICE, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function warning( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::WARNING, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function error( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::ERROR, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function critical( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::CRITICAL, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function alert( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::ALERT, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */

	public function emergency( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::EMERGENCY, $context );
	}
	// endregion
}
