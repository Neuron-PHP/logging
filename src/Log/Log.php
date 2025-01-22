<?php
namespace Neuron\Log;

use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Patterns\Singleton\Memory;

/**
 * Singleton for cross-cutting log access.
 */
class Log extends Memory
{
	public ?ILogger $Logger = null;
	public array $Channels = [];

	/**
	 * Creates and initializes the core logger if needed.
	 */
	public function initIfNeeded(): void
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
	public static function staticLog( string $Text, int $Level ): void
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		$Log->Logger->log( $Text, $Level );
	}

	/**
	 * @param int $Level
	 */
	public static function setRunLevel( mixed $Level ): void
	{
		/** @var Log $Log */
		$Log = self::getInstance();
		$Log->initIfNeeded();

		if( is_int( $Level ) )
		{
			$Log->Logger->setRunLevel( $Level );
			$Log->serialize();
			return;
		}

		$Log->Logger->setRunLevelText( $Level );
		$Log->serialize();
	}

	/**
	 * @return int
	 */
	public static function getRunLevel() : int
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		return $Log->Logger->getRunLevel();
	}

	/**
	 * @param string $Name
	 * @param string $Value
	 * @return void
	 */
	public static function setContext( string $Name, string $Value ) : void
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		$Log->Logger->setContext( $Name, $Value );

		foreach( $Log->Channels as $Channel )
			$Channel->setContext( $Name, $Value );

		$Log->serialize();
	}

	/**
	 * @param string $Text
	 */
	public static function debug( string $Text ): void
	{
		self::staticLog( $Text, ILogger::DEBUG );
	}

	/**
	 * @param string $Text
	 */
	public static function info( string $Text ): void
	{
		self::staticLog( $Text, ILogger::INFO );
	}

	/**
	 * @param string $Text
	 */
	public static function warning( string $Text ): void
	{
		self::staticLog( $Text, ILogger::WARNING );
	}

	/**
	 * @param string $Text
	 */
	public static function error( string $Text ): void
	{
		self::staticLog( $Text, ILogger::ERROR );
	}

	/**
	 * @param string $Text
	 */
	public static function fatal( string $Text ): void
	{
		self::staticLog( $Text, ILogger::FATAL );
	}

	public static function addChannel( string $Name, ILogger $Logger ) : void
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();

		if( !array_key_exists( $Name, $Log->Channels ) )
		{
			$Log->Channels[ $Name ] = new LogMux();
		}

		$Log->Channels[ $Name ]->addLog( $Logger );

		$Log->serialize();
	}

	public static function getChannel( string $Name ) : LogMux
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();

		if( !array_key_exists( $Name, $Log->Channels ) )
		{
			$Log->Channels[ $Name ] = new LogMux();
		}

		return $Log->Channels[ $Name ];
	}

	public static function addFilter( Filter\IFilter $Filter ) : void
	{
		$Log = self::getInstance();
		$Log->initIfNeeded();
		$Log->Logger->addFilter( $Filter );
	}
}
