<?php

namespace Neuron\Log\Destination;

use \Neuron\Log;
use \Neuron\Log\Format;
use \Neuron\Log\Filter;
use Neuron\Log\ILogger;

/**
 * Abstract base class for log destinations.
 */

abstract class DestinationBase
{
	private Format\IFormat $_Format;
	private array    $_Filters = [];
	private ?ILogger $_Parent  = null;


	/**
	 * @param Format\IFormat $Format
	 */

	public function __construct( Format\IFormat $Format )
	{
		$this->setFileHandles();
		$this->setFormat( $Format );
	}

	/**
	 * Maps STDERR and STDOUT to file handles in non-CLI environments.
	 * @return void
	 */
	public function setFileHandles(): void
	{
		if( !defined( 'STDERR' ) )
		{
			define( 'STDERR', fopen( 'php://stderr', 'w' ) );
		}

		if( !defined( 'STDOUT' ) )
		{
			define( 'STDOUT', fopen( 'php://stdout', 'w' ) );
		}
	}


	/**
	 * Sets the parent logger.
	 *
	 * @param ILogger $Logger
	 */
	public function setParent( ILogger $Logger ) : void
	{
		$this->_Parent = $Logger;
	}

	/**
	 * Gets the parent logger.
	 *
	 * @return ILogger
	 */
	public function getParent() : ?ILogger
	{
		return $this->_Parent;
	}

	/**
	 * Gets the text for a log level.
	 * @param $Level
	 * @return string
	 */

	public function getLevelText( int $Level ): string
	{
		switch( $Level )
		{
			case Log\ILogger::DEBUG:
				return "Debug";

			case Log\ILogger::INFO:
				return "Info";

			case Log\ILogger::WARNING:
				return "Warning";

			case Log\ILogger::ERROR:
				return "Error";

			case Log\ILogger::FATAL:
				return "Fatal";

			default:
				return "Unknown";
		}
	}

	/**
	 * Sets the formatter.
	 *
	 * @param Format\IFormat $Format
	 */

	public function setFormat( Format\IFormat $Format ): void
	{
		$this->_Format = $Format;
	}

	/**
	 * Adds a logging filter.
	 *
	 * @param Filter\IFilter $Filter
	 * @return bool
	 */
	public function addFilter( Filter\IFilter $Filter ): bool
	{
		$this->_Filters[] = $Filter;
		return true;
	}

	/**
	 * Removes a logging filter.
	 *
	 * @param Filter\IFilter $RemoveFilter
	 * @return bool
	 */
	public function removeFilter( Filter\IFilter $RemoveFilter ): bool
	{
		$BeforeSize = count( $this->_Filters );
		$this->_Filters = array_filter( $this->_Filters, function ($filter) use ($RemoveFilter)
			{
				// Use strict comparison to ensure exact object match
				return $filter !== $RemoveFilter;
			}
		);

		return count( $this->_Filters ) < $BeforeSize;
	}

	/**
	 * Writes the log data to the destination.
	 *
	 * @param $Text - Text m
	 * @param Log\Data $Data
	 * @return mixed
	 */

	protected abstract function write( string $Text, Log\Data $Data ): void;

	/**
	 * Opens the destination. Destinations may require parameters to be passed in.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param array $Params
	 * @return mixed
	 */

	public function open( array $Params ): bool
	{
		return true;
	}

	/**
	 * Closes the destination if required.
	 *
	 * @return void
	 */
	public function close(): void
	{}

	/**
	 * Handles writing the log data after filtering and formatting.
	 *
	 * @param $Text - Output that has been run through the formatter.
	 * @param $Level - Text output level.
	 */

	public function log( string $Text, int $Level ): void
	{
		$Log = new Log\Data(
			time(),
			$Text,
			$Level,
			$this->getLevelText( $Level ),
			$this->getParent() ? $this->getParent()->getContext() : []
		);

		foreach( $this->_Filters as $Filter )
		{
			$Log = $Filter->filter( $Log );
			if( !$Log )
				return;
		}

		$Text = $this->_Format->format( $Log );

		$this->write( $Text, $Log );
	}
}
