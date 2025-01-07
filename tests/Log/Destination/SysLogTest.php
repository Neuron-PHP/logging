<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\SysLog;
use Neuron\Log\Format\PlainText;
use PHPUnit\Framework\TestCase;

class SysLogTest extends TestCase
{
	public function testLog()
	{
		$File = new SysLog( new PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}
}
