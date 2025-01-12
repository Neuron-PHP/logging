<?php

namespace Tests\Log;
use Neuron\Log\Destination\Echoer;
use Neuron\Log\Format\JSON;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\Logger;
use Neuron\Log\LogMux;
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
			ILogger::INFO
		);

		$this->_Mux->addLog(
			new Logger(
				new Echoer(
					new JSON()
				)
			),
			ILogger::WARNING
		);

	}

	public function testPass()
	{
		$this->_Mux->setRunLevel( ILogger::DEBUG );
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

		$this->assertStringContainsString( "\"text\":\"$test\"", $s );
	}

	public function testFail()
	{
		$this->_Mux->setRunLevel( ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Mux->debug( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );
	}

	public function testAddFilter()
	{
		$this->assertTrue( $this->_Mux->addFilter( new ExampleFilter() ) );
	}

	public function testRemoveFilter()
	{
		$Filter = new ExampleFilter();
		$this->_Mux->addFilter( $Filter );

		$this->assertTrue( $this->_Mux->removeFilter( $Filter ) );
	}
	
	public function testFilter()
	{
		$Filter = new ExampleFilter();
		$this->_Mux->addFilter( $Filter );

		$test = 'this is a test';

		ob_start();

		$this->_Mux->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString(  'testing', $s );
	}
}
