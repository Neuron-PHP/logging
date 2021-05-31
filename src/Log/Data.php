<?php

namespace Neuron\Log;

/**
 * Class Data
 * @package Neuron\Log
 */

class Data
{
	public int $TimeStamp;
	public string $Text;
	public int $Level;
	public string $LevelText;

	/**
	 * @param int $TimeStamp
	 * @param string $Text
	 * @param int $Level
	 * @param string $LevelText
	 */

	public function __construct( int $TimeStamp, string $Text, int $Level, string $LevelText )
	{
		$this->TimeStamp = $TimeStamp;
		$this->Text      = $Text;
		$this->Level     = $Level;
		$this->LevelText = $LevelText;
	}
}
