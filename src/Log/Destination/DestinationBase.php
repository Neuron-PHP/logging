<?php

namespace Neuron\Log\Destination;

use \Neuron\Log;
use \Neuron\Log\Format;
use \Neuron\Log\Filter;
use Neuron\Log\ILogger;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Abstract base class for all logging destinations in the Neuron logging system.
 * 
 * This class provides the foundation for implementing various logging outputs
 * such as files, databases, remote services, or console output. It handles
 * common functionality like formatting, filtering, and parent logger relationships.
 * 
 * Concrete implementations must implement the write() method to define how
 * log data is actually output to their specific destination.
 * 
 * Key responsibilities:
 * - Manages log formatters and filters
 * - Handles parent logger relationships
 * - Provides standard file handles for STDOUT/STDERR
 * - Orchestrates the filtering and formatting pipeline
 * - Defines the contract for destination-specific writing
 * 
 * @package Neuron\Log\Destination
 * 
 * @example
 * ```php
 * class CustomDestination extends DestinationBase
 * {
 *     protected function write(string $text, Log\Data $data): void
 *     {
 *         // Custom implementation for writing log data
 *         file_put_contents('/var/log/custom.log', $text, FILE_APPEND);
 *     }
 * }
 * ```
 */

abstract class DestinationBase
{
	private Format\IFormat $_format;
	private array    $_filters = [];
	private ?ILogger $_parent  = null;
	private mixed $_stdOut;
	private mixed $_stdErr;

	/**
	 * @param Format\IFormat $format
	 */
	public function __construct( Format\IFormat $format )
	{
		$this->setFileHandles();
		$this->setFormat( $format );
	}

	/**
	 * Maps STDERR and STDOUT to file handles in non-CLI environments.
	 * @return void
	 */
	public function setFileHandles(): void
	{
		$this->_stdErr = fopen( 'php://stderr', 'w' );
		$this->_stdOut = fopen( 'php://stdout', 'w' );
	}

	/**
	 * @return mixed
	 */
	public function getStdOut()
	{
		return $this->_stdOut;
	}

	/**
	 * @return mixed
	 */
	public function getStdErr()
	{
		return $this->_stdErr;
	}

	/**
	 * Sets the parent logger.
	 *
	 * @param ILogger $logger
	 */
	public function setParent( ILogger $logger ) : void
	{
		$this->_parent = $logger;
	}

	/**
	 * Gets the parent logger.
	 *
	 * @return ILogger
	 */
	public function getParent() : ?ILogger
	{
		return $this->_parent;
	}

	/**
	 * Sets the formatter.
	 *
	 * @param Format\IFormat $format
	 */

	public function setFormat( Format\IFormat $format ): void
	{
		$this->_format = $format;
	}

	/**
	 * Adds a logging filter.
	 *
	 * @param Filter\IFilter $filter
	 * @return bool
	 */
	public function addFilter( Filter\IFilter $filter ): bool
	{
		$this->_filters[] = $filter;
		return true;
	}

	/**
	 * Removes a logging filter.
	 *
	 * @param Filter\IFilter $removeFilter
	 * @return bool
	 */
	public function removeFilter( Filter\IFilter $removeFilter ): bool
	{
		$beforeSize = count( $this->_filters );
		$this->_filters = array_filter( $this->_filters, function ($filter) use ($removeFilter)
			{
				// Use strict comparison to ensure exact object match
				return $filter !== $removeFilter;
			}
		);

		return count( $this->_filters ) < $beforeSize;
	}

	/**
	 * Writes the formatted log data to the specific destination.
	 * 
	 * This abstract method must be implemented by concrete destination classes
	 * to define how log data is actually written to their specific output target.
	 * The text has already been formatted and filtered when this method is called.
	 *
	 * @param string $text The formatted log message ready for output
	 * @param Log\Data $data The complete log data object containing metadata
	 * @return void
	 */

	protected abstract function write( string $text, Log\Data $data ): void;

	/**
	 * Opens the destination. Destinations may require parameters to be passed in.
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param array $params
	 * @return mixed
	 */

	public function open( array $params ): bool
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
	 * @param $text - Output that has been run through the formatter.
	 * @param $level - Text output level.
	 * @param $context - Optional context array
	 */

	public function log( string $text, Log\RunLevel $level, array $context = [] ): void
	{
		// Get channel from parent logger if available
		$channel = null;
		if( $this->getParent() && method_exists( $this->getParent(), 'getChannel' ) )
		{
			$channel = $this->getParent()->getChannel();
		}

		// Merge global context with per-call context
		$mergedContext = $this->getParent() ? $this->getParent()->getContext() : [];

		// Per-call context takes precedence
		$mergedContext = array_merge( $mergedContext, $context );

		$log = new Log\Data(
			time(),
			$text,
			$level,
			$level->getLevel(),
			$mergedContext,
			$channel
		);

		foreach( $this->_filters as $filter )
		{
			$log = $filter->filter( $log );
			if( !$log )
				return;
		}

		$text = $this->_format->format( $log );

		$this->write( $text, $log );
	}
}
