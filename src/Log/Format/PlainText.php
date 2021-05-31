<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats data as plain text.
 */

class PlainText implements IFormat
{
	private $_ShowDate;

	/**
	 * PlainText constructor.
	 * @param bool $ShowDate
	 */
	public function __construct( $ShowDate = true )
	{
		$this->_ShowDate = $ShowDate;
	}

	/**
	 * @param Log\Data $Data
	 * @return string
	 */
	public function format( Log\Data $Data ) : string
	{
		$output = '';

		if( $this->_ShowDate )
		{
			$output .= date( "[Y-m-d G:i:s]", $Data->TimeStamp );
		}

		return  $output."[$Data->LevelText] $Data->Text";
	}
}

