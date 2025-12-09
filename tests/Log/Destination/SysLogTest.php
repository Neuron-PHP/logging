<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\SysLog;
use Neuron\Log\Format\PlainText;
use Neuron\Log\ILogger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class SysLogTest extends TestCase
{
	public function testLog()
	{
		$File = new SysLog( new PlainText() );

		$File->log( "Test", RunLevel::ERROR );

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

		$data = new Data(
			time(),
			'test',
			RunLevel::DEBUG,
			'info',
			[]
		);


		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteInfo()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::INFO,
			'info',
			[]
		);

		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteWarning()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::WARNING,
			'info',
			[]
		);


		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteError()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::ERROR,
			'info',
			[]
		);


		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteCritical()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::CRITICAL,
			'info',
			[]
		);

		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteNotice()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::NOTICE,
			'notice',
			[]
		);

		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteAlert()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::ALERT,
			'alert',
			[]
		);

		$File->write( "Test", $data );
		$this->assertTrue( true );
	}

	public function testWriteEmergency()
	{
		$File = new SysLog( new PlainText() );

		$data = new Data(
			time(),
			'test',
			RunLevel::EMERGENCY,
			'emergency',
			[]
		);

		$File->write( "Test", $data );
		$this->assertTrue( true );
	}
}
