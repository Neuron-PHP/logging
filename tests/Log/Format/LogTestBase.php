<?php
namespace Tests\Log\Format;

use Neuron\Log\Data;
use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class LogTestBase extends TestCase
{
	const INPUT = 'Test log.';
	public Data $Data;

	public function setUp() : void
	{
		$this->Data = new Data(
			time(),
			self::INPUT,
			RunLevel::DEBUG,
			'DEBUG',
			[
				'context'
			]
		);
	}
}
