<?php
namespace Tests\Log\Format;

use Neuron\Log\Format\JSON;

class JSONTest extends LogTestBase
{
	public function testFormat()
	{
		$json = new JSON();

		$out = $json->format( $this->data );

		$decoded = json_decode( $out, true );

		$this->assertTrue(
			is_array( $decoded )
		);

		// Check basic fields
		$this->assertArrayHasKey( 'date', $decoded );
		$this->assertArrayHasKey( 'level', $decoded );
		$this->assertArrayHasKey( 'message', $decoded );
		$this->assertEquals( 'Test log.', $decoded['message'] );
	}

	public function testFormatWithArrayContext()
	{
		$json = new JSON();

		$context = [
			'user_id' => 123,
			'action' => 'login',
			'metadata' => [
				'ip' => '192.168.1.1',
				'browser' => 'Chrome'
			]
		];

		$data = new \Neuron\Log\Data(
			time(),
			'User logged in',
			\Neuron\Log\RunLevel::INFO,
			'INFO',
			$context
		);

		$out = $json->format( $data );
		$decoded = json_decode( $out, true );

		// Check context is properly included as structured data
		$this->assertArrayHasKey( 'context', $decoded );
		$this->assertEquals( 123, $decoded['context']['user_id'] );
		$this->assertEquals( 'login', $decoded['context']['action'] );
		$this->assertIsArray( $decoded['context']['metadata'] );
		$this->assertEquals( '192.168.1.1', $decoded['context']['metadata']['ip'] );
	}

	public function testFormatWithChannel()
	{
		$json = new JSON();

		$data = new \Neuron\Log\Data(
			time(),
			'Test message',
			\Neuron\Log\RunLevel::ERROR,
			'ERROR',
			[ 'error_code' => 500 ],
			'api'  // channel
		);

		$out = $json->format( $data );
		$decoded = json_decode( $out, true );

		// Check channel is included
		$this->assertArrayHasKey( 'channel', $decoded );
		$this->assertEquals( 'api', $decoded['channel'] );

		// Check context is still there
		$this->assertArrayHasKey( 'context', $decoded );
		$this->assertEquals( 500, $decoded['context']['error_code'] );
	}
}
