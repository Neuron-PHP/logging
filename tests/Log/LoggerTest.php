<?php

class LoggerTest extends PHPUnit\Framework\TestCase
{
	public $_Logger;

	public function setUp() : void
	{
		$this->_Logger = new Neuron\Log\Logger(
			new Neuron\Log\Destination\Echoer(
				new Neuron\Log\Format\Raw()
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

	public function testSingleContext()
	{
		$this->_Logger->setRunLevel( Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		ob_start();

		$this->_Logger->log( $test, Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertEquals( "[UserId=1] ".$test."\r\n", $s );

	}

	public function testMultipleContext()
	{
		$this->_Logger->setRunLevel( Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		ob_start();

		$this->_Logger->log( $test, Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertEquals( "[UserId=1, SessionId=2] ".$test."\r\n", $s );
	}

	public function testRemoveContext()
	{
		$this->_Logger->setRunLevel( Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		$this->_Logger->setContext( "UserId", '' );
		ob_start();

		$this->_Logger->log( $test, Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertEquals( "[SessionId=2] ".$test."\r\n", $s );
	}

}
