<?php
namespace Tests\Log;
use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Format\Raw;
use Neuron\Log\ILogger;
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;
use Tests\Log\Filter\ExampleFilter;

class LogSingletonTest extends TestCase
{
	public function setUp() : void
	{
		parent::setUp();
	}

	public function testSetLevelAsString()
	{
		Log::setRunLevel( 'DEBUG' );
		$this->assertEquals( RunLevel::DEBUG, Log::getRunLevel() );
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
		$Plain = new Echoer(
			new PlainText()
		);

		$PlainLog = new Logger( $Plain );
		$PlainLog->setRunLevel( RunLevel::INFO );
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
		Log::setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		Log::staticLog( $test, RunLevel::INFO );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testFail()
	{
		Log::setRunLevel( RunLevel::INFO );
		$test = 'this is a test';

		ob_start();

		Log::staticLog( $test, RunLevel::DEBUG );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $str == '' );
	}

	public function testDebug()
	{
		Log::setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		Log::debug( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testInfo()
	{
		Log::setRunLevel( RunLevel::INFO );
		$test = 'this is a test';

		ob_start();

		Log::info( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testWarning()
	{
		Log::setRunLevel( RunLevel::WARNING );
		$test = 'this is a test';

		ob_start();

		Log::warning( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testError()
	{
		Log::setRunLevel( RunLevel::ERROR );
		$test = 'this is a test';

		ob_start();

		Log::error( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	public function testFatal()
	{
		Log::setRunLevel( RunLevel::FATAL );
		$test = 'this is a test';

		ob_start();

		Log::fatal( $test );

		$str = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( (bool)strstr( $str, $test ) );
	}

	/**
	 * @throws \Exception
	 */
	public function testAddChannel()
	{
		Log::setRunLevel( RunLevel::INFO );

		$test = 'this is a test';

		$Plain = new Echoer(
			new Raw()
		);

		$PlainLog = new Logger( $Plain );
		$PlainLog->setRunLevel( RunLevel::INFO );
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

	public function testAddFilter()
	{
		Log::addFilter( new ExampleFilter() );
		$this->assertTrue( true );
	}
}
