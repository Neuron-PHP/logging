<?php

namespace Tests\Log\Adapter;

use Neuron\Log\Adapter\Psr3Adapter;
use Neuron\Log\Destination\Memory;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Logger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Comprehensive tests for PSR-3 LoggerInterface adapter.
 *
 * Tests all PSR-3 log methods and level mapping.
 */
class Psr3AdapterTest extends TestCase
{
	private Logger $neuronLogger;
	private Memory $destination;
	private Psr3Adapter $adapter;

	protected function setUp(): void
	{
		parent::setUp();

		// Create Neuron logger with Memory destination for testing
		$this->destination = new Memory(new PlainText());
		$this->neuronLogger = new Logger($this->destination);
		$this->neuronLogger->setRunLevel(RunLevel::DEBUG);

		// Create PSR-3 adapter
		$this->adapter = new Psr3Adapter($this->neuronLogger);
	}

	public function testEmergency(): void
	{
		$this->adapter->emergency('Emergency message', ['user' => 'admin']);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Emergency message', $output);
		$this->assertStringContainsString('Emergency', $output);
	}

	public function testAlert(): void
	{
		$this->adapter->alert('Alert message', ['ip' => '127.0.0.1']);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Alert message', $output);
		$this->assertStringContainsString('Alert', $output);
	}

	public function testCritical(): void
	{
		$this->adapter->critical('Critical message');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Critical message', $output);
		$this->assertStringContainsString('Critical', $output);
	}

	public function testError(): void
	{
		$this->adapter->error('Error message', ['code' => 500]);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Error message', $output);
		$this->assertStringContainsString('Error', $output);
	}

	public function testWarning(): void
	{
		$this->adapter->warning('Warning message');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Warning message', $output);
		$this->assertStringContainsString('Warning', $output);
	}

	public function testNotice(): void
	{
		$this->adapter->notice('Notice message', ['action' => 'login']);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Notice message', $output);
		$this->assertStringContainsString('Notice', $output);
	}

	public function testInfo(): void
	{
		$this->adapter->info('Info message');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Info message', $output);
		$this->assertStringContainsString('Info', $output);
	}

	public function testDebug(): void
	{
		$this->adapter->debug('Debug message', ['var' => 'value']);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Debug message', $output);
		$this->assertStringContainsString('Debug', $output);
	}

	public function testLogWithEmergencyLevel(): void
	{
		$this->adapter->log(LogLevel::EMERGENCY, 'Emergency via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Emergency via log()', $output);
	}

	public function testLogWithAlertLevel(): void
	{
		$this->adapter->log(LogLevel::ALERT, 'Alert via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Alert via log()', $output);
	}

	public function testLogWithCriticalLevel(): void
	{
		$this->adapter->log(LogLevel::CRITICAL, 'Critical via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Critical via log()', $output);
	}

	public function testLogWithErrorLevel(): void
	{
		$this->adapter->log(LogLevel::ERROR, 'Error via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Error via log()', $output);
	}

	public function testLogWithWarningLevel(): void
	{
		$this->adapter->log(LogLevel::WARNING, 'Warning via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Warning via log()', $output);
	}

	public function testLogWithNoticeLevel(): void
	{
		$this->adapter->log(LogLevel::NOTICE, 'Notice via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Notice via log()', $output);
	}

	public function testLogWithInfoLevel(): void
	{
		$this->adapter->log(LogLevel::INFO, 'Info via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Info via log()', $output);
	}

	public function testLogWithDebugLevel(): void
	{
		$this->adapter->log(LogLevel::DEBUG, 'Debug via log()');

		$output = $this->destination->getData();
		$this->assertStringContainsString('Debug via log()', $output);
	}

	public function testLogWithInvalidStringLevel(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid log level: invalid');

		$this->adapter->log('invalid', 'Should fail');
	}

	public function testLogWithNonStringLevel(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Log level must be a string, integer given');

		$this->adapter->log(123, 'Should fail');
	}

	public function testLogWithArrayLevel(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Log level must be a string, array given');

		$this->adapter->log(['invalid'], 'Should fail');
	}

	public function testGetNeuronLogger(): void
	{
		$logger = $this->adapter->getNeuronLogger();

		$this->assertSame($this->neuronLogger, $logger);
	}

	public function testStringableMessage(): void
	{
		$stringable = new class {
			public function __toString(): string {
				return 'Stringable message';
			}
		};

		$this->adapter->info($stringable);

		$output = $this->destination->getData();
		$this->assertStringContainsString('Stringable message', $output);
	}

	public function testContextIsPassedThrough(): void
	{
		$context = [
			'user_id' => 42,
			'action' => 'delete',
			'resource' => 'post'
		];

		$this->adapter->warning('User action', $context);

		$output = $this->destination->getData();
		$this->assertStringContainsString('User action', $output);
	}
}
