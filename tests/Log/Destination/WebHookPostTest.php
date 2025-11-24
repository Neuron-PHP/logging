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
		$post = new WebHookPost( new CSV() );

		$fail = false;
		try
		{
			$post->open(
				[
					'endpoint' => 'fail'
				]
			);
		}
		catch( \Exception $exception )
		{
			$fail = true;
		}

		$this->assertTrue( $fail );
	}

	public function testOpenPass()
	{
		$post = new WebHookPost( new CSV() );

		$fail = false;
		try
		{
			$post->open(
				[
					'endpoint' => 'http://www.example.org'
				]
			);
		}
		catch( \Exception $exception )
		{
			$fail = true;
		}

		$this->assertFalse( $fail );
	}

	public function testWrite()
	{
		$post = new WebHookPost( new CSV() );

		$fail = false;
		try
		{
			$post->open(
				[
					'endpoint' => 'http://www.example.org'
				]
			);
		}
		catch( \Exception $exception )
		{
			$fail = true;
		}

		$this->assertFalse( $fail );

		$data = new Data(
			time(),
			'test',
			RunLevel::INFO,
			'info',
			[]
		);

		$post->write( 'test', $data );
	}
}
