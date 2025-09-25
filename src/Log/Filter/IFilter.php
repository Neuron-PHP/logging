<?php

namespace Neuron\Log\Filter;

use Neuron\Log\Data;

/**
 * Log filtering interface for the Neuron logging system.
 * 
 * This interface defines the contract for log filters, which provide conditional
 * processing and transformation of log entries before they reach their final
 * destination. Filters enable sophisticated log processing including:
 * 
 * - Conditional logging based on level, content, or context
 * - Log entry transformation and enrichment
 * - Rate limiting and sampling
 * - Sensitive data scrubbing and redaction
 * - Log routing and conditional forwarding
 * 
 * Filter behavior:
 * - Return the Data object (modified or unmodified) to continue processing
 * - Return null to prevent the log entry from being written
 * - Filters are applied in the order they were added to destinations
 * - Each filter receives the output of the previous filter in the chain
 * 
 * @package Neuron\Log\Filter
 * @author Neuron-PHP Framework
 * @version 3.0.0
 * @since 1.0.0
 * 
 * @example
 * ```php
 * class DebugOnlyFilter implements IFilter
 * {
 *     public function filter(Data $data): Data|null
 *     {
 *         // Only allow debug level logs
 *         return $data->getLevel()->isDebug() ? $data : null;
 *     }
 * }
 * 
 * class SensitiveDataFilter implements IFilter
 * {
 *     public function filter(Data $data): Data|null
 *     {
 *         $message = $data->getMessage();
 *         $message = preg_replace('/password=\w+/', 'password=***', $message);
 *         return $data->withMessage($message);
 *     }
 * }
 * ```
 */

interface IFilter
{
	/**
	 * @param Data $Data
	 * @return Data|null Return null if no logging should be performed.
	 */
	public function filter( Data $Data ) : Data | null;
}
