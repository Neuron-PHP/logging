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
	 * @param array $params
	 * @return bool
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function open( array $params ) : bool
	{
		openlog('neuron', LOG_PID, LOG_USER );

		return true;
	}

	public function close(): void
	{
		closelog();
	}

	/**
	 * @param string $text
	 * @param Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		$level = 0;

		switch( $data->level )
		{
			case Log\RunLevel::DEBUG:
				$level = LOG_DEBUG;
				break;

			case Log\RunLevel::INFO:
				$level = LOG_INFO;
				break;

			case Log\RunLevel::NOTICE:
				$level = LOG_NOTICE;
				break;

			case Log\RunLevel::WARNING:
				$level = LOG_WARNING;
				break;

			case Log\RunLevel::ERROR:
				$level = LOG_ERR;
				break;

			case Log\RunLevel::CRITICAL:
				$level = LOG_CRIT;
				break;

			case Log\RunLevel::ALERT:
				$level = LOG_ALERT;
				break;

			case Log\RunLevel::EMERGENCY:
				$level = LOG_EMERG;
				break;
		}

		syslog( $level, $text );
	}
}
