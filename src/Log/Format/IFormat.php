<?php

namespace Neuron\Log\Format;

use Neuron\Log;

/**
 * Log formatting interface for the Neuron logging system.
 * 
 * This interface defines the contract for log formatters, which transform
 * log data objects into formatted strings suitable for various output
 * destinations. Formatters handle the presentation layer of logging,
 * converting structured log data into human-readable or machine-parseable formats.
 * 
 * Common format implementations:
 * - PlainText: Human-readable text format for console/file output
 * - JSON: Structured JSON format for log aggregation systems
 * - HTML: Web-formatted logs with styling and structure
 * - CSV: Tabular format for spreadsheet analysis
 * - Slack: Formatted messages for Slack channel notifications
 * 
 * @package Neuron\Log\Format
 * 
 * @example
 * ```php
 * class CustomFormat implements IFormat
 * {
 *     public function format(Log\Data $data): string
 *     {
 *         return sprintf('[%s] %s: %s',
 *             date('Y-m-d H:i:s', $data->getTimestamp()),
 *             $data->getLevel()->getName(),
 *             $data->getMessage()
 *         );
 *     }
 * }
 * ```
 */

interface IFormat
{
	/**
	 * @param Log\Data $data
	 * @return string
	 */
	public function format( Log\Data $data ) : string;
}
