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
	 * @param array $context Optional context array
	 */
	public function log( string $text, RunLevel $level, array $context = [] ): void;

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
	 * @param array $context Optional context array
	 * @return void
	 */
	public function debug( string $text, array $context = [] ): void;

	/**
	 * @param string $text
	 * @param array $context Optional context array
	 * @return void
	 */
	public function info( string $text, array $context = [] ): void;

	/**
	 * @param string $text
	 * @param array $context Optional context array
	 * @return void
	 */
	public function warning( string $text, array $context = [] ): void;

	/**
	 * @param string $text
	 * @param array $context Optional context array
	 * @return void
	 */
	public function error( string $text, array $context = [] ): void;

	/**
	 * @param string $text
	 * @param array $context Optional context array
	 * @return void
	 */
	public function fatal( string $text, array $context = [] ): void;

	/**
	 * Add a context value to the log. Contexts are prepended to each log entry.
	 *
	 * @param string $name
	 * @param mixed $value Can be string, array, or other scalar/object types
	 * @return void
	 */
	public function setContext( string $name, mixed $value ) : void;

	/**
	 * @return array
	 */
	public function getContext() : array;

	public function reset(): void;
}
