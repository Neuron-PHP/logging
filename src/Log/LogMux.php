<?php

namespace Neuron\Log;

/**
 * Class LogMux
 * @package Neuron\Log
 */
class LogMux implements ILogger
{
	private $_Logs = [];

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

	/**
	 * @param $Level
	 *
	 * Sync run levels for all loggers.
	 */

	public function setRunLevel( int $Level )
	{
		foreach( $this->getLogs() as $Log )
		{
			$Log->setRunLevel( $Level );
		}
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
