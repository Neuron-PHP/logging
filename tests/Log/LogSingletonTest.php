<?php
namespace Tests\Log;
use Neuron\Log\Log;
use PHPUnit\Framework\TestCase;

class LogSingletonTest extends TestCase
{
	public function setUp() : void
	{
		parent::setUp();
	}

	public function testContext()
	{
		$test = 'this is a test';

		Log::setContext( 'UserId', 1 );
		ob_start();

		Log::error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1) ".$test."\r\n", $s );
	}

	public function testChannelContext()
	{
		$Plain = new \Neuron\Log\Destination\Echoer(
			new \Neuron\Log\Format\PlainText()
		);

		$PlainLog = new \Neuron\Log\Logger( $Plain );
		$PlainLog->setRunLevel( \Neuron\Log\ILogger::INFO );
		Log::addChannel( 'Test', $PlainLog );

		$test = 'this is a test';

		Log::setContext( 'UserId', 1 );
		ob_start();

		Log::getChannel( 'Test' )->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "(UserId=1) ".$test."\r\n", $s );

	}
	public function testPass()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		Log::staticLog( $test, \Neuron\Log\ILogger::INFO );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testFail()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		Log::staticLog( $test, \Neuron\Log\ILogger::DEBUG );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $str == '' );
	}

	public function testDebug()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::DEBUG );
		$test = 'this is a test';

		ob_start();

		Log::debug( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $str, $test ) ? true : false );
	}

	public function testInfo()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		Log::info( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $str, $test ) ? true : false );
	}

	public function testWarning()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::WARNING );
		$test = 'this is a test';

		ob_start();

		Log::warning( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $str, $test ) ? true : false );
	}

	public function testError()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::ERROR );
		$test = 'this is a test';

		ob_start();

		Log::error( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $str, $test ) ? true : false );
	}

	public function testFatal()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::FATAL );
		$test = 'this is a test';

		ob_start();

		Log::fatal( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( strstr( $str, $test ) ? true : false );
	}

	public function testAddChannel()
	{
		Log::setRunLevel( \Neuron\Log\ILogger::INFO );

		$test = 'this is a test';

		$Plain = new \Neuron\Log\Destination\Echoer(
			new \Neuron\Log\Format\Raw()
		);

		$PlainLog = new \Neuron\Log\Logger( $Plain );
		$PlainLog->setRunLevel( \Neuron\Log\ILogger::INFO );
		Log::addChannel( 'RealTime', $PlainLog );

		ob_start();

		Log::getChannel( 'RealTime' )->info( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( $test, $str );
	}

	public function testMissingChannel()
	{
		$this->assertNotNull(
			Log::getChannel( 'Missing' )
		);
	}
}
