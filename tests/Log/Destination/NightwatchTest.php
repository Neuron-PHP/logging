<?php
namespace Tests\Log\Destination;

use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Base;
use Neuron\Log\Format\Raw;
use Neuron\Log\Logger;
use Neuron\Log\RunLevel;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Records PSR-3 calls for assertions.
 */
class MemoryPsrLogger implements LoggerInterface
{
	public array $records = [];

	public function emergency( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	public function alert( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::ALERT, $message, $context );
	}

	public function critical( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	public function error( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::ERROR, $message, $context );
	}

	public function warning( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::WARNING, $message, $context );
	}

	public function notice( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::NOTICE, $message, $context );
	}

	public function info( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::INFO, $message, $context );
	}

	public function debug( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::DEBUG, $message, $context );
	}

	public function log( $level, string|\Stringable $message, array $context = [] ): void
	{
		$this->records[] = [
			'level'   => $level,
			'message' => (string)$message,
			'context' => $context
		];
	}
}

/**
 * PSR-3 logger that always fails.
 */
class ThrowingPsrLogger implements LoggerInterface
{
	public function emergency( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::EMERGENCY, $message, $context );
	}

	public function alert( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::ALERT, $message, $context );
	}

	public function critical( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::CRITICAL, $message, $context );
	}

	public function error( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::ERROR, $message, $context );
	}

	public function warning( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::WARNING, $message, $context );
	}

	public function notice( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::NOTICE, $message, $context );
	}

	public function info( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::INFO, $message, $context );
	}

	public function debug( string|\Stringable $message, array $context = [] ): void
	{
		$this->log( LogLevel::DEBUG, $message, $context );
	}

	public function log( $level, string|\Stringable $message, array $context = [] ): void
	{
		throw new \RuntimeException( 'nightwatch channel unavailable' );
	}
}

/**
 * Formatter that would change the message if write() used $text.
 */
class PrefixFormat extends Base
{
	public function format( \Neuron\Log\Data $data ): string
	{
		return 'FORMATTED:' . $data->text;
	}
}

/**
 * Nightwatch destination that records stderr instead of writing to the process.
 */
class NightwatchCapturingStdErr extends Nightwatch
{
	/** @var resource */
	public $stream;

	public function __construct( \Neuron\Log\Format\IFormat $format )
	{
		parent::__construct( $format );
		$this->stream = fopen( 'php://memory', 'w+' );
	}

	public function getStdErr()
	{
		return $this->stream;
	}

	public function stderrContents(): string
	{
		rewind( $this->stream );

		return stream_get_contents( $this->stream );
	}
}

/**
 * Nightwatch destination whose stderr handle throws.
 */
class NightwatchBrokenStdErr extends Nightwatch
{
	public function getStdErr()
	{
		throw new \RuntimeException( 'stderr unavailable' );
	}
}

