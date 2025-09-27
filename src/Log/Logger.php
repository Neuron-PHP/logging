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
	private ?string              			$channel = null;

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

	public function setContext( string $name, mixed $value ) : void
	{
		if( $value === null || $value === '' )
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
	 * Set the channel name for this logger.
	 *
	 * @param string|null $channel
	 * @return void
	 */
	public function setChannel( ?string $channel ): void
	{
		$this->channel = $channel;
	}

	/**
	 * Get the channel name for this logger.
	 *
	 * @return string|null
	 */
	public function getChannel(): ?string
	{
		return $this->channel;
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
	 * Interpolates context values into the message placeholders.
	 * Implements PSR-3 style message interpolation.
	 *
	 * @param string $message
	 * @param array $context
	 * @return string
	 */
	private function interpolate( string $message, array $context ): string
	{
		$replace = [];
		foreach( $context as $key => $val )
		{
			// Only interpolate scalar values and objects with __toString
			if( !is_array( $val ) && (!is_object( $val ) || method_exists( $val, '__toString' )) )
			{
				$replace['{' . $key . '}'] = (string) $val;
			}
		}
		return strtr( $message, $replace );
	}

	/**
	 * @param string $text
	 * @param RunLevel $level
	 * @param array $context
	 */
	public function log( string $text, RunLevel $level, array $context = [] ): void
	{
		// Interpolate context values into message
		if( !empty( $context ) )
		{
			$text = $this->interpolate( $text, $context );
		}

		$this->getDestination()->log( $text, $level, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */
	public function debug( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::DEBUG, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */
	public function info( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::INFO, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */
	public function warning( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::WARNING, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */
	public function error( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::ERROR, $context );
	}

	/**
	 * @param string $text
	 * @param array $context
	 */
	public function fatal( string $text, array $context = [] ): void
	{
		$this->log( $text, RunLevel::FATAL, $context );
	}

	public function reset(): void
	{
	}
}

