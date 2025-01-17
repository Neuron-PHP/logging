<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\SysLog;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use PHPUnit\Framework\TestCase;

class SysLogTest extends TestCase
{
	public function testLog()
	{
		$File = new SysLog( new PlainText() );

		$File->log( "Test", \Neuron\Log\ILogger::ERROR );

		$this->assertTrue( true );
	}

	public function testOpen()
	{
		$File = new SysLog( new PlainText() );

		$this->assertTrue( $File->open( [] ) );
	}

	public function testClose()
	{
		$File = new SysLog( new PlainText() );
		$File->close();
		$this->assertTrue( true );
	}

	public function testWriteDebug()
	{
		$File = new SysLog( new PlainText() );

		$Data = new Data(
			time(),
			'test',
			ILogger::DEBUG,
			'info',
			[]
		);


		$File->write( "Test", $Data );
		$this->assertTrue( true );
	}

	public function testWriteInfo()
	{
		$File = new SysLog( new PlainText() );

		$Data = new Data(
			time(),
			'test',
			ILogger::INFO,
			'info',
			[]
		);

		$File->write( "Test", $Data );
		$this->assertTrue( true );
	}

	public function testWriteWarning()
	{
		$File = new SysLog( new PlainText() );

		$Data = new Data(
			time(),
			'test',
			ILogger::WARNING,
			'info',
			[]
		);


		$File->write( "Test", $Data );
		$this->assertTrue( true );
	}

	public function testWriteError()
	{
		$File = new SysLog( new PlainText() );

		$Data = new Data(
			time(),
			'test',
			ILogger::ERROR,
			'info',
			[]
		);


		$File->write( "Test", $Data );
		$this->assertTrue( true );
	}

	public function testWriteFatal()
	{
		$File = new SysLog( new PlainText() );

		$Data = new Data(
			time(),
			'test',
			ILogger::FATAL,
			'info',
			[]
		);

		$File->write( "Test", $Data );
		$this->assertTrue( true );
	}
}
