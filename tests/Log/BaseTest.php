<?php

namespace Tests\Log;

use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\Raw;
use Neuron\Log\Base;
use Neuron\Log\Logger;
use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
	public Base $Logger;
	public function setUp(): void
	{
		$this->Logger = new Base(
			new Logger(
				new Echoer(
					new Raw()
				)
			)
		);
	}

	public function testSetRunLevelTextPass()
	{

		$this->Logger->setRunLevel( 'info' );

		$this->assertEquals(
			$this->Logger->getRunLevel(),
			\Neuron\Log\ILogger::INFO
		);
	}

	public function testDebugPass()
	{
		$this->Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->Logger->log( $test, \Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testInfoPass()
	{
		$this->Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->Logger->info( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testWarningPass()
	{
		$this->Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->Logger->warning( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testErrorPass()
	{
		$this->Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->Logger->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testFatalPass()
	{
		$this->Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->Logger->fatal( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

}
