<?php

namespace Neuron\Log;

use Exception;

/**
 * Single Logger implementation.
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Logger implements ILogger
{
	private RunLevel             			$_runLevel = RunLevel::ERROR;
	private Destination\DestinationBase $_destination;
	private array           				$_context = [];

	/**
	 * @param Destination\DestinationBase $dest
	 */
	public function __construct( Destination\DestinationBase $dest )
	{
		$this->setDestination( $dest );

		$this->addFilter( new Filter\RunLevel() );
	}

	public function addFilter( Filter\FilterBase $filter ): bool
	{
		$filter->setParent( $this );
		return $this->_destination->addFilter( $filter );
	}

	public function removeFilter( Filter\IFilter $filter ): bool
	{
		return $this->_destination->removeFilter( $filter );
	}

	public function setContext( string $name, string $value ) : void
	{
		if( !$value )
		{
			unset( $this->_context[ $name ] );
			return;
		}

		$this->_context[ $name ] = $value;
	}

	public function getContext() : array
	{
		return $this->_context;
	}

	/**
	 * @param Destination\DestinationBase $dest
	 */
	public function setDestination( Destination\DestinationBase $dest ): void
	{
		$dest->setParent( $this );

		$this->_destination = $dest;
	}

	/**
	 * @return mixed
	 */
	public function getDestination(): Destination\DestinationBase
	{
		return $this->_destination;
	}

	/**
	 * @param string $level
	 * @throws Exception
	 */
	public function setRunLevelText( string $level ): void
	{
		$intLevel = RunLevel::DEBUG;

		switch( strtolower( $level ) )
		{
			case 'debug':
				break;

			case 'info':
				$intLevel = RunLevel::INFO;
				break;

			case 'warning':
				$intLevel = RunLevel::WARNING;
				break;

			case 'error':
				$intLevel = RunLevel::ERROR;
				break;

			case 'fatal':
				$intLevel = RunLevel::FATAL;
				break;

			default:
				throw new Exception( "Unrecognized run level '$level'" );
		}

		$this->setRunLevel( $intLevel );
	}

	/**
	 * @param $level string|int either the run level or a string representation of it.
	 * @throws Exception
	 */
	public function setRunLevel( mixed $level ): void
	{
		if( is_string( $level ) )
		{
			$this->setRunLevelText( $level );
			return;
		}

		$this->_runLevel = $level;
	}

	/**
	 * @return RunLevel
	 */
	public function getRunLevel() : RunLevel
	{
		return $this->_runLevel;
	}

	/**
	 * @return mixed
	 */
	public function open( array $params ): mixed
	{
		return $this->getDestination()->open( $params );
	}

	/**
	 *
	 */
	public function close(): void
	{
		$this->getDestination()->close();
	}

	/**
	 * @param string $text
	 * @param RunLevel $level
	 */
	public function log( string $text, RunLevel $level ): void
	{
		$this->getDestination()->log( $text, $level );
	}

	/**
	 * @param string $text
	 */
	public function debug( string $text ): void
	{
		$this->log( $text, RunLevel::DEBUG );
	}

	/**
	 * @param string $text
	 */
	public function info( string $text ): void
	{
		$this->log( $text, RunLevel::INFO );
	}

	/**
	 * @param string $text
	 */
	public function warning( string $text ): void
	{
		$this->log( $text, RunLevel::WARNING );
	}

	/**
	 * @param string $text
	 */
	public function error( string $text ): void
	{
		$this->log( $text, RunLevel::ERROR );
	}

	/**
	 * @param string  $text
	 */
	public function fatal( string $text ): void
	{
		$this->log( $text, RunLevel::FATAL );
	}

	public function reset(): void
	{
	}
}

