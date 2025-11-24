<?php
namespace Neuron\Log;

/**
 * Logging run level enumeration for message prioritization and filtering.
 * 
 * This enum defines the standard logging levels used throughout the Neuron
 * logging system for message classification, filtering, and prioritization.
 * Each level has a numeric value that allows for easy comparison and
 * hierarchical filtering, where higher values represent more critical messages.
 * 
 * Logging level hierarchy (lowest to highest priority):
 * - DEBUG (0): Detailed diagnostic information for development and troubleshooting
 * - INFO (10): General informational messages about application flow
 * - NOTICE (15): Normal but significant events
 * - WARNING (20): Warning messages about potentially harmful situations
 * - ERROR (30): Error events that allow the application to continue running
 * - CRITICAL (40): Critical conditions that need attention
 * - ALERT (45): Action must be taken immediately
 * - EMERGENCY (50): System is unusable
 * 
 * Key features:
 * - Numeric values allow easy level comparison and filtering
 * - String representation for human-readable output
 * - Integration with log destinations and filters
 * - Support for minimum level filtering
 * - Consistent severity classification across all loggers
 * 
 * @package Neuron\Log
 * 
 * @example
 * ```php
 * // Using run levels in logging
 * $logger = new Logger();
 * $logger->setMinimumLevel(RunLevel::WARNING);
 * 
 * // These will be logged (WARNING and above)
 * $logger->log(RunLevel::WARNING, 'Deprecated function used');
 * $logger->log(RunLevel::ERROR, 'Database connection failed');
 * $logger->log(RunLevel::CRITICAL, 'Critical system failure');
 * 
 * // These will be filtered out (below WARNING)
 * $logger->log(RunLevel::DEBUG, 'Variable value: ' . $var);
 * $logger->log(RunLevel::INFO, 'User logged in');
 * 
 * // Level comparison
 * if ($currentLevel->value >= RunLevel::ERROR->value) {
 *     $this->sendAlertEmail($message);
 * }
 * ```
 */
enum RunLevel : int
{
	case DEBUG     = 0;		// Log all
	case INFO      = 10;		// Log informational
	case NOTICE    = 15;		// Log normal but significant events
	case WARNING   = 20;		// Log warning
	case ERROR     = 30;		// Log error
	case CRITICAL  = 40;		// Log critical conditions
	case ALERT     = 45;		// Action must be taken immediately
	case EMERGENCY = 50;		// System is unusable

	public function getLevel() : string
	{
		return match( $this )
		{
			RunLevel::DEBUG     => "Debug",
			RunLevel::INFO      => "Info",
			RunLevel::NOTICE    => "Notice",
			RunLevel::WARNING   => "Warning",
			RunLevel::ERROR     => "Error",
			RunLevel::CRITICAL  => "Critical",
			RunLevel::ALERT     => "Alert",
			RunLevel::EMERGENCY => "Emergency"
		};
	}
}
