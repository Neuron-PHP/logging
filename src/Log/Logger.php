<?php

namespace Neuron\Log;

use Neuron\Log\Destination\DestinationBase;

/**
 * Class Logger
 * @package Neuron\Log
 */
class Logger implements ILogger
{
	private int $_RunLevel = ILogger::ERROR;
	private DestinationBase $_Destination;

	/**
	 * @param Destination\DestinationBase $Dest
	 */
	public function setDestination( Destination\DestinationBase $Dest )
	{
		$this->_Destination = $Dest;
	}

	/**
	 * @return mixed
	 */
	public function getDestination()
	{
		return $this->_Destination;
	}

	/**
	 * @param $Level
	 */
	public function setRunLevel( int $Level )
	{
		$this->_RunLevel = $Level;
	}

	/**
	 * @return int
	 */
	public function getRunLevel() : int
	{
		return $this->_RunLevel;
	}

	//////////////////////////////////////////////////////////////////////////

	/**
	 * @param Destination\DestinationBase $Dest
	 */
	public function __construct( Destination\DestinationBase $Dest )
	{
		$this->setDestination( $Dest );
	}

	/**
	 * @return mixed
	 */
	public function open()
	{
		return $this->getDestination()->open();
	}

	/**
	 *
	 */
	public function close()
	{
		$this->getDestination()->close();
	}

	/**
	 * @param string $Text
	 * @param int $Level
	 */
	public function log( string $Text, int $Level )
	{
		if( $Level >= $this->getRunLevel() )
		{
			$this->getDestination()->log( $Text, $Level );
		}
	}

	/**
	 * @param string $Text
	 */
	public function debug( string $Text )
	{
		$this->log( $Text, self::DEBUG );
	}

	/**
	 * @param $Text
	 */
	public function info( string $Text )
	{
		$this->log( $Text, self::INFO );
	}

	/**
	 * @param string $Text
	 */
	public function warning( string $Text )
	{
		$this->log( $Text, self::WARNING );
	}

	/**
	 * @param string $Text
	 */
	public function error( string $Text )
	{
		$this->log( $Text, self::ERROR );
	}

	/**
	 * @param string  $Text
	 */
	public function fatal( string $Text )
	{
		$this->log( $Text, self::FATAL );
	}
}

