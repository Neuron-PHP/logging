<?php

use Neuron\Log\Destination\SysLog;
use PHPUnit\Framework\TestCase;

class SysLogTest extends TestCase
{
	public function testLog()
	{
		$File = new SysLog( new \Neuron\Log\Format\PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}
}
