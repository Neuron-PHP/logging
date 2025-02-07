<?php

namespace Neuron\Log;

/**
 * Log multiplexer. Allows writing to multiple logs simultaneously.
 */
class LogMux implements ILogger
{
	private array 		$_Logs     = [];
	private RunLevel	$_RunLevel = RunLevel::DEBUG;

	/**
	 * @param ILogger $Log
	 */

	/**
	 * @param ILogger $Log
	 * @return void
	 *
	 * Adds a logger.
	 */
	public function addLog( ILogger $Log ): void
	{
		$this->_Logs[] = $Log;
	}

	/**
	 * Add a filter to all attached loggers destinations.
	 *
	 * @param Filter\IFilter $Filter
	 * @return bool
	 */
	public function addFilter( Filter\IFilter $Filter ): bool
	{
		$Added  = false;
		foreach( $this->_Logs as $Log )
		{
			if( $Log->addFilter( $Filter ) )
				$Added = true;
		}

		return $Added;
	}

	/**
	 * Removes a filter from all attached loggers destinations.
	 *
	 * @param Filter\IFilter $Filter
	 * @return bool
	 */
	public function removeFilter( Filter\IFilter $Filter ): bool
	{
		$Removed = false;

		foreach( $this->_Logs as $Log )
		{
			if( $Log->removeFilter( $Filter ) )
				$Removed = true;
		}

		return $Removed;
	}

	/**
	 * Clears all attached loggers.
	 */

	public function reset(): void
	{
		$this->_Logs = [];
	}

	/**
	 * Returns an array of all attached loggers.
	 *
	 * @return array
	 */

	public function getLogs(): array
	{
		return $this->_Logs;
	}

	/**
	 * Adds context for all loggers.
	 *
	 * @param string $Name
	 * @param string $Value
	 * @return void
	 */
	public function setContext( string $Name, string $Value ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setContext( $Name, $Value );
		}
	}

	/**
	 * Returns an array of all contexts.
	 *
	 * @return array
	 */
	public function getContext() : array
	{
		foreach( $this->getLogs() as $Log )
		{
			return $Log->getContext();
		}

		return [];
	}

	/**
	 * Sync run levels for all loggers.
	 *
	 * @param mixed $Level
	 */

	public function setRunLevel( mixed $Level ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevel( $Level );

			$this->_RunLevel = $Log->getRunLevel();
		}
	}

	public function setRunLevelText( string $Level ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevelText( $Level );

			$this->_RunLevel = $Log->getRunLevel();
		}
	}

	/**
	 * @return RunLevel
	 */
	public function getRunLevel(): RunLevel
	{
		return $this->_RunLevel;
	}

	// region ILogger

	/**
	 * @param string $Text
	 * @param RunLevel $Level
	 */

	public function log( string $Text, RunLevel $Level ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->log( $Text, $Level );
		}
	}

	/**
	 * @param string $Text
	 */

	public function debug( string $Text ): void
	{
		$this->log( $Text, RunLevel::DEBUG );
	}

	/**
	 * @param string $Text
	 */

	public function info( string $Text ): void
	{
		$this->log( $Text, RunLevel::INFO );
	}

	/**
	 * @param string $Text
	 */

	public function warning( string $Text ): void
	{
		$this->log( $Text, RunLevel::WARNING );
	}

	/**
	 * @param string $Text
	 */

	public function error( string $Text ): void
	{
		$this->log( $Text, RunLevel::ERROR );
	}

	/**
	 * @param $Text
	 */

	public function fatal( string $Text ): void
	{
		$this->log( $Text, RunLevel::FATAL );
	}
	// endregion
}
