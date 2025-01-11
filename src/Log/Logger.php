<?php

namespace Neuron\Log;

use Exception;

/**
 * Single Logger implementation.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Logger implements ILogger
{
	private int             				$_RunLevel = ILogger::ERROR;
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
	 * @param string $Text
	 * @throws Exception
	 */
	public function setRunLevelText( string $Text ): void
	{
		$Level = self::DEBUG;

		switch( strtolower( $Text ) )
		{
			case 'debug':
				break;

			case 'info':
				$Level = self::INFO;
				break;

			case 'warning':
				$Level = self::WARNING;
				break;

			case 'error':
				$Level = self::ERROR;
				break;

			case 'fatal':
				$Level = self::FATAL;
				break;

			default:
				throw new Exception( "Unrecognized run level '$Text'" );
		}

		$this->setRunLevel( $Level );
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

		$this->_RunLevel = (int)$Level;
	}

	/**
	 * @return int
	 */
	public function getRunLevel() : int
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
	 * @param int $Level
	 */
	public function log( string $Text, int $Level ): void
	{
		$this->getDestination()->log( $Text, $Level );
	}

	/**
	 * @param string $Text
	 */
	public function debug( string $Text ): void
	{
		$this->log( $Text, self::DEBUG );
	}

	/**
	 * @param string $Text
	 */
	public function info( string $Text ): void
	{
		$this->log( $Text, self::INFO );
	}

	/**
	 * @param string $Text
	 */
	public function warning( string $Text ): void
	{
		$this->log( $Text, self::WARNING );
	}

	/**
	 * @param string $Text
	 */
	public function error( string $Text ): void
	{
		$this->log( $Text, self::ERROR );
	}

	/**
	 * @param string  $Text
	 */
	public function fatal( string $Text ): void
	{
		$this->log( $Text, self::FATAL );
	}

	public function reset(): void
	{
	}
}

