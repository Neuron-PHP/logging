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
	public function log( string $Text, int $Level ): void;

	/**
	 * @param int $Level
	 * @return void
	 */
	public function setRunLevel( mixed $Level ): void;

	/**
	 * @return int
	 */
	public function getRunLevel() : int;

	/**
	 * @param string $Text
	 * @return void
	 */
	public function debug( string $Text ): void;

	/**
	 * @param string $Text
	 * @return void
	 */
	public function info( string $Text ): void;

	/**
	 * @param string $Text
	 * @return void
	 */
	public function warning( string $Text ): void;

	/**
	 * @param string $Text
	 * @return void
	 */
	public function error( string $Text ): void;

	/**
	 * @param string $Text
	 * @return void
	 */
	public function fatal( string $Text ): void;

	/**
	 * @param string $Name
	 * @param string $Value
	 * @return void
	 */
	public function setContext( string $Name, string $Value ) : void;
}
