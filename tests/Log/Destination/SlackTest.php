<?php

namespace Tests\Log\Destination;

use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\Raw;
use Neuron\Log\RunLevel;
use Neuron\Core\System\MemoryHttpClient;
use Neuron\Util\WebHook;
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

		$Slack->log( "Test", RunLevel::ERROR );

		$Slack->close();
	}

	public function testBeforeTheFirstWriteThereIsNoResponse()
	{
		$Slack = new Slack( new Raw() );

		$this->assertNull( $Slack->getLastResponse() );
		$this->assertFalse( $Slack->lastWriteSucceeded() );
	}

	public function testASuccessfulPostIsReportedAsSuch()
	{
		$Slack = $this->slackWith( function( MemoryHttpClient $Http ) {
			$Http->addSuccessResponse( '*hooks.slack.com*', 'ok', 200 );
		} );

		$Slack->log( "Test", RunLevel::ERROR );

		$this->assertTrue( $Slack->lastWriteSucceeded() );
		$this->assertSame( 200, (int)$Slack->getLastResponse()->getHttpCode() );
	}

	public function testAnHttpErrorIsReportedWithoutThrowing()
	{
		$Slack = $this->slackWith( function( MemoryHttpClient $Http ) {
			$Http->addSuccessResponse( '*hooks.slack.com*', 'rate limited', 429 );
		} );

		$Slack->log( "Test", RunLevel::ERROR );

		$this->assertFalse( $Slack->lastWriteSucceeded() );
		$this->assertSame( 429, (int)$Slack->getLastResponse()->getHttpCode() );
	}

	public function testATransportErrorIsReportedWithoutThrowing()
	{
		$Slack = $this->slackWith( function( MemoryHttpClient $Http ) {
			$Http->addErrorResponse( '*hooks.slack.com*', 'Could not resolve host', 6 );
		} );

		$Slack->log( "Test", RunLevel::ERROR );

		$this->assertFalse( $Slack->lastWriteSucceeded() );
		$this->assertSame( 6, (int)$Slack->getLastResponse()->getError() );
		$this->assertSame( 'Could not resolve host', $Slack->getLastResponse()->getErrorString() );
	}

	private function slackWith( callable $Configure ): Slack
	{
		$Http = new MemoryHttpClient();
		$Configure( $Http );

		$Slack = new Slack( new Raw() );
		$Slack->setWebHook( new WebHook( $Http ) );
		$Slack->open( [ 'endpoint' => 'https://hooks.slack.com/services/T/B/X', 'params' => [ 'channel' => '#t' ] ] );

		return $Slack;
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
