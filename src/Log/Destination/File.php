<?php

namespace Neuron\Log\Destination;

use Neuron\Log;

/**
 * Outputs log data to a file.
 * Use the 'file_name' parameter in the open param array.
 */

class File extends DestinationBase
{
	private string $_Name;
	private $_File;

	/**
	 * @return string
	 */

	public function getFileName() : string
	{
		return $this->_Name;
	}

	/**
	 * Returns either the normal file name or, replaced %DATE% with the current date
	 * for example:
	 * 2021-06-03.log
	 *
	 * @param string $Mask
	 * @return string
	 */

	public function buildFileName( string $Mask ) : string
	{
		return str_replace( "%DATE%", date( "Y-m-d" ).".log", $Mask );
	}

	/**
	 * @param array $Params
	 * @return bool
	 */

	public function open( array $Params ) : bool
	{
		$this->_Name = $this->buildFileName( $Params[ 'file_name' ] );

		$this->_File = @fopen( $this->_Name, 'a' );

		if( !$this->_File )
		{
			return false;
		}

		return true;
	}

	/**
	 * Closes the open file handle associated with the log file.
	 */

	public function close()
	{
		if( $this->_File )
		{
			fclose( $this->_File );
		}
	}

	/**
	 * @param $Text
	 * @param Log\Data $Data
	 * @return void
	 *
	 * @SuppressWarnings(PHPMD)
	 */

	public function write( string $Text, Log\Data $Data )
	{
		fwrite(	$this->_File,
					"$Text\r\n" );
	}
}
