<?php

namespace Neuron\Log;

use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Patterns\Singleton\Memory;

class Log extends Memory
{
	public ?ILogger $Logger = null;

	/**
	 * Creates and initializes the core logger if needed.
	 */
	public function initIfNeeded()
	{
		if( !$this->Logger )
		{
			$this->Logger = new LogMux();

			$this->Logger->addLog(
				new Logger(
					new Echoer(
						new PlainText()
					)
				)
			);

			$this->serialize();
		}
	}

	/**
	 * @param string $Text
	 * @param int $Level
	 */
	public static function _log( string $Text, int $Level )
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		$Log->Logger->log( $Text, $Level );
	}

	/**
	 * @param int $Level
	 */
	public static function setRunLevel( int $Level )
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		$Log->Logger->setRunLevel( $Level );
		$Log->serialize();
	}

	/**
	 * @param string $Text
	 */
	public static function debug( string $Text )
	{
		self::_log( $Text, ILogger::DEBUG );
	}

	/**
	 * @param string $Text
	 */
	public static function info( string $Text )
	{
		self::_log( $Text, ILogger::INFO );
	}

	/**
	 * @param string $Text
	 */
	public static function warning( string $Text )
	{
		self::_log( $Text, ILogger::WARNING );
	}

	/**
	 * @param string $Text
	 */
	public static function error( string $Text )
	{
		self::_log( $Text, ILogger::ERROR );
	}

	/**
	 * @param string $Text
	 */
	public static function fatal( string $Text )
	{
		self::_log( $Text, ILogger::FATAL );
	}
}
