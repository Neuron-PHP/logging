<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Sends individual log data via email.
 */
class Email
{
	private string $_to;
	private string $_from;
	private string $_subject;

	/**
	 * @param array $params
	 * @return bool
	 */

	/**
	 * @param array $params [ 'to' => string, 'from' => string, 'subject' => string ]
	 * @return bool
	 */
	public function open( array $params ) : bool
	{
		$this->_to      = $params[ 'to' ];
		$this->_from    = $params[ 'from' ];
		$this->_subject = $params[ 'subject' ];

		return true;
	}

	/**
	 * @param string $text
	 * @param Log\Data $data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $data ): void
	{
		mail(
			$this->_to,
			$this->_subject,
			$text,
			"From: ".$this->_from
		);
	}
}
