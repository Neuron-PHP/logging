<?php

namespace Neuron\Log;

use Neuron\Log\Destination\DestinationBase;

/**
 * Logger implementation.
 */
class Logger implements ILogger
{
	private int             $_RunLevel = ILogger::ERROR;
	private DestinationBase $_Destination;
	private array           $_Context  = [];

	public function setContext( string $Name, string $Value ) : void
	{
		if( !$Value )
		{
			unset( $this->_Context[ $Name ] );
		}
		else
		{
			$this->_Context[ $Name ] = $Value;
		}
	}

	/**
	 * @return string
	 */
	protected function getContextString() : string
	{
		$Context = '';

		foreach( $this->_Context as $Name => $Value )
		{
			if( strlen( $Context ) )
			{
				$Context .= ', ';
			}

			$Context .= "$Name=$Value";
		}

		if( $Context )
		{
			return "[$Context] ";
		}

		return "";
	}

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
	 * @param string $Text
	 * @throws \Exception
	 */
	public function setRunLevelByText( string $Text )
	{
		$Level = self::DEBUG;

		switch( strtolower( $Text ) )
		{
			case 'debug':
				$Level = self::DEBUG;
				break;

			case 'info':
				$Level = self::INFO;
				break;

			case 'warning':
				$Level = self::WARNING;
				break;

			case 'error':
				$Level = self::ERROR;
				break;

			case 'fatal':
				$Level = self::FATAL;
				break;

			default:
				throw new \Exception( "Unrecognized run level '$Text'" );
		}

		$this->setRunLevel( $Level );
	}

	/**
	 * @param $Level
	 */
	public function setRunLevel( mixed $Level )
	{
		if( is_string( $Level ) )
		{
			$this->setRunLevelByText( $Level );
			return;
		}

		$this->_RunLevel = (int)$Level;
	}

	/**
	 * @return int
	 */
	public function getRunLevel() : int
	{
		return $this->_RunLevel;
	}

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
			$this->getDestination()->log( $this->getContextString().$Text, $Level );
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

