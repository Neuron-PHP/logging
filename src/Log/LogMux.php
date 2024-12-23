<?php

namespace Neuron\Log;

/**
 * Log multiplexer. Allows writing to multiple log destinations simultaneously.
 */
class LogMux implements ILogger
{
	private array $_Logs     = [];
	private int   $_RunLevel = 0;

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
	 * @param Filter\IFilter $Filter
	 * @return bool
	 *
	 * Add a filter to all attached loggers destinations.
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
	 * @param Filter\IFilter $Filter
	 * @return bool
	 *
	 * Removes a filter from all attached loggers destinations.
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
	 * Clears all attached logers.
	 */

	public function reset(): void
	{
		$this->_Logs = [];
	}

	/**
	 * @return array
	 *
	 * Returns an array of all attached loggers.
	 */

	public function getLogs(): array
	{
		return $this->_Logs;
	}

	/**
	 * @param string $Name
	 * @param string $Value
	 * @return void
	 *
	 * Adds context for all loggers.
	 */
	public function setContext( string $Name, string $Value ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setContext( $Name, $Value );
		}
	}

	public function getContext() : array
	{
		foreach( $this->getLogs() as $Log )
		{
			return $Log->getContext();
		}
	}

	/**
	 * @param $Level
	 *
	 * Sync run levels for all loggers.
	 */

	public function setRunLevel( mixed $Level ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevel( $Level );

			$this->_RunLevel = $Log->getRunLevel();
		}
	}

	public function setRunLevelText( string $Level )
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevelText( $Level );

			$this->_RunLevel = $Log->getRunLevel();
		}
	}

	/**
	 * @return int
	 */
	public function getRunLevel(): int
	{
		return $this->_RunLevel;
	}

	//region ILogger
	/**
	 * @param $Text
	 * @param $Level
	 */

	public function log( string $Text, int $Level ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->log( $Text, $Level );
		}
	}

	/**
	 * @param $Text
	 */

	public function debug( string $Text ): void
	{
		$this->log( $Text, self::DEBUG );
	}

	/**
	 * @param $Text
	 */

	public function info( string $Text ): void
	{
		$this->log( $Text, self::INFO );
	}

	/**
	 * @param $Text
	 */

	public function warning( string $Text ): void
	{
		$this->log( $Text, self::WARNING );
	}

	/**
	 * @param $Text
	 */

	public function error( string $Text ): void
	{
		$this->log( $Text, self::ERROR );
	}

	/**
	 * @param $Text
	 */

	public function fatal( string $Text ): void
	{
		$this->log( $Text, self::FATAL );
	}
	//endregion
}
