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
	public ?string $channel;

	/**
	 * @param int $timeStamp
	 * @param string $text
	 * @param RunLevel $level
	 * @param string $levelText
	 * @param array $context
	 * @param string|null $channel
	 */

	public function __construct( int $timeStamp, string $text, RunLevel $level, string $levelText, array $context, ?string $channel = null )
	{
		$this->timeStamp = $timeStamp;
		$this->text      = $text;
		$this->level     = $level;
		$this->levelText = $levelText;
		$this->context   = $context;
		$this->channel   = $channel;
	}
}
