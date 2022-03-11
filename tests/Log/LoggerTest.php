<?php

class LoggerTest extends PHPUnit\Framework\TestCase
{
	public $_Logger;

	public function setUp() : void
	{
		$this->_Logger = new Neuron\Log\Logger(
			new Neuron\Log\Destination\Echoer(
				new Neuron\Log\Format\PlainText()
			)
		);
	}

	public function testSetRunLevelTextPass()
	{
		$this->_Logger->setRunLevel( 'info' );

		$this->assertEquals(
			$this->_Logger->getRunLevel(),
			Neuron\Log\ILogger::INFO
		);
	}

	public function testSetRunLevelFail()
	{
		$Failed = false;

		try
		{
			$this->_Logger->setRunLevel( 'fail' );
		}
		catch( Exception $Exception )
		{
			$Failed = true;
		}

		$this->assertTrue( $Failed );
	}

	public function testPass()
	{
		$this->_Logger->setRunLevel( Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->log( $test, Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testFail()
	{
		$this->_Logger->setRunLevel( Neuron\Log\ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->log( $test, Neuron\Log\ILogger::DEBUG );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );

	}
}
