<?php

namespace Neuron\Log;

/**
 * Logger interface.
 */

interface ILogger
{

	/**
	 * @param string $text
	 * @param RunLevel $level
	 */
	public function log( string $text, RunLevel $level ): void;

	/**
	 * @param int $level
	 * @return void
	 */
	public function setRunLevel( mixed $level ): void;

	/**
	 * Sets the run level by text.
	 * Valid values are: debug, info, warning, error, fatal
	 * @param string $level
	 */
	public function setRunLevelText( string $level );

	/**
	 * @return RunLevel
	 */
	public function getRunLevel() : RunLevel;

	/**
	 * @param string $text
	 * @return void
	 */
	public function debug( string $text ): void;

	/**
	 * @param string $text
	 * @return void
	 */
	public function info( string $text ): void;

	/**
	 * @param string $text
	 * @return void
	 */
	public function warning( string $text ): void;

	/**
	 * @param string $text
	 * @return void
	 */
	public function error( string $text ): void;

	/**
	 * @param string $text
	 * @return void
	 */
	public function fatal( string $text ): void;

	/**
	 * Add a context value to the log. Contexts are prepended to each log entry.
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public function setContext( string $name, string $value ) : void;

	/**
	 * @return array
	 */
	public function getContext() : array;

	public function reset(): void;
}
