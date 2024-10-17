<?php

namespace Tests\Log;
use PHPUnit\Framework\TestCase;

class LogMuxTest extends TestCase
{
	public $_Mux;

	public function setUp() : void
	{
		$this->_Mux = new \Neuron\Log\LogMux();

		$this->_Mux->addLog(
			new \Neuron\Log\Logger(
				new \Neuron\Log\Destination\Echoer(
					new \Neuron\Log\Format\PlainText( false )
				)
			),
			\Neuron\Log\ILogger::INFO
		);

		$this->_Mux->addLog(
			new \Neuron\Log\Logger(
				new \Neuron\Log\Destination\Echoer(
					new \Neuron\Log\Format\JSON()
				)
			),
			\Neuron\Log\ILogger::WARNING
		);

	}

	public function testPass()
	{
		$this->_Mux->setRunLevel( \Neuron\Log\ILogger::DEBUG );
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
		$this->_Mux->setRunLevel( \Neuron\Log\ILogger::INFO );
		$test = 'this is a test';

		ob_start();

		$this->_Mux->debug( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertTrue( $s == '' );
	}

	public function testAddFilter()
	{
		$this->assertTrue( $this->_Mux->addFilter( new \Tests\Log\Filter\ExampleFilter() ) );
	}

	public function testRemoveFilter()
	{
		$Filter = new \Tests\Log\Filter\ExampleFilter();
		$this->_Mux->addFilter( $Filter );

		$this->assertTrue( $this->_Mux->removeFilter( $Filter ) );
	}
	
	public function testFilter()
	{
		$Filter = new \Tests\Log\Filter\ExampleFilter();
		$this->_Mux->addFilter( $Filter );

		$test = 'this is a test';

		ob_start();

		$this->_Mux->error( $test );

		$s = ob_get_contents();

		ob_end_clean();

		$this->assertStringContainsString(  'testing', $s );
	}
}
