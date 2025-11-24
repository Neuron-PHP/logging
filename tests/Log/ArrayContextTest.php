<?php
namespace Tests\Log;

use Neuron\Log\Data;
use Neuron\Log\Logger;
use Neuron\Log\Log;
use Neuron\Log\RunLevel;
use Neuron\Log\Destination\Memory;
use Neuron\Log\Format\PlainText;
use PHPUnit\Framework\TestCase;

class ArrayContextTest extends TestCase
{
	public function testMessageInterpolation()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->error( 'User {userId} failed login from {ip}', [
			'userId' => 12345,
			'ip'     => '192.168.1.100'
		] );

		$output = $memory->getData();
		$this->assertStringContainsString( 'User 12345 failed login from 192.168.1.100', $output );
	}

	public function testArrayContext()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->info( 'Processing order', [
			'orderId' => 'ORD-123',
			'items'   => [ 'apple', 'banana', 'orange' ],
			'total'   => 45.99
		] );

		$output = $memory->getData();
		$this->assertStringContainsString( 'Processing order', $output );
		$this->assertStringContainsString( 'orderId=ORD-123', $output );
		$this->assertStringContainsString( 'total=45.99', $output );
		// Array should be JSON encoded
		$this->assertStringContainsString( '["apple","banana","orange"]', $output );
	}

	public function testExceptionContext()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		$exception = new \RuntimeException( 'Test exception' );

		$logger->error( 'Operation failed', [
			'exception' => $exception,
			'operation' => 'database_query'
		] );

		$output = $memory->getData();
		$this->assertStringContainsString( 'Operation failed', $output );
		$this->assertStringContainsString( 'RuntimeException: Test exception', $output );
		$this->assertStringContainsString( 'operation=database_query', $output );
	}

	public function testMixedContextTypes()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->debug( 'Complex context test', [
			'string'  => 'hello',
			'int'     => 42,
			'float'   => 3.14,
			'bool'    => true,
			'null'    => null,
			'array'   => [ 1, 2, 3 ]
		] );

		$output = $memory->getData();
		$this->assertStringContainsString( 'string=hello', $output );
		$this->assertStringContainsString( 'int=42', $output );
		$this->assertStringContainsString( 'float=3.14', $output );
		$this->assertStringContainsString( 'bool=true', $output );
		$this->assertStringContainsString( 'null=null', $output );
		$this->assertStringContainsString( '[1,2,3]', $output );
	}

	public function testGlobalAndLocalContextMerge()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		// Set global context
		$logger->setContext( 'app', 'myapp' );
		$logger->setContext( 'version', '1.0.0' );

		// Log with local context
		$logger->info( 'User action', [
			'user'   => 'john',
			'action' => 'login'
		] );

		$output = $memory->getData();
		// Should have both global and local context
		$this->assertStringContainsString( 'app=myapp', $output );
		$this->assertStringContainsString( 'version=1.0.0', $output );
		$this->assertStringContainsString( 'user=john', $output );
		$this->assertStringContainsString( 'action=login', $output );
	}

	public function testLogSingletonWithArrayContext()
	{
		// Reset singleton logger
		Log::getInstance()->logger = null;
		Log::setRunLevel( RunLevel::DEBUG );

		// Capture output
		ob_start();

		Log::error( 'Database error: {error}', [
			'error'  => 'Connection timeout',
			'host'   => 'db.example.com',
			'port'   => 3306
		] );

		$output = ob_get_clean();

		$this->assertStringContainsString( 'Database error: Connection timeout', $output );
		$this->assertStringContainsString( 'host=db.example.com', $output );
		$this->assertStringContainsString( 'port=3306', $output );
	}

	public function testComplexObjectWithToString()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		// Create an object with __toString
		$obj = new class {
			public function __toString()
			{
				return 'CustomObject[id=123]';
			}
		};

		$logger->info( 'Object test', [
			'object' => $obj
		] );

		$output = $memory->getData();
		$this->assertStringContainsString( 'object=CustomObject[id=123]', $output );
	}

	public function testSetContextWithArrayValue()
	{
		$memory = new Memory( new PlainText( false ) );
		$logger = new Logger( $memory );
		$logger->setRunLevel( RunLevel::DEBUG );

		// Set context with array value
		$logger->setContext( 'tags', [ 'production', 'critical' ] );
		$logger->setContext( 'server', 'web01' );

		$logger->error( 'System failure' );

		$output = $memory->getData();
		$this->assertStringContainsString( 'tags=["production","critical"]', $output );
		$this->assertStringContainsString( 'server=web01', $output );
	}
}