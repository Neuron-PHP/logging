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

	public function testRemoveContextWithNull()
	{
		$this->_Logger->setContext( 'UserId', 1 );
		$this->_Logger->setContext( 'UserId', null );

		$context = $this->_Logger->getContext();
		$this->assertArrayNotHasKey( 'UserId', $context );
	}

	public function testSetChannel()
	{
		$this->_Logger->setChannel( 'test-channel' );
		$this->assertEquals( 'test-channel', $this->_Logger->getChannel() );
	}

	public function testSetChannelNull()
	{
		$this->_Logger->setChannel( 'test-channel' );
		$this->_Logger->setChannel( null );
		$this->assertNull( $this->_Logger->getChannel() );
	}

	public function testEmergency()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'emergency message';

		ob_start();
		$this->_Logger->emergency( $test );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( $test, $s );
	}

	public function testAlert()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'alert message';

		ob_start();
		$this->_Logger->alert( $test );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( $test, $s );
	}

	public function testNotice()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );
		$test = 'notice message';

		ob_start();
		$this->_Logger->notice( $test );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( $test, $s );
	}

	public function testMessageInterpolation()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );

		ob_start();
		$this->_Logger->info( 'User {username} logged in from {ip}', [
			'username' => 'john_doe',
			'ip' => '192.168.1.1'
		] );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'User john_doe logged in from 192.168.1.1', $s );
	}

	public function testMessageInterpolationWithNonScalar()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );

		ob_start();
		$this->_Logger->info( 'Data: {data}', [
			'data' => [ 'key' => 'value' ] // Array should not be interpolated
		] );
		$s = ob_get_contents();
		ob_end_clean();

		// Should contain the original placeholder since arrays aren't interpolated
		$this->assertStringContainsString( 'Data: {data}', $s );
	}

	public function testMessageInterpolationWithStringableObject()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );

		$stringable = new class {
			public function __toString(): string
			{
				return 'StringableValue';
			}
		};

		ob_start();
		$this->_Logger->info( 'Object: {obj}', [ 'obj' => $stringable ] );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'Object: StringableValue', $s );
	}

	public function testSetRunLevelWithString()
	{
		$this->_Logger->setRunLevel( 'info' );
		$this->assertEquals( RunLevel::INFO, $this->_Logger->getRunLevel() );

		$this->_Logger->setRunLevel( 'warning' );
		$this->assertEquals( RunLevel::WARNING, $this->_Logger->getRunLevel() );
	}

	public function testDebugWithContext()
	{
		$this->_Logger->setRunLevel( RunLevel::DEBUG );

		ob_start();
		$this->_Logger->debug( 'Debug {msg}', [ 'msg' => 'test' ] );
		$s = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( 'Debug test', $s );
	}
}
