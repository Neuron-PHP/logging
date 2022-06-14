<?php

namespace Neuron\Log;

/**
 * Log multiplexer. Allows writing to multiple log destinations simultaneously.
 */
class LogMux implements ILogger
{
	private $_Logs     = [];
	private $_RunLevel = 0;

	/**
	 * @param ILogger $Log
	 */

	public function addLog( ILogger $Log )
	{
		$this->_Logs[] = $Log;
	}

	/**
	 * Clears all attached logs.
	 */

	public function reset()
	{
		$this->_Logs = [];
	}

	/**
	 * @return mixed
	 */

	public function getLogs()
	{
		return $this->_Logs;
	}

	public function setContext( string $Name, string $Value ): void
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setContext( $Name, $Value );

			$this->_RunLevel = $Log->getRunLevel();
		}
	}

	/**
	 * @param $Level
	 *
	 * Sync run levels for all loggers.
	 */

	public function setRunLevel( mixed $Level )
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevel( $Level );

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

	public function log( string $Text, int $Level )
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->log( $Text, $Level );
		}
	}

	/**
	 * @param $Text
	 */

	public function debug( string $Text )
	{
		$this->log( $Text, self::DEBUG );
	}

	/**
	 * @param $Text
	 */

	public function info( string $Text )
	{
		$this->log( $Text, self::INFO );
	}

	/**
	 * @param $Text
	 */

	public function warning( string $Text )
	{
		$this->log( $Text, self::WARNING );
	}

	/**
	 * @param $Text
	 */

	public function error( string $Text )
	{
		$this->log( $Text, self::ERROR );
	}

	/**
	 * @param $Text
	 */

	public function fatal( string $Text )
	{
		$this->log( $Text, self::FATAL );
	}
	//endregion
}
