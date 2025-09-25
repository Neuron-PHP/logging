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
	public ?ILogger $logger = null;
	public array $channels = [];

	/**
	 * Creates and initializes the core logger if needed.
	 */
	public function initIfNeeded(): void
	{
		if( !$this->logger )
		{
			$this->logger = new LogMux();

			$this->logger->addLog(
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
	 * @param string $text
	 * @param RunLevel $level
	 */
	public static function staticLog( string $text, RunLevel $level ): void
	{
		$log = self::getInstance();
		$log->initIfNeeded();
		$log->logger->log( $text, $level );
	}

	/**
	 * @param int $level
	 */
	public static function setRunLevel( mixed $level ): void
	{
		/** @var Log $log */
		$log = self::getInstance();
		$log->initIfNeeded();

		if( $level instanceof RunLevel )
		{
			$log->logger->setRunLevel( $level );
			$log->serialize();
			return;
		}

		$log->logger->setRunLevelText( $level );
		$log->serialize();
	}

	/**
	 * @return RunLevel
	 */
	public static function getRunLevel() : RunLevel
	{
		$log = self::getInstance();
		$log->initIfNeeded();
		return $log->logger->getRunLevel();
	}

	/**
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public static function setContext( string $name, string $value ) : void
	{
		$log = self::getInstance();
		$log->initIfNeeded();
		$log->logger->setContext( $name, $value );

		foreach( $log->channels as $channel )
			$channel->setContext( $name, $value );

		$log->serialize();
	}

	/**
	 * @param string $text
	 */
	public static function debug( string $text ): void
	{
		self::staticLog( $text, RunLevel::DEBUG );
	}

	/**
	 * @param string $text
	 */
	public static function info( string $text ): void
	{
		self::staticLog( $text, RunLevel::INFO );
	}

	/**
	 * @param string $text
	 */
	public static function warning( string $text ): void
	{
		self::staticLog( $text, RunLevel::WARNING );
	}

	/**
	 * @param string $text
	 */
	public static function error( string $text ): void
	{
		self::staticLog( $text, RunLevel::ERROR );
	}

	/**
	 * @param string $text
	 */
	public static function fatal( string $text ): void
	{
		self::staticLog( $text, RunLevel::FATAL );
	}

	public static function addChannel( string $name, ILogger $logger ) : void
	{
		$log = self::getInstance();
		$log->initIfNeeded();

		if( !array_key_exists( $name, $log->channels ) )
		{
			$log->channels[ $name ] = new LogMux();
			// Set the channel name on the LogMux
			if( method_exists( $log->channels[ $name ], 'setChannel' ) )
			{
				$log->channels[ $name ]->setChannel( $name );
			}
		}

		$log->channels[ $name ]->addLog( $logger );

		$log->serialize();
	}

	public static function channel( string $name ) : LogMux
	{
		$log = self::getInstance();
		$log->initIfNeeded();

		if( !array_key_exists( $name, $log->channels ) )
		{
			$log->channels[ $name ] = new LogMux();
			// Set the channel name on the LogMux
			if( method_exists( $log->channels[ $name ], 'setChannel' ) )
			{
				$log->channels[ $name ]->setChannel( $name );
			}
		}

		return $log->channels[ $name ];
	}

	public static function addFilter( Filter\IFilter $filter ) : void
	{
		$log = self::getInstance();
		$log->initIfNeeded();
		$log->logger->addFilter( $filter );
	}
}
