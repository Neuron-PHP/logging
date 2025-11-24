[![CI](https://github.com/Neuron-PHP/logging/actions/workflows/ci.yml/badge.svg)](https://github.com/Neuron-PHP/logging/actions)
# Neuron-PHP Logging

A flexible and powerful logging component for PHP 8.4+ applications, part of the Neuron-PHP framework.

## Features

- **PSR-3 Compatible**: Full PSR-3 logger interface with array contexts and message interpolation
- **Multiple Destinations**: Write logs to files, console, Slack, webhooks, syslog, Nightwatch, and more
- **Flexible Formatting**: Support for plain text, JSON, HTML, CSV, and custom formats
- **Log Levels**: Full PSR-3 compliant log levels (debug, info, notice, warning, error, critical, alert, emergency)
- **Array Context Support**: Pass complex arrays, objects, and exceptions as context
- **Message Interpolation**: Use `{placeholders}` in messages for automatic substitution
- **Channels**: Manage multiple independent loggers with automatic channel tracking
- **Filters**: Apply custom filters to control which logs are written
- **Multiplexing**: Write to multiple destinations simultaneously with different run levels
- **Exception Tracking**: Automatic exception formatting with stack traces
- **Laravel Nightwatch**: Full integration with special context handling

## Requirements

- PHP 8.4 or higher
- ext-curl (for webhook/Slack destinations)
- ext-json (for JSON formatting)
- ext-sockets (for socket destination)

## Installation

Install via Composer:

```bash
composer require neuron-php/logging
```

## Quick Start

The simplest way to start logging is using the singleton facade:

```php
use Neuron\Log\Log;

// Set the minimum log level
Log::setRunLevel( 'debug' );

// Write log messages
Log::debug( 'Debug message' );
Log::info( 'Information message' );
Log::notice( 'Notice: user registration started' );
Log::warning( 'Warning message' );
Log::error( 'Error message' );
Log::critical( 'Critical: database connection lost' );
Log::alert( 'Alert: disk space critically low' );
Log::emergency( 'Emergency: system is shutting down' );

// With array context and interpolation (PSR-3 style)
Log::error( 'User {userId} failed login from {ip}', [
	'userId' => 12345,
	'ip' => '192.168.1.1',
	'attempts' => 3
] );
```

## Destinations

The logging component supports writing to various destinations:

### Available Destinations

- **Echo**: Output to browser/console
- **Email**: Send logs via email
- **File**: Write to log files
- **Memory**: Store logs in memory for testing
- **Nightwatch**: Send to Laravel Nightwatch monitoring service
- **Null**: Discard logs (useful for testing)
- **Papertrail**: Send to Papertrail cloud logging service
- **Slack**: Post to Slack channels
- **Socket**: Send over network sockets
- **Sqs**: Send to Amazon SQS for queue-based processing
- **StdErr**: Write to standard error
- **StdOut**: Write to standard output
- **StdOutStdErr**: Write to both stdout and stderr based on level
- **SysLog**: System logging
- **Webhook**: Send to custom webhooks
- **WebSocket**: Stream logs in real-time via WebSocket

### Formats

Each destination can use different formatting:

- **CSV**: Comma-separated values
- **HTML**: HTML formatted output
- **HTMLEmail**: HTML optimized for emails
- **JSON**: Structured JSON output
- **Nightwatch**: Laravel Nightwatch-specific JSON formatting
- **PlainText**: Human-readable text format
- **Raw**: Unformatted output
- **Slack**: Slack-specific formatting

## Usage Examples

### Basic Logging

```php
use Neuron\Log\Log;

// Configure run level
Log::setRunLevel( 'debug' );

// Simple logging
Log::debug( 'Debug information' );
Log::info( 'Application started' );
Log::warning( 'Memory usage high' );

// With array context
Log::error( 'Database connection failed', [
	'host' => 'db.example.com',
	'port' => 3306,
	'error' => 'Connection timeout'
] );

// With message interpolation
Log::info( 'User {user} performed {action}', [
	'user' => 'admin@example.com',
	'action' => 'delete_records',
	'count' => 42
] );
```

### File Logging

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\PlainText;

// Create a file logger
$fileDestination = new File( new PlainText() );
$fileDestination->open( [ 'path' => '/var/log/app.log' ] );

$logger = new Logger( $fileDestination );
$logger->setRunLevel( 'info' );
$logger->info( 'Application event logged to file' );
```

### JSON Logging

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\JSON;

// Log in JSON format for structured logging
$jsonDestination = new File( new JSON() );
$jsonDestination->open( [ 'path' => '/var/log/app.json' ] );

$jsonLogger = new Logger( $jsonDestination );

// Array context is automatically included in JSON output
$jsonLogger->error( 'Database error', [
	'query' => 'SELECT * FROM users',
	'error_code' => 1054,
	'duration' => 234.5
] );
```

### Slack Integration

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\SlackFormat;

$slack = new Slack( new SlackFormat() );
$slack->open( [
	'endpoint' => $_ENV['LOG_SLACK_WEBHOOK_URL'],
	'params' => [
		'channel'  => '#alerts',
		'username' => 'AppLogger',
		'icon_emoji' => ':warning:'
	]
] );

$slackLogger = new Logger( $slack );
$slackLogger->setRunLevel( 'error' );

// Add to the multiplexer
Log::getInstance()->Logger->addLog( $slackLogger );

// Now errors and above will also go to Slack with context
Log::error( 'Payment processing failed', [
	'transaction_id' => 'txn_12345',
	'amount' => 99.99,
	'currency' => 'USD'
] );
```

### Laravel Nightwatch Integration

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;

// Create Nightwatch destination with default channel
$nightwatch = new Nightwatch( new NightwatchFormat() );
$nightwatch->open( [
	'token' => $_ENV['NIGHTWATCH_TOKEN'],  // Your Nightwatch API token
	'endpoint' => 'https://nightwatch.laravel.com/api/logs', // Optional, uses default
	'batch_size' => 10,  // Optional: batch logs for better performance
	'timeout' => 5       // Optional: API request timeout in seconds
] );

$nightwatchLogger = new Logger( $nightwatch );
$nightwatchLogger->setRunLevel( 'info' );

// Log messages will be sent to Nightwatch
$nightwatchLogger->info( 'Application started' );
$nightwatchLogger->error( 'Database connection failed', [
	'host' => 'db.example.com',
	'port' => 3306,
	'error' => 'Connection timeout'
] );

// For production use with the singleton
use Neuron\Log\Log;

// Add Nightwatch to the global logger
Log::getInstance()->Logger->addLog( $nightwatchLogger );

// Now all logs at info level and above go to Nightwatch
Log::info( 'User logged in', [ 'user_id' => 123 ] );
Log::warning( 'API rate limit approaching', [ 'requests' => 950, 'limit' => 1000 ] );
Log::error( 'Payment processing failed', [ 'transaction_id' => 'txn_abc123' ] );
```

#### Nightwatch with Channels

The channel name is automatically passed to Nightwatch when using named channels:

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;

// Create a single Nightwatch format instance
$nightwatchFormat = new NightwatchFormat( 'neuron', 'my-app' );

// Create Nightwatch destination
$nightwatch = new Nightwatch( $nightwatchFormat );
$nightwatch->open( [ 'token' => $_ENV['NIGHTWATCH_TOKEN'] ] );

// Create logger and add to multiple channels
$logger = new Logger( $nightwatch );
Log::addChannel( 'audit', $logger );
Log::addChannel( 'security', $logger );
Log::addChannel( 'payments', $logger );

// Logs automatically include the channel name
Log::channel( 'audit' )->info( 'User updated profile', [ 'user_id' => 123 ] );
// Nightwatch receives: {"channel": "audit", "message": "User updated profile", ...}

Log::channel( 'security' )->warning( 'Failed login attempt', [ 'ip' => '192.168.1.1' ] );
// Nightwatch receives: {"channel": "security", "message": "Failed login attempt", ...}

Log::channel( 'payments' )->error( 'Payment failed', [ 'amount' => 99.99 ] );
// Nightwatch receives: {"channel": "payments", "message": "Payment failed", ...}
```

The channel name appears in the Nightwatch dashboard for easy filtering and monitoring.

### Papertrail Integration

Papertrail is a cloud-based logging service that aggregates and centralizes logs from multiple sources. The Papertrail destination sends logs using the remote syslog protocol with optional TLS encryption.

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Papertrail;
use Neuron\Log\Format\PlainText;

// Create Papertrail destination
$papertrail = new Papertrail( new PlainText() );
$papertrail->open( [
	'host' => 'logs5.papertrailapp.com',  // Your Papertrail host
	'port' => 12345,                       // Your Papertrail port
	'system_name' => 'my-app-prod',        // Optional: System name (defaults to hostname)
	'use_tls' => true,                     // Optional: Use TLS encryption (default: true)
	'facility' => 16,                      // Optional: Syslog facility (default: 16 for local0)
	'sd_id' => 'mycompany@12345'          // Optional: Structured Data ID (default: 'neuron@32473')
] );

$papertrailLogger = new Logger( $papertrail );
$papertrailLogger->setRunLevel( 'info' );

// Logs are sent to Papertrail with structured data
$papertrailLogger->error( 'Payment processing failed', [
	'transaction_id' => 'txn_12345',
	'amount' => 99.99,
	'currency' => 'USD',
	'error_code' => 'INSUFFICIENT_FUNDS'
] );

// Context is included as structured data in syslog format
$papertrailLogger->info( 'User action', [
	'user_id' => 456,
	'action' => 'purchase',
	'items' => [ 'SKU-123', 'SKU-456' ]
] );
```

The Papertrail destination:
- Sends logs using RFC 5424 syslog format over TCP/TLS
- Automatically maps log levels to syslog severities
- Includes context as structured data for easy filtering in Papertrail
- Supports automatic reconnection if the connection is lost
- Allows custom SD-ID for organizations with IANA Private Enterprise Numbers

### Amazon SQS Integration

Send logs to Amazon SQS for scalable, queue-based processing:

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Sqs;
use Neuron\Log\Format\JSON;

// Create SQS destination
$sqs = new Sqs( new JSON() );
$sqs->open( [
	'queue_url' => 'https://sqs.us-east-1.amazonaws.com/123456789/my-log-queue',
	'region' => 'us-east-1',
	'credentials' => [                    // Optional: Use IAM role if not provided
		'key' => $_ENV['AWS_ACCESS_KEY'],
		'secret' => $_ENV['AWS_SECRET_KEY']
	],
	'batch_size' => 10,                   // Optional: Batch messages (1-10, default 1)
	'attributes' => [                     // Optional: Message attributes
		'Environment' => 'production',
		'Application' => 'neuron-app'
	],
	'max_retries' => 3,                   // Optional: Retry attempts (default 3)
	'retry_delay' => 1.0                  // Optional: Initial retry delay in seconds
] );

$sqsLogger = new Logger( $sqs );
$sqsLogger->setRunLevel( 'info' );

// Logs are sent to SQS with automatic batching
$sqsLogger->error( 'Critical system error', [
	'service' => 'payment-processor',
	'error_code' => 'GATEWAY_TIMEOUT',
	'retry_count' => 3
] );

// Context and channel are included in message body
$sqsLogger->info( 'Order processed', [
	'order_id' => 'ORD-12345',
	'amount' => 299.99,
	'items' => 5
] );
```

The SQS destination:
- Sends logs as JSON messages to SQS queues
- Supports batching up to 10 messages for improved performance
- Automatic retry with exponential backoff
- Message attributes for filtering and routing
- Works with IAM roles or explicit credentials
- Includes log level and channel as message attributes

**Required**: Install the AWS SDK via Composer:
```bash
composer require aws/aws-sdk-php
```

### WebSocket Real-Time Streaming

Stream logs in real-time to web browsers or monitoring dashboards using WebSocket connections:

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\WebSocket;
use Neuron\Log\Format\JSON;

// Create WebSocket destination
$websocket = new WebSocket( new JSON() );
$websocket->open( [
	'url' => 'ws://localhost:8080/logs',  // WebSocket server URL
	'max_reconnect_attempts' => 5,        // Optional: Max reconnection attempts (default: 5)
	'reconnect_delay' => 1.0              // Optional: Initial reconnect delay in seconds
] );

$wsLogger = new Logger( $websocket );
$wsLogger->setRunLevel( 'debug' );

// Logs are streamed in real-time to connected WebSocket clients
$wsLogger->info( 'Real-time event', [
	'event_type' => 'user_login',
	'user_id' => 123,
	'timestamp' => time()
] );
```

The WebSocket destination:
- Maintains persistent WebSocket connections
- Automatically reconnects with exponential backoff
- Sends logs as WebSocket text frames
- Perfect for real-time monitoring dashboards
- Works with any WebSocket server implementation

### Array Context Support (PSR-3 Compatible)

```php
use Neuron\Log\Log;

// Pass array context as second parameter
Log::error( 'Database query failed', [
	'query' => 'SELECT * FROM orders WHERE id = ?',
	'params' => [12345],
	'duration' => 1234.5,
	'error' => 'Connection timeout'
] );

// Complex nested arrays
Log::info( 'Order processed', [
	'order' => [
		'id' => 'ORD-12345',
		'items' => [
			[ 'sku' => 'WIDGET-1', 'qty' => 2 ],
			[ 'sku' => 'GADGET-5', 'qty' => 1 ]
		],
		'total' => 149.99
	],
	'customer_id' => 789
] );

// Exception tracking with automatic formatting
try
{
	// some operation
}
catch( Exception $e )
{
	Log::error( 'Operation failed', [
		'exception' => $e,  // Stack trace automatically captured
		'operation' => 'process_payment',
		'user_id' => $userId
	] );
}
```

### Message Interpolation

```php
// Use {placeholders} in messages - PSR-3 style
Log::info( 'User {userId} logged in from {ip} at {time}', [
	'userId' => 12345,
	'ip' => '192.168.1.100',
	'time' => date( 'H:i:s' )
] );
// Output: User 12345 logged in from 192.168.1.100 at 14:30:45

// Interpolation works with all log levels
Log::error( 'Failed to send email to {email}: {error}', [
	'email' => 'user@example.com',
	'error' => 'SMTP connection refused',
	'retry_count' => 3
] );
```

### Global and Local Context

```php
use Neuron\Log\Log;

// Set global context that applies to all logs
Log::setContext( 'app_version', '2.0.0' );
Log::setContext( 'environment', 'production' );
Log::setContext( 'server', gethostname() );

// Can also set array values in global context
Log::setContext( 'tags', [ 'monitoring', 'production' ] );

// Local context in log call is merged with global
Log::info( 'User action', [
	'action' => 'profile_update',
	'user_id' => 456
] );
// Both global and local context are included

### Multiple Channels

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Format\SlackFormat;

// Create an audit logger
$auditFile = new File( new PlainText() );
$auditFile->open( [ 'path' => '/var/log/audit.log' ] );
$auditLogger = new Logger( $auditFile );

// Create a real-time alerts logger
$alertSlack = new Slack( new SlackFormat() );
$alertSlack->open( [
	'endpoint' => $_ENV['SLACK_WEBHOOK'],
	'params' => [ 'channel' => '#alerts' ]
] );
$alertLogger = new Logger( $alertSlack );

// Register channels
Log::addChannel( 'audit', $auditLogger );
Log::addChannel( 'alerts', $alertLogger );

// Use specific channels
Log::channel( 'audit' )->info( 'User login', [ 'user' => $username ] );
Log::channel( 'alerts' )->emergency( 'System down!' );
```

### Multiplexer (Multiple Destinations)

```php
use Neuron\Log\LogMux;
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Destination\StdErr;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Format\JSON;

// Create multiple loggers
$fileLogger = new Logger( new File( new JSON() ) );
$fileLogger->getDestination()->open( [ 'path' => '/var/log/app.json' ] );
$fileLogger->setRunLevel( 'debug' );

$consoleLogger = new Logger( new StdErr( new PlainText() ) );
$consoleLogger->setRunLevel( 'warning' );

// Create multiplexer
$mux = new LogMux();
$mux->addLog( $fileLogger );
$mux->addLog( $consoleLogger );

// Logs go to both destinations based on their run levels
$mux->debug( 'Debug info' );     // Only to file
$mux->warning( 'Warning!' );     // To both file and console
$mux->error( 'Error occurred' ); // To both file and console
```

### PSR-3 Adapter

The Neuron logging component provides a PSR-3 adapter that allows you to use Neuron loggers with any library or framework that expects a PSR-3 compliant logger (like Symfony, Laravel packages, Monolog alternatives, etc.).

#### Basic Usage

```php
use Neuron\Log\Logger;
use Neuron\Log\Adapter\Psr3Adapter;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\JSON;

// Create a Neuron logger
$destination = new File( new JSON() );
$destination->open( [ 'path' => '/var/log/app.json' ] );
$neuronLogger = new Logger( $destination );
$neuronLogger->setRunLevel( 'info' );

// Wrap it with the PSR-3 adapter
$psr3Logger = new Psr3Adapter( $neuronLogger );

// Now use it with any PSR-3 expecting code
$psr3Logger->info( 'Application started' );
$psr3Logger->error( 'Database connection failed', [
	'host' => 'db.example.com',
	'error' => 'Connection timeout'
] );
```

#### Framework Integration

The adapter makes it easy to integrate Neuron logging into frameworks that expect PSR-3:

```php
use Neuron\Log\Log;
use Neuron\Log\Adapter\Psr3Adapter;

// Get Neuron's singleton logger and wrap it
$psr3Logger = new Psr3Adapter( Log::getInstance()->Logger );

// Use with Symfony components
$httpClient = new \Symfony\Component\HttpClient\CurlHttpClient( [
	'logger' => $psr3Logger
] );

// Use with Guzzle
$guzzleClient = new \GuzzleHttp\Client( [
	'handler' => $handlerStack,
	'logger' => $psr3Logger
] );

// Use with any PSR-3 compatible library
$thirdPartyService = new ThirdPartyService( $psr3Logger );
```

#### Level Mapping

The adapter automatically maps PSR-3 log levels to Neuron's RunLevel enum:

| PSR-3 Level | Neuron RunLevel | Numeric Value |
|-------------|-----------------|---------------|
| emergency   | EMERGENCY       | 50            |
| alert       | ALERT           | 45            |
| critical    | CRITICAL        | 40            |
| error       | ERROR           | 30            |
| warning     | WARNING         | 20            |
| notice      | NOTICE          | 15            |
| info        | INFO            | 10            |
| debug       | DEBUG           | 0             |

#### Advanced Features

The adapter maintains full compatibility with Neuron's advanced features:

```php
use Neuron\Log\Logger;
use Neuron\Log\Adapter\Psr3Adapter;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\JSON;

// Create Neuron logger with multiple destinations
$fileLogger = new Logger( new File( new JSON() ) );
$fileLogger->getDestination()->open( [ 'path' => '/var/log/app.json' ] );

// Add filters, context, and other Neuron features
$fileLogger->setContext( 'app_version', '2.0.0' );
$fileLogger->setContext( 'environment', 'production' );

// Wrap with PSR-3 adapter
$psr3Logger = new Psr3Adapter( $fileLogger );

// PSR-3 calls use Neuron features underneath
$psr3Logger->warning( 'API rate limit approaching', [
	'requests' => 950,
	'limit' => 1000
] );
// Context includes: app_version, environment, requests, limit

// Access the underlying Neuron logger if needed
$neuronLogger = $psr3Logger->getNeuronLogger();
$neuronLogger->setRunLevel( 'debug' );
```

#### Why Use the Adapter?

1. **Framework Integration**: Use Neuron's powerful logging with any PSR-3 expecting library
2. **Gradual Migration**: Migrate from other PSR-3 loggers without changing application code
3. **Best of Both Worlds**: Keep Neuron's advanced features (multiple destinations, formats, filters) while maintaining PSR-3 compatibility
4. **Drop-in Replacement**: Works as a drop-in replacement for Monolog, PSR-3 Log, or other PSR-3 implementations

### Custom Filters

```php
use Neuron\Log\Logger;
use Neuron\Log\Filter\IFilter;
use Neuron\Log\RunLevel;

class ProductionFilter implements IFilter
{
	public function shouldLog( RunLevel $level, string $message, array $context ): bool
	{
		// Don't log debug messages in production
		if( $level === RunLevel::DEBUG && $_ENV['APP_ENV'] === 'production' )
		{
			return false;
		}

		// Don't log sensitive data
		if( str_contains( $message, 'password' ) || str_contains( $message, 'token' ) )
		{
			return false;
		}

		return true;
	}
}

$logger = new Logger( $destination );
$logger->addFilter( new ProductionFilter() );
```

## Advanced Configuration

### Environment-Based Configuration

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Destination\StdOut;
use Neuron\Log\Format\JSON;
use Neuron\Log\Format\PlainText;

$environment = $_ENV['APP_ENV'] ?? 'development';

if( $environment === 'production' )
{
	// Production: JSON to file
	$destination = new File( new JSON() );
	$destination->open( [ 'path' => '/var/log/app.json' ] );
	$runLevel = 'warning';
}
else
{
	// Development: Plain text to console
	$destination = new StdOut( new PlainText( true ) );
	$runLevel = 'debug';
}

$logger = new Logger( $destination );
$logger->setRunLevel( $runLevel );

Log::getInstance()->Logger->addLog( $logger );
```

### Testing with Memory Logger

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Memory;
use Neuron\Log\Format\Raw;

// For unit testing
$memoryDestination = new Memory( new Raw() );
$testLogger = new Logger( $memoryDestination );

$testLogger->error( 'Test error' );
$testLogger->info( 'Test info' );

// Retrieve logged messages
$logs = $memoryDestination->getLogs();
assert( count( $logs ) === 2 );
assert( $logs[0]['level'] === 'error' );
```

## API Reference

### Log Levels

The following PSR-3 compliant log levels are supported (from lowest to highest severity):

- `debug` (0) - Detailed debug information for development and troubleshooting
- `info` (10) - Informational messages about application flow
- `notice` (15) - Normal but significant events
- `warning` (20) - Warning messages about potentially harmful situations
- `error` (30) - Error events that allow the application to continue running
- `critical` (40) - Critical conditions that need immediate attention
- `alert` (45) - Action must be taken immediately
- `emergency` (50) - System is unusable, requires immediate intervention

### Logger Methods

All logger methods accept an optional array context for additional data:

```php
$logger->debug( string $message, array $context = [] );
$logger->info( string $message, array $context = [] );
$logger->notice( string $message, array $context = [] );
$logger->warning( string $message, array $context = [] );
$logger->error( string $message, array $context = [] );
$logger->critical( string $message, array $context = [] );
$logger->alert( string $message, array $context = [] );
$logger->emergency( string $message, array $context = [] );
$logger->log( string $message, RunLevel $level, array $context = [] );

// Set minimum log level
$logger->setRunLevel( mixed $level );           // Accepts RunLevel enum or string
$logger->setRunLevelText( string $level );      // Set by text: 'debug', 'info', etc.
$logger->getRunLevel(): RunLevel;               // Get current run level

// Context management
$logger->setContext( string $name, mixed $value );  // Add global context
$logger->getContext(): array;                       // Get all context
$logger->reset();                                   // Clear all context
```

## Testing

Run the test suite:

```bash
./vendor/bin/phpunit tests
```

Run tests with coverage:

```bash
./vendor/bin/phpunit tests --coverage-text
```

## Contributing

Contributions are welcome! Please ensure all tests pass and maintain code coverage above 95%.

## License

MIT License - see LICENSE file for details.

## More Information

- Full documentation: [neuronphp.com](http://neuronphp.com)
- GitHub: [github.com/neuron-php/logging](https://github.com/neuron-php/logging)
- Issues: [github.com/neuron-php/logging/issues](https://github.com/neuron-php/logging/issues)
