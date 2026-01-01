<?php

namespace Neuron\Log\Destination;

use Neuron\Log;
use Neuron\Log\Data;

/**
 * Appends log data to a file.
 * Use the 'file_name' parameter in the open param array.
 */

class File extends DestinationBase
{
	private string $_name;
	private $_file;

	/**
	 * @return string
	 */

	public function getFileName() : string
	{
		return $this->_name;
	}

	/**
	 * Returns either the normal file name or replaces %DATE% with the current date
	 * for example:
	 * 2021-06-03.log
	 *
	 * @param string $mask
	 * @return string
	 */

	public function buildFileName( string $mask ) : string
	{
		return str_replace( "%DATE%", date( "Y-m-d" ).".log", $mask );
	}

	/**
	 * @param array $params
	 * @return bool
	 */

	/**
	 * @param array $params [ 'file_name' => string ]
	 * @return bool
	 */
	public function open( array $params ) : bool
	{
		$this->_name = $this->buildFileName( $params[ 'file_name' ] );

		$this->_file = @fopen( $this->_name, 'a' );

		if( !$this->_file )
		{
			return false;
		}

		return true;
	}

	/**
	 * Closes the open file handle associated with the log file.
	 */

	public function close(): void
	{
		if( $this->_file )
		{
			fclose( $this->_file );
		}
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
		// Only write if file handle is valid
		if( !$this->_file || !is_resource( $this->_file ) )
		{
			return;
		}

		fwrite(	$this->_file,
					"$text\r\n" );
	}
}
