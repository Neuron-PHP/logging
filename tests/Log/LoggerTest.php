<?php
namespace Tests\Log;
use Exception;
use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\Logger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
	public Logger $_Logger;

	public function setUp() : void
	{
		$this->_Logger = new Logger(
			new Echoer(
				new PlainText()
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
			RunLevel::INFO
		);
	}

	public function testSetRunLevelFail()
	{
		$failed = false;

		try
		{
			$this->_Logger->setRunLevel( 'fail' );
		}
		catch( Exception $exception )
		{
			$failed = true;
		}

		$this->assertTrue( $failed );
	}

	public function testDebug()
	{
		$Success = true;

		try
		{
			$this->_Logger->setRunLevel( 'debug' );
		}
		catch( Exception $exception )
		{
			$Success = false;
		}

		$this->assertTrue( $Success );

	}

	public function testWarning()
	{
		$Success = true;

		try
		{
			$this->_Logger->setRunLevel( 'warning' );
		}
		catch( Exception $exception )
		{
			$Success = false;
		}

		$this->assertTrue( $Success );

	}

	public function testError()
	{
		$Success = true;

		try
		{
			$this->_Logger->setRunLevel( 'error' );
		}
		catch( Exception $exception )
		{
			$Success = false;
		}

		$this->assertTrue( $Success );

	}

	public function testFatal()
	{
		$Success = true;

		try
		{
			$this->_Logger->setRunLevel( 'fatal' );
		}
		catch( Exception $exception )
		{
			$Success = false;
		}

		$this->assertTrue( $Success );

	}

	public function testDebugPass()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->debug( $test);

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $s, $test ) );
	}

	public function testInfoPass()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->info( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testWarningPass()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->warning( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testErrorPass()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testCriticalPass()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->critical( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $s, $test ) ? true : false );
	}

	public function testFail()
	{
		$this->_Logger->setRunLevel( RunLevel::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Logger->log( $test, RunLevel::DEBUG );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );
	}

	public function testOpenPass()
	{
		$this->assertTrue(
			$this->_Logger->open( [] )
		);
	}

	public function testResetPass()
	{
		$this->_Logger->reset();

		$this->assertTrue( true );
	}

	public function testClosePass()
	{
		$this->_Logger->close();

		$this->assertTrue( true );
	}

	public function testSingleContext()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		ob_start();

		$this->_Logger->log( $test, RunLevel::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1) ".$test."\r\n", $s );
	}

	public function testMultipleContext()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		ob_start();

		$this->_Logger->log( $test, RunLevel::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1|SessionId=2) ".$test."\r\n", $s );
	}

	/**
	 * @throws Exception
	 */
	public function testRemoveContext()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'SessionId', 2 );
		$this->_Logger->setContext( "UserId", '' );
		ob_start();

		$this->_Logger->log( $test, RunLevel::INFO );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(SessionId=2) ".$test."\r\n", $s );
		$this->assertStringNotContainsString( "UserId".$test."\r\n", $s );
	}
}
