<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Sends individual log data via email.
 */
class Email
{
	private string $_To;
	private string $_From;
	private string $_Subject;

	/**
	 * @param array $Params
	 * @return bool
	 */

	/**
	 * @param array $Params [ 'to' => string, 'from' => string, 'subject' => string ]
	 * @return bool
	 */
	public function open( array $Params ) : bool
	{
		$this->_To      = $Params[ 'to' ];
		$this->_From    = $Params[ 'from' ];
		$this->_Subject = $Params[ 'subject' ];

		return true;
	}

	/**
	 * @param string $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data ): void
	{
		mail(
			$this->_To,
			$this->_Subject,
			$Text,
			"From: ".$this->_From
		);
	}
}
