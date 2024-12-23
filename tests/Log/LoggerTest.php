<?php
namespace Tests\Log;
use Exception;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
	public $_Logger;

	public function setUp() : void
	{
		$this->_Logger = new \Neuron\Log\Logger(
			new \Neuron\Log\Destination\Echoer(
				new \Neuron\Log\Format\PlainText()
			)
		);
	}

	/**
	 * @throws Exception
	 */
	public function testSetRunLevelTextPass()
	{
		$this->_Logger->setRunLevel( 'info' );

		$this->assertEquals(
			$this->_Logger->getRunLevel(),
			\Neuron\Log\ILogger::INFO
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

	public function testDebugPass()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->log( $test, \Neuron\Log\ILogger::INFO,);

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testInfoPass()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->info( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testWarningPass()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->warning( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testErrorPass()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testFatalPass()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->fatal( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testFail()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->log( $test, \Neuron\Log\ILogger::DEBUG );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );
	}

	public function testSingleContext()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		ob_start();

		$this->_Logger->log( $test, \Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1) ".$test."\r\n", $s );
	}

	public function testMultipleContext()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		ob_start();

		$this->_Logger->log( $test, \Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1|SessionId=2) ".$test."\r\n", $s );
	}

	/**
	 * @throws Exception
	 */
	public function testRemoveContext()
	{
		$this->_Logger->setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		$this->_Logger->setContext( "UserId", '' );
		ob_start();

		$this->_Logger->log( $test, \Neuron\Log\ILogger::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(SessionId=2) ".$test."\r\n", $s );
	}
}
