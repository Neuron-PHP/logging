<?php

namespace Log;

use Neuron\Log\Format\Raw;
use Neuron\Log\Base;
use Neuron\Log\Destination\Memory;
use Neuron\Log\Logger;

class BaseTest extends \PHPUnit\Framework\TestCase
{
	public function testSetRunLevelTextPass()
	{
		$Logger = new Base(
			new Logger(
				new Memory(
					new Raw()
				)
			)
		);

		$Logger->setRunLevel( 'info' );

		$this->assertEquals(
			$Logger->getRunLevel(),
			\Neuron\Log\ILogger::INFO
		);
	}

}
