<?php
namespace Tests\Log\Destination;

use Exception;
use Neuron\Log\Data;
use Neuron\Log\Destination\WebHookPost;
use Neuron\Log\Format\CSV;
use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class WebHookPostTest extends TestCase
{
	public function testOpenFail()
	{
		$Post = new WebHookPost( new CSV() );

		$Fail = false;
		try
		{
			$Post->open(
				[
					'endpoint' => 'fail'
				]
			);
		}
		catch( \Exception $Exception )
		{
			$Fail = true;
		}

		$this->assertTrue( $Fail );
	}

	public function testOpenPass()
	{
		$Post = new WebHookPost( new CSV() );

		$Fail = false;
		try
		{
			$Post->open(
				[
					'endpoint' => 'http://www.example.org'
				]
			);
		}
		catch( \Exception $Exception )
		{
			$Fail = true;
		}

		$this->assertFalse( $Fail );
	}

	public function testWrite()
	{
		$Post = new WebHookPost( new CSV() );

		$Fail = false;
		try
		{
			$Post->open(
				[
					'endpoint' => 'http://www.example.org'
				]
			);
		}
		catch( \Exception $Exception )
		{
			$Fail = true;
		}

		$this->assertFalse( $Fail );

		$Data = new Data(
			time(),
			'test',
			RunLevel::INFO,
			'info',
			[]
		);

		$Post->write( 'test', $Data );
	}
}
