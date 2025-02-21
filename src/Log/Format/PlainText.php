<?php

namespace Neuron\Log\Format;

use \Neuron\Log;

/**
 * Formats log data as plain text.
 */

class PlainText extends Base
{
	private bool $_ShowDate;

	/**
	 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
	 * PlainText constructor.
	 * @param bool $ShowDate
	 */
	public function __construct( bool $ShowDate = true )
	{
		$this->_ShowDate = $ShowDate;
	}

	/**
	 * @param Log\Data $Data
	 * @return string
	 */
	public function format( Log\Data $Data ) : string
	{
		$Output = '';

		if( $this->_ShowDate )
		{
			$Output .= date( "[Y-m-d G:i:s]", $Data->TimeStamp );
		}

		$Context = $this->getContextString( $Data->Context );
		$Output .= " /$Data->LevelText/ ";
		if( $Context )
			$Output .= "({$this->getContextString( $Data->Context )})";
		
		return $Output." ".$Data->Text;
	}
}

