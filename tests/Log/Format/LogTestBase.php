<?php
namespace Tests\Log\Format;

use PHPUnit\Framework\TestCase;

class LogTestBase extends TestCase
{
	const INPUT = 'Test log.';
	public $Data;

	public function setUp() : void
	{
		$this->Data = new \Neuron\Log\Data( time(), self::INPUT, \Neuron\Log\ILogger::DEBUG, 'DEBUG' );
	}
}
