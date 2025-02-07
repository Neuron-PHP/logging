<?php

namespace Neuron\Log;

/**
 * DTO to pass log information internally.
 */

class Data
{
	public int $TimeStamp;
	public string $Text;
	public RunLevel $Level;
	public string $LevelText;
	public array $Context;

	/**
	 * @param int $TimeStamp
	 * @param string $Text
	 * @param RunLevel $Level
	 * @param string $LevelText
	 * @param array $Context
	 */

	public function __construct( int $TimeStamp, string $Text, RunLevel $Level, string $LevelText, array $Context )
	{
		$this->TimeStamp = $TimeStamp;
		$this->Text      = $Text;
		$this->Level     = $Level;
		$this->LevelText = $LevelText;
		$this->Context   = $Context;
	}
}
