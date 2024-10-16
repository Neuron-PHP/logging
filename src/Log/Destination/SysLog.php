<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs log information to syslog.
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
	 * @param $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		$Level = 0;

		switch( $Data->Level )
		{
			case Log\ILogger::DEBUG:
				$Level = LOG_DEBUG;
				break;

			case Log\ILogger::INFO:
				$Level = LOG_INFO;
				break;

			case Log\ILogger::WARNING:
				$Level = LOG_WARNING;
				break;

			case Log\ILogger::ERROR:
				$Level = LOG_ERR;
				break;

			case Log\ILogger::FATAL:
				$Level = LOG_CRIT;
				break;
		}

		syslog( $Level, $Text );
	}
}
