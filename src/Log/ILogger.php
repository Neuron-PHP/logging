<?php

namespace Neuron\Log;

/**
 * Logger interface.
 */

interface ILogger
{
	const DEBUG   = 0;		// Log all
	const INFO    = 10;		// Log informational
	const WARNING = 20;		// Log warning
	const ERROR   = 30;		// Log error
	const FATAL   = 40;		// Log fatal

	/**
	 * @param string $Text
	 * @param int $Level
	 */
	public function log( string $Text, int $Level );

	/**
	 * @param int $Level
	 * @return mixed
	 */
	public function setRunLevel( mixed $Level );

	public function getRunLevel() : int;

	/**
	 * @param string $Text
	 * @return mixed
	 */
	public function debug( string $Text );

	/**
	 * @param string $Text
	 * @return mixed
	 */
	public function info( string $Text );

	/**
	 * @param string $Text
	 * @return mixed
	 */
	public function warning( string $Text );

	/**
	 * @param string $Text
	 * @return mixed
	 */
	public function error( string $Text );

	/**
	 * @param string $Text
	 * @return mixed
	 */
	public function fatal( string $Text );

	/**
	 * @param string $Name
	 * @param string $Value
	 * @return mixed
	 */
	public function setContext( string $Name, string $Value ) : void;
}
