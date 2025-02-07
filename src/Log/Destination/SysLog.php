<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Outputs log data to syslog.
 */
class SysLog extends DestinationBase
{
	/**
	 * @param array $Params
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function open( array $Params ) : bool
	{
		openlog('neuron', LOG_PID, LOG_USER );

		return true;
	}

	public function close(): void
	{
		closelog();
	}

	/**
	 * @param string $Text
	 * @param Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		$Level = 0;

		switch( $Data->Level )
		{
			case Log\RunLevel::DEBUG:
				$Level = LOG_DEBUG;
				break;

			case Log\RunLevel::INFO:
				$Level = LOG_INFO;
				break;

			case Log\RunLevel::WARNING:
				$Level = LOG_WARNING;
				break;

			case Log\RunLevel::ERROR:
				$Level = LOG_ERR;
				break;

			case Log\RunLevel::FATAL:
				$Level = LOG_CRIT;
				break;
		}

		syslog( $Level, $Text );
	}
}
