<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\Raw;
use PHPUnit\Framework\TestCase;

class SlackTest extends TestCase
{
	public function testOpenPass()
	{
		$Slack = new Slack( new Raw() );

		$Pass = $Slack->open(
				[
					'endpoint' => 'https://test.com',
					'params' => []
				]
			);

		$this->assertTrue( $Pass );

		$Slack->log( "Test", \Neuron\Log\ILogger::ERROR );

		$Slack->close();
	}

	public function testOpenFail()
	{
		$Pass = true;

		$Slack = new Slack( new Raw() );

		try
		{
			$Pass = $Slack->open(
				[
					'endpoint' => 'fail',
					'params' => []
				]
			);
		}
		catch( \Exception $e )
		{
			$Pass = false;
		}

		$this->assertFalse( $Pass );

		$Slack->close();
	}
}
