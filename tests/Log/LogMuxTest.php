<?php

namespace Tests\Log;
use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\JSON;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\Logger;
use Neuron\Log\LogMux;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;
use Tests\Log\Filter\ExampleFilter;

class LogMuxTest extends TestCase
{
	public LogMux $_Mux;

	public function setUp() : void
	{
		$this->_Mux = new LogMux();

		$this->_Mux->addLog(
			new Logger(
				new Echoer(
					new PlainText( false )
				)
			),
			RunLevel::INFO
		);

		$this->_Mux->addLog(
			new Logger(
				new Echoer(
					new JSON()
				)
			),
			RunLevel::WARNING
		);

	}

	public function testInfoPass()
	{
		$this->_Mux->setRunLevel( RunLevel::DEBUG );
		$test = 'this is a test';

		ob_start();

		$this->_Mux->info( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( $test, $s );

		ob_start();

		$this->_Mux->warning( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "\"message\":\"$test\"", $s );
	}

	public function testFatalPass()
	{
		$this->_Mux->setRunLevel( 'debug' );
		$test = 'this is a test';

		ob_start();

		$this->_Mux->fatal( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( $test, $s );

		ob_start();

		$this->_Mux->warning( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString( "\"message\":\"$test\"", $s );
	}


	public function testFail()
	{
		$this->_Mux->setRunLevel( RunLevel::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Mux->debug( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );
	}

	public function testSetRunLevel()
	{
		$this->_Mux->setRunLevelText( 'info' );
		$this->assertEquals( RunLevel::INFO, $this->_Mux->getRunLevel() );
	}

	public function testGetContext()
	{
		$this->_Mux->setContext( 'test', 'testing' );
		$contexts = $this->_Mux->getContext( 'test', 'testing' );

		$this->assertStringContainsString( 'testing', $contexts[ 'test' ] );
	}

	public function testReset()
	{
		$this->_Mux->setContext( 'test', 'testing' );
		$this->_Mux->reset();

		$contexts = $this->_Mux->getContext( 'test', 'testing' );

		$this->assertEmpty( $contexts );
	}

	public function testAddFilter()
	{
		$this->assertTrue( $this->_Mux->addFilter( new ExampleFilter() ) );
	}

	public function testRemoveFilter()
	{
		$filter = new ExampleFilter();
		$this->_Mux->addFilter( $filter );

		$this->assertTrue( $this->_Mux->removeFilter( $filter ) );
	}
}
