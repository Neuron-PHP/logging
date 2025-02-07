<?php

namespace Neuron\Log;

use Exception;

/**
 * Single Logger implementation.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Logger implements ILogger
{
	private RunLevel             			$_RunLevel = RunLevel::ERROR;
	private Destination\DestinationBase $_Destination;
	private array           				$_Context = [];

	/**
	 * @param Destination\DestinationBase $Dest
	 */
	public function __construct( Destination\DestinationBase $Dest )
	{
		$this->setDestination( $Dest );

		$this->addFilter( new Filter\RunLevel() );
	}

	public function addFilter( Filter\FilterBase $Filter ): bool
	{
		$Filter->setParent( $this );
		return $this->_Destination->addFilter( $Filter );
	}

	public function removeFilter( Filter\IFilter $Filter ): bool
	{
		return $this->_Destination->removeFilter( $Filter );
	}

	public function setContext( string $Name, string $Value ) : void
	{
		if( !$Value )
		{
			unset( $this->_Context[ $Name ] );
			return;
		}

		$this->_Context[ $Name ] = $Value;
	}

	public function getContext() : array
	{
		return $this->_Context;
	}

	/**
	 * @param Destination\DestinationBase $Dest
	 */
	public function setDestination( Destination\DestinationBase $Dest ): void
	{
		$Dest->setParent( $this );

		$this->_Destination = $Dest;
	}

	/**
	 * @return mixed
	 */
	public function getDestination(): Destination\DestinationBase
	{
		return $this->_Destination;
	}

	/**
	 * @param string $Level
	 * @throws Exception
	 */
	public function setRunLevelText( string $Level ): void
	{
		$IntLevel = RunLevel::DEBUG;

		switch( strtolower( $Level ) )
		{
			case 'debug':
				break;

			case 'info':
				$IntLevel = RunLevel::INFO;
				break;

			case 'warning':
				$IntLevel = RunLevel::WARNING;
				break;

			case 'error':
				$IntLevel = RunLevel::ERROR;
				break;

			case 'fatal':
				$IntLevel = RunLevel::FATAL;
				break;

			default:
				throw new Exception( "Unrecognized run level '$Level'" );
		}

		$this->setRunLevel( $IntLevel );
	}

	/**
	 * @param $Level string|int either the run level or a string representation of it.
	 * @throws Exception
	 */
	public function setRunLevel( mixed $Level ): void
	{
		if( is_string( $Level ) )
		{
			$this->setRunLevelText( $Level );
			return;
		}

		$this->_RunLevel = $Level;
	}

	/**
	 * @return RunLevel
	 */
	public function getRunLevel() : RunLevel
	{
		return $this->_RunLevel;
	}

	/**
	 * @return mixed
	 */
	public function open( array $Params ): mixed
	{
		return $this->getDestination()->open( $Params );
	}

	/**
	 *
	 */
	public function close(): void
	{
		$this->getDestination()->close();
	}

	/**
	 * @param string $Text
	 * @param RunLevel $Level
	 */
	public function log( string $Text, RunLevel $Level ): void
	{
		$this->getDestination()->log( $Text, $Level );
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
	 * @param string  $Text
	 */
	public function fatal( string $Text ): void
	{
		$this->log( $Text, RunLevel::FATAL );
	}

	public function reset(): void
	{
	}
}

