<?php
namespace Tests\Log\Destination;

use Neuron\Log\Data;
use Neuron\Log\Destination\WebSocket;
use Neuron\Log\Format\PlainText;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;

class WebSocketTest extends TestCase
{
	public function testOpenRequiresUrl()
	{
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'WebSocket URL is required' );

		$ws = new WebSocket( new PlainText() );
		$ws->open( [] );
	}

	public function testOpenWithValidUrl()
	{
		$ws = new WebSocket( new PlainText() );

		// This will fail to connect but shouldn't throw an exception
		$result = $ws->open( [ 'url' => 'ws://localhost:9999' ] );

		// Connection will fail (no server running) but open should handle it gracefully
		$this->assertIsBool( $result );
	}

	public function testOpenWithCustomParameters()
	{
		$ws = new WebSocket( new PlainText() );

		$result = $ws->open( [
			'url' => 'ws://localhost:9999',
			'max_reconnect_attempts' => 3,
			'reconnect_delay' => 0.5
		] );

		$this->assertIsBool( $result );
	}

	public function testWriteHandlesDisconnectionGracefully()
	{
		$ws = new WebSocket( new PlainText() );
		$ws->open( [
			'url' => 'ws://localhost:9999',
			'max_reconnect_attempts' => 1,
			'reconnect_delay' => 0.1
		] );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::INFO,
			'INFO',
			[]
		);

		// This should not throw an exception even if connection fails
		$ws->write( 'Test message', $data );

		// No exception means the test passed
		$this->assertTrue( true );
	}

	public function testMultipleWritesWithoutConnection()
	{
		$ws = new WebSocket( new PlainText() );
		$ws->open( [
			'url' => 'ws://localhost:9999',
			'max_reconnect_attempts' => 1,
			'reconnect_delay' => 0.1
		] );

		$data = new Data(
			time(),
			'Test message',
			RunLevel::INFO,
			'INFO',
			[]
		);

		// Multiple writes should all be handled gracefully
		for( $i = 0; $i < 5; $i++ )
		{
			$ws->write( "Test message $i", $data );
		}

		// No exception means the test passed
		$this->assertTrue( true );
	}
}