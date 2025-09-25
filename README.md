[![CI](https://github.com/Neuron-PHP/logging/actions/workflows/ci.yml/badge.svg)](https://github.com/Neuron-PHP/logging/actions)
# Neuron-PHP Logging

A flexible and powerful logging component for PHP 8.4+ applications, part of the Neuron-PHP framework.

## Features

- **Multiple Destinations**: Write logs to files, console, Slack, webhooks, syslog, and more
- **Flexible Formatting**: Support for plain text, JSON, HTML, CSV, and custom formats
- **Log Levels**: Full support for standard log levels (debug, info, warning, error, critical, alert, emergency)
- **Contextual Logging**: Attach contextual data to log entries for better debugging
- **Channels**: Manage multiple independent loggers with different configurations
- **Filters**: Apply custom filters to control which logs are written
- **Multiplexing**: Write to multiple destinations simultaneously with different run levels
- **PSR-3 Compatible**: Follows PHP logging standards

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
Log::setRunLevel('debug');

// Write log messages
Log::debug('Debug message');
Log::info('Information message');
Log::warning('Warning message');
Log::error('Error message');
Log::critical('Critical error');
```

## Destinations

The logging component supports writing to various destinations:

### Available Destinations

- **Echo**: Output to browser/console
- **File**: Write to log files
- **StdOut**: Write to standard output
- **StdErr**: Write to standard error
- **StdOutStdErr**: Write to both stdout and stderr based on level
- **SysLog**: System logging
- **Memory**: Store logs in memory for testing
- **Null**: Discard logs (useful for testing)
- **Email**: Send logs via email
- **Slack**: Post to Slack channels
- **Webhook**: Send to custom webhooks
- **Socket**: Send over network sockets
- **Nightwatch**: Send to Laravel Nightwatch monitoring service

### Formats

Each destination can use different formatting:

- **PlainText**: Human-readable text format
- **JSON**: Structured JSON output
- **CSV**: Comma-separated values
- **HTML**: HTML formatted output
- **HTMLEmail**: HTML optimized for emails
- **Raw**: Unformatted output
- **Slack**: Slack-specific formatting
- **Nightwatch**: Laravel Nightwatch-specific JSON formatting

## Usage Examples

### Basic Logging

```php
use Neuron\Log\Log;

// Configure run level
Log::setRunLevel('debug');

// Write log messages at different levels
Log::debug('Debug information');
Log::info('Application started');
Log::warning('Memory usage high');
Log::error('Failed to connect to database');
Log::critical('System failure');
```

### File Logging

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\PlainText;

// Create a file logger
$fileDestination = new File(new PlainText());
$fileDestination->open(['path' => '/var/log/app.log']);

$logger = new Logger($fileDestination);
$logger->setRunLevel('info');
$logger->info('Application event logged to file');
```

### JSON Logging

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Format\JSON;

// Log in JSON format for structured logging
$jsonDestination = new File(new JSON());
$jsonDestination->open(['path' => '/var/log/app.json']);

$jsonLogger = new Logger($jsonDestination);
$jsonLogger->error('Database error', [
    'query' => 'SELECT * FROM users',
    'error_code' => 1054
]);
```

### Slack Integration

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\SlackFormat;

$slack = new Slack(new SlackFormat());
$slack->open([
    'endpoint' => $_ENV['LOG_SLACK_WEBHOOK_URL'],
    'params' => [
        'channel'  => '#alerts',
        'username' => 'AppLogger',
        'icon_emoji' => ':warning:'
    ]
]);

$slackLogger = new Logger($slack);
$slackLogger->setRunLevel('error');

// Add to the multiplexer
Log::getInstance()->Logger->addLog($slackLogger);

// Now errors and above will also go to Slack
Log::error('Critical system error');
```

### Laravel Nightwatch Integration

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;

// Create Nightwatch destination with default channel
$nightwatch = new Nightwatch(new NightwatchFormat());
$nightwatch->open([
    'token' => $_ENV['NIGHTWATCH_TOKEN'],  // Your Nightwatch API token
    'endpoint' => 'https://nightwatch.laravel.com/api/logs', // Optional, uses default
    'batch_size' => 10,  // Optional: batch logs for better performance
    'timeout' => 5       // Optional: API request timeout in seconds
]);

$nightwatchLogger = new Logger($nightwatch);
$nightwatchLogger->setRunLevel('info');

// Log messages will be sent to Nightwatch
$nightwatchLogger->info('Application started');
$nightwatchLogger->error('Database connection failed', [
    'host' => 'db.example.com',
    'port' => 3306,
    'error' => 'Connection timeout'
]);

// For production use with the singleton
use Neuron\Log\Log;

// Add Nightwatch to the global logger
Log::getInstance()->Logger->addLog($nightwatchLogger);

// Now all logs at info level and above go to Nightwatch
Log::info('User logged in', ['user_id' => 123]);
Log::warning('API rate limit approaching', ['requests' => 950, 'limit' => 1000]);
Log::error('Payment processing failed', ['transaction_id' => 'txn_abc123']);
```

#### Nightwatch with Channels

The channel name is automatically passed to Nightwatch when using named channels:

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;

// Create a single Nightwatch format instance
$nightwatchFormat = new NightwatchFormat('neuron', 'my-app');

// Create Nightwatch destination
$nightwatch = new Nightwatch($nightwatchFormat);
$nightwatch->open(['token' => $_ENV['NIGHTWATCH_TOKEN']]);

// Create logger and add to multiple channels
$logger = new Logger($nightwatch);
Log::addChannel('audit', $logger);
Log::addChannel('security', $logger);
Log::addChannel('payments', $logger);

// Logs automatically include the channel name
Log::channel('audit')->info('User updated profile', ['user_id' => 123]);
// Nightwatch receives: {"channel": "audit", "message": "User updated profile", ...}

Log::channel('security')->warning('Failed login attempt', ['ip' => '192.168.1.1']);
// Nightwatch receives: {"channel": "security", "message": "Failed login attempt", ...}

Log::channel('payments')->error('Payment failed', ['amount' => 99.99]);
// Nightwatch receives: {"channel": "payments", "message": "Payment failed", ...}
```

The channel name appears in the Nightwatch dashboard for easy filtering and monitoring.

### Contextual Logging

```php
use Neuron\Log\Log;

// Set global context
Log::setContext('request_id', uniqid());
Log::setContext('user_id', $userId);
Log::setContext('session_id', session_id());

// All subsequent logs will include this context
Log::info('User action performed');
// Output: [2025-01-08 10:30:45][Info] [request_id=abc123, user_id=42, session_id=xyz789] User action performed
```

### Multiple Channels

```php
use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\File;
use Neuron\Log\Destination\Slack;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Format\SlackFormat;

// Create an audit logger
$auditFile = new File(new PlainText());
$auditFile->open(['path' => '/var/log/audit.log']);
$auditLogger = new Logger($auditFile);

// Create a real-time alerts logger
$alertSlack = new Slack(new SlackFormat());
$alertSlack->open([
    'endpoint' => $_ENV['SLACK_WEBHOOK'],
    'params' => ['channel' => '#alerts']
]);
$alertLogger = new Logger($alertSlack);

// Register channels
Log::addChannel('audit', $auditLogger);
Log::addChannel('alerts', $alertLogger);

// Use specific channels
Log::channel('audit')->info('User login', ['user' => $username]);
Log::channel('alerts')->critical('System down!');
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
$fileLogger = new Logger(new File(new JSON()));
$fileLogger->getDestination()->open(['path' => '/var/log/app.json']);
$fileLogger->setRunLevel('debug');

$consoleLogger = new Logger(new StdErr(new PlainText()));
$consoleLogger->setRunLevel('warning');

// Create multiplexer
$mux = new LogMux();
$mux->addLog($fileLogger);
$mux->addLog($consoleLogger);

// Logs go to both destinations based on their run levels
$mux->debug('Debug info');     // Only to file
$mux->warning('Warning!');     // To both file and console
$mux->error('Error occurred'); // To both file and console
```

### Custom Filters

```php
use Neuron\Log\Logger;
use Neuron\Log\Filter\IFilter;
use Neuron\Log\RunLevel;

class ProductionFilter implements IFilter {
    public function shouldLog(RunLevel $level, string $message, array $context): bool {
        // Don't log debug messages in production
        if ($level === RunLevel::DEBUG && $_ENV['APP_ENV'] === 'production') {
            return false;
        }
        
        // Don't log sensitive data
        if (str_contains($message, 'password') || str_contains($message, 'token')) {
            return false;
        }
        
        return true;
    }
}

$logger = new Logger($destination);
$logger->addFilter(new ProductionFilter());
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

if ($environment === 'production') {
    // Production: JSON to file
    $destination = new File(new JSON());
    $destination->open(['path' => '/var/log/app.json']);
    $runLevel = 'warning';
} else {
    // Development: Plain text to console
    $destination = new StdOut(new PlainText(true));
    $runLevel = 'debug';
}

$logger = new Logger($destination);
$logger->setRunLevel($runLevel);

Log::getInstance()->Logger->addLog($logger);
```

### Testing with Memory Logger

```php
use Neuron\Log\Logger;
use Neuron\Log\Destination\Memory;
use Neuron\Log\Format\Raw;

// For unit testing
$memoryDestination = new Memory(new Raw());
$testLogger = new Logger($memoryDestination);

$testLogger->error('Test error');
$testLogger->info('Test info');

// Retrieve logged messages
$logs = $memoryDestination->getLogs();
assert(count($logs) === 2);
assert($logs[0]['level'] === 'error');
```

## API Reference

### Log Levels

The following log levels are supported (from lowest to highest severity):

- `debug` - Detailed debug information
- `info` - Informational messages
- `notice` - Normal but significant events
- `warning` - Warning messages
- `error` - Error conditions
- `critical` - Critical conditions
- `alert` - Action must be taken immediately
- `emergency` - System is unusable

### Logger Methods

```php
$logger->debug(string $message, array $context = []);
$logger->info(string $message, array $context = []);
$logger->notice(string $message, array $context = []);
$logger->warning(string $message, array $context = []);
$logger->error(string $message, array $context = []);
$logger->critical(string $message, array $context = []);
$logger->alert(string $message, array $context = []);
$logger->emergency(string $message, array $context = []);
$logger->log(RunLevel $level, string $message, array $context = []);
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
