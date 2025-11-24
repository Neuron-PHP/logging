<?php
/**
 * Demonstration of array context support and PSR-3 style logging
 *
 * This example shows the new capabilities:
 * - Array context parameters
 * - Message interpolation
 * - Exception handling
 * - Complex object logging
 * - Special Nightwatch context keys
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Neuron\Log\Log;
use Neuron\Log\Logger;
use Neuron\Log\Destination\StdOut;
use Neuron\Log\Destination\Nightwatch;
use Neuron\Log\Format\PlainText;
use Neuron\Log\Format\Nightwatch as NightwatchFormat;

echo "=== Array Context and PSR-3 Style Logging Demo ===\n\n";

// Configure logging
Log::getInstance()->logger = null; // Reset
Log::setRunLevel( 'debug' );

echo "--- 1. Message Interpolation ---\n";
Log::info( 'User {userId} logged in from {ip} at {time}', [
	'userId' => 12345,
	'ip'     => '192.168.1.100',
	'time'   => date( 'H:i:s' )
] );

echo "\n--- 2. Array Context Support ---\n";
Log::warning( 'High memory usage detected', [
	'memory'   => [
		'used'  => memory_get_usage( true ),
		'peak'  => memory_get_peak_usage( true ),
		'limit' => ini_get( 'memory_limit' )
	],
	'process'  => getmypid(),
	'server'   => gethostname()
] );

echo "\n--- 3. Exception Handling ---\n";
try {
	throw new \RuntimeException( 'Database connection failed', 500 );
} catch( \Exception $e ) {
	Log::error( 'Failed to connect to database', [
		'exception' => $e,
		'host'      => 'db.example.com',
		'port'      => 3306,
		'retry'     => 3
	] );
}

echo "\n--- 4. Complex Nested Arrays ---\n";
Log::info( 'Order processed successfully', [
	'order' => [
		'id'       => 'ORD-' . uniqid(),
		'customer' => [
			'id'    => 789,
			'email' => 'customer@example.com',
			'tier'  => 'premium'
		],
		'items'    => [
			[ 'sku' => 'PROD-001', 'qty' => 2, 'price' => 29.99 ],
			[ 'sku' => 'PROD-002', 'qty' => 1, 'price' => 49.99 ]
		],
		'total'    => 109.97,
		'currency' => 'USD'
	],
	'fulfillment' => [
		'warehouse' => 'WEST-01',
		'carrier'   => 'FedEx',
		'tracking'  => 'TRK' . rand( 1000000, 9999999 )
	]
] );

echo "\n--- 5. Mixed Context Types ---\n";
Log::debug( 'Debug information', [
	'string'  => 'hello world',
	'integer' => 42,
	'float'   => 3.14159,
	'boolean' => true,
	'null'    => null,
	'array'   => [ 'nested', 'array', 'values' ],
	'object'  => new DateTime()
] );

echo "\n--- 6. Global + Local Context Merge ---\n";
// Set global context
Log::setContext( 'app', 'demo' );
Log::setContext( 'version', '2.0.0' );
Log::setContext( 'environment', 'development' );

// Log with local context - both will be included
Log::info( 'Application event with merged context', [
	'event'   => 'user_action',
	'action'  => 'profile_update',
	'user_id' => 456
] );

echo "\n--- 7. Nightwatch Special Context Keys ---\n";
// Create Nightwatch logger to show special handling
$nightwatchFormat = new NightwatchFormat( 'demo', 'array-context-example' );
$nightwatch = new Nightwatch( $nightwatchFormat );
$nightwatch->open( [ 'token' => 'demo-token' ] ); // Won't actually send

$nightwatchLogger = new Logger( $nightwatch );
$nightwatchLogger->setRunLevel( 'debug' );

// Add to a channel
Log::addChannel( 'monitoring', $nightwatchLogger );

// Log with special Nightwatch context keys
Log::channel( 'monitoring' )->error( 'Performance degradation detected', [
	'performance' => [
		'duration' => 1234.5,
		'memory'   => 104857600,
		'cpu'      => 0.85
	],
	'tags'        => [ 'critical', 'performance', 'alert' ],
	'user'        => [
		'id'    => 999,
		'email' => 'admin@example.com',
		'role'  => 'administrator'
	],
	'request'     => [
		'id'     => 'req-' . uniqid(),
		'url'    => '/api/v1/reports',
		'method' => 'POST'
	],
	'custom_data' => 'This stays in regular context'
] );

echo "\n--- 8. Object with __toString() ---\n";
class CustomId {
	private string $id;

	public function __construct( string $id ) {
		$this->id = $id;
	}

	public function __toString() {
		return "ID[{$this->id}]";
	}
}

Log::info( 'Custom object logging', [
	'transaction_id' => new CustomId( 'TXN-12345' ),
	'session_id'     => new CustomId( 'SESS-67890' )
] );

echo "\n--- 9. Array Values in setContext ---\n";
Log::setContext( 'tags', [ 'production', 'critical', 'monitored' ] );
Log::setContext( 'servers', [ 'web01', 'web02', 'web03' ] );

Log::error( 'Cluster failure detected' );

echo "\n--- 10. Demonstrating Channel + Array Context ---\n";
// Create audit channel with file destination
$auditLogger = new Logger( new StdOut( new PlainText() ) );
Log::addChannel( 'audit', $auditLogger );

// Channel name is automatically included, plus array context
Log::channel( 'audit' )->info( 'User {user} performed {action}', [
	'user'        => 'admin@example.com',
	'action'      => 'delete_records',
	'records'     => [ 101, 102, 103 ],
	'confirmed'   => true,
	'timestamp'   => time()
] );

echo "\n=== Summary ===\n";
echo "The logging package now supports:\n";
echo "1. PSR-3 style message interpolation with {placeholders}\n";
echo "2. Array contexts passed as second parameter\n";
echo "3. Automatic exception formatting\n";
echo "4. Complex nested arrays and objects\n";
echo "5. Special Nightwatch context keys (exception, performance, tags, etc.)\n";
echo "6. Mixed type handling (strings, arrays, objects, etc.)\n";
echo "7. Global + local context merging\n";
echo "8. Channel-aware logging with array contexts\n\n";

echo "All while maintaining backward compatibility!\n";