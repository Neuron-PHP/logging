<?php

namespace Neuron\Log\Format;

use Neuron\Log;
use Neuron\Log\Format\IFormat;

abstract class Base implements IFormat
{
	protected function getContextString( array $contextList ) : string
	{
		$context = '';

		foreach( $contextList as $name => $value )
		{
			if( strlen( $context ) )
			{
				$context .= '|';
			}

			// Convert value to string representation
			$stringValue = $this->valueToString( $value );
			$context .= "$name=$stringValue";
		}

		if( $context )
		{
			return $context;
		}

		return "";
	}

	/**
	 * Converts a value to string representation for logging.
	 *
	 * @param mixed $value
	 * @return string
	 */
	protected function valueToString( mixed $value ): string
	{
		if( is_null( $value ) )
		{
			return 'null';
		}

		if( is_bool( $value ) )
		{
			return $value ? 'true' : 'false';
		}

		if( is_scalar( $value ) )
		{
			return (string) $value;
		}

		if( is_array( $value ) )
		{
			return json_encode( $value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		}

		if( is_object( $value ) )
		{
			if( method_exists( $value, '__toString' ) )
			{
				return (string) $value;
			}

			if( $value instanceof \Throwable )
			{
				return get_class( $value ) . ': ' . $value->getMessage();
			}

			return get_class( $value );
		}

		return gettype( $value );
	}

	abstract public function format( Log\Data $data ): string;
}
