<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats log data as plain text.
 */

class PlainText extends Base
{
	private bool $_showDate;

	/**
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 * PlainText constructor.
	 * @param bool $showDate
	 */
	public function __construct( bool $showDate = true )
	{
		$this->_showDate = $showDate;
	}

	/**
	 * @param Log\Data $data
	 * @return string
	 */
	public function format( Log\Data $data ) : string
	{
		$output = '';

		if( $this->_showDate )
		{
			$output .= date( "[Y-m-d G:i:s]", $data->timeStamp );
		}

		$context = $this->getContextString( $data->context );
		$output .= " /$data->levelText/ ";
		if( $context )
			$output .= "({$this->getContextString( $data->context )})";
		
		return $output." ".$data->text;
	}
}

