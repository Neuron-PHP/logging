<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Writes log data to a file.
 * Use the 'file_name' parameter in the open param array.
 */

class File extends DestinationBase
{
	private $_Name;
	private $_File;

	/**
	 * @param array $Params
	 * @return bool
	 */

	public function open( array $Params ) : bool
	{
		$this->_Name = $Params[ 'file_name' ];

		$this->_File = @fopen( $this->_Name, 'a' );

		if( !$this->_File )
		{
			return false;
		}

		return true;
	}

	public function close()
	{
		if( $this->_File )
		{
			fclose( $this->_File );
		}
	}

	/**
	 * @param $text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $text, Log\Data $Data )
	{
		fwrite(	$this->_File,
					"$text\r\n" );
	}
}
