<?php

namespace Neuron\Log;

/**
 * DTO to pass log information internally.
 */

class Data
{
	public int $timeStamp;
	public string $text;
	public RunLevel $level;
	public string $levelText;
	public array $context;

	/**
	 * @param int $timeStamp
	 * @param string $text
	 * @param RunLevel $level
	 * @param string $levelText
	 * @param array $context
	 */

	public function __construct( int $timeStamp, string $text, RunLevel $level, string $levelText, array $context )
	{
		$this->timeStamp = $timeStamp;
		$this->text      = $text;
		$this->level     = $level;
		$this->levelText = $levelText;
		$this->context   = $context;
	}
}