class NightwatchTest extends TestCase
{
	public function testOpenWithoutLoggerFails()
	{
		$nightwatch = new Nightwatch( new Raw() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nightwatch destination requires a PSR-3 logger parameter' );

		$nightwatch->open( [] );
	}

	public function testOpenWithInvalidLoggerFails()
	{
		$nightwatch = new Nightwatch( new Raw() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nightwatch destination requires a PSR-3 logger parameter' );

		$nightwatch->open( [ 'logger' => 'not-a-logger' ] );
	}

	public function testOpenWithInvalidChannelKeyFails()
	{
		$nightwatch = new Nightwatch( new Raw() );

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'Nightwatch channel_key must be a non-empty string' );

		$nightwatch->open(
			[
				'logger'      => new MemoryPsrLogger(),
				'channel_key' => ''
			]
		);
	}

	public function testForwardsLevelMessageAndContext()
	{
		$psr = new MemoryPsrLogger();
		$logger = $this->loggerFor( $psr );

		$logger->error( 'Database connection failed', [ 'host' => 'db.example.com' ] );

		$this->assertCount( 1, $psr->records );
		$this->assertSame( LogLevel::ERROR, $psr->records[0]['level'] );
		$this->assertSame( 'Database connection failed', $psr->records[0]['message'] );
		$this->assertSame( 'db.example.com', $psr->records[0]['context']['host'] );
		$this->assertArrayNotHasKey( 'neuron_channel', $psr->records[0]['context'] );
	}

	public function testMapsEveryRunLevel()
	{
		$levels = [
			[ RunLevel::DEBUG, LogLevel::DEBUG ],
			[ RunLevel::INFO, LogLevel::INFO ],
			[ RunLevel::NOTICE, LogLevel::NOTICE ],
			[ RunLevel::WARNING, LogLevel::WARNING ],
			[ RunLevel::ERROR, LogLevel::ERROR ],
			[ RunLevel::CRITICAL, LogLevel::CRITICAL ],
			[ RunLevel::ALERT, LogLevel::ALERT ],
			[ RunLevel::EMERGENCY, LogLevel::EMERGENCY ]
		];

		foreach( $levels as $level )
		{
			$psr = new MemoryPsrLogger();
			$logger = $this->loggerFor( $psr );
			$logger->log( 'level check', $level[0] );

			$this->assertSame( $level[1], $psr->records[0]['level'] );
		}
	}

	public function testAddsNeuronChannelToContext()
	{
		$psr = new MemoryPsrLogger();
		$logger = $this->loggerFor( $psr );
		$logger->setChannel( 'payments' );

		$logger->info( 'Payment failed', [ 'amount' => 99.99 ] );

		$this->assertSame( 'payments', $psr->records[0]['context']['neuron_channel'] );
		$this->assertSame( 99.99, $psr->records[0]['context']['amount'] );
	}

	public function testChannelKeyIsConfigurable()
	{
		$psr = new MemoryPsrLogger();
		$destination = new Nightwatch( new Raw() );
		$destination->open(
			[
				'logger'      => $psr,
				'channel_key' => 'source'
			]
		);

		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );
		$logger->setChannel( 'audit' );
		$logger->info( 'User updated profile' );

		$this->assertSame( 'audit', $psr->records[0]['context']['source'] );
		$this->assertArrayNotHasKey( 'neuron_channel', $psr->records[0]['context'] );
	}

	public function testChannelOverwritesExistingContextKey()
	{
		$psr = new MemoryPsrLogger();
		$logger = $this->loggerFor( $psr );
		$logger->setChannel( 'security' );

		$logger->warning( 'Failed login', [ 'neuron_channel' => 'caller' ] );

		$this->assertSame( 'security', $psr->records[0]['context']['neuron_channel'] );
	}

	public function testPreservesThrowableInContext()
	{
		$exception = new \RuntimeException( 'connection refused' );
		$psr = new MemoryPsrLogger();
		$logger = $this->loggerFor( $psr );

		$logger->error( 'Query failed', [ 'exception' => $exception, 'sql' => 'SELECT 1' ] );

		$this->assertSame( $exception, $psr->records[0]['context']['exception'] );
		$this->assertSame( 'SELECT 1', $psr->records[0]['context']['sql'] );
	}

	public function testIgnoresFormattedText()
	{
		$psr = new MemoryPsrLogger();
		$destination = new Nightwatch( new PrefixFormat() );
		$destination->open( [ 'logger' => $psr ] );

		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );
		$logger->info( 'plain message' );

		$this->assertSame( 'plain message', $psr->records[0]['message'] );
		$this->assertStringNotContainsString( 'FORMATTED:', $psr->records[0]['message'] );
	}

	public function testLoggerFailureDoesNotEscape()
	{
		$destination = new NightwatchCapturingStdErr( new Raw() );
		$destination->open( [ 'logger' => new ThrowingPsrLogger() ] );

		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->error( 'This must not throw' );

		$this->assertStringContainsString( 'nightwatch channel unavailable', $destination->stderrContents() );
	}

	public function testStdErrFailureDoesNotEscape()
	{
		$destination = new NightwatchBrokenStdErr( new Raw() );
		$destination->open( [ 'logger' => new ThrowingPsrLogger() ] );

		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->error( 'This must not throw either' );

		$this->assertTrue( true );
	}

	public function testWriteBeforeOpenDoesNotThrow()
	{
		$destination = new NightwatchCapturingStdErr( new Raw() );
		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );

		$logger->info( 'opened nowhere' );

		$this->assertStringContainsString( 'must not be accessed before initialization', $destination->stderrContents() );
	}

	/**
	 * @param LoggerInterface $psr
	 * @return Logger
	 */
	private function loggerFor( LoggerInterface $psr ): Logger
	{
		$destination = new Nightwatch( new Raw() );
		$destination->open( [ 'logger' => $psr ] );

		$logger = new Logger( $destination );
		$logger->setRunLevel( RunLevel::DEBUG );

		return $logger;
	}
}
