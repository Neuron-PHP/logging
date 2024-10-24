<?php

namespace Neuron\Log;

/**
 * Base log class.
 */
class Base implements ILogger
{
	private ILogger $_Logger;

	/**
	 * @param ILogger $Logger
	 */

	public function __construct( ILogger $Logger )
	{
		$this->_Logger = $Logger;
	}

	public function setContext( string $Name, string $Value ): void
	{
		$this->_Logger->setContext( $Name, $Value );
	}

	public function getContext(): array
	{
		return $this->_Logger->getContext();
	}

	/**
	 * @return ILogger
	 */

	public function getLogger() : ILogger
	{
		return $this->_Logger;
	}

	/**
	 * @param $ext
	 * @param $Level
	 *
	 * Writes to the logger. Defaults to debug level.
	 * Data is only written to the log based on the loggers run-level.
	 */

	public function log( string $Text, int $Level = self::DEBUG, array $Context = [] ): void
	{
		$this->_Logger->log( get_class( $this ).': '.$Text, $Level, $Context );
	}

	/**
	 * @param $Level
	 */

	public function setRunLevel( mixed $Level ): void
	{
		$this->_Logger->setRunLevel( $Level );
	}

	/**
	 *
	 */
	public function getRunLevel() : int
	{
		return $this->_Logger->getRunLevel();
	}

	/**
	 * @param $Text
	 */

	public function debug( string $Text ): void
	{
		$this->_Logger->debug( $Text );
	}

	/**
	 * @param $Text
	 */

	public function info( string $Text ): void
	{
		$this->_Logger->info( $Text );
	}

	/**
	 * @param $Text
	 */

	public function warning( string $Text ): void
	{
		$this->_Logger->warning( $Text );
	}

	/**
	 * @param $Text
	 */

	public function error( string $Text ): void
	{
		$this->_Logger->error( $Text );
	}

	/**
	 * @param $Text
	 */

	public function fatal( string $Text ): void
	{
		$this->_Logger->fatal( $Text );
	}
}
