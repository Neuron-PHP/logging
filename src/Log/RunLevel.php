<?php
namespace Neuron\Log;

enum RunLevel : int
{
	case DEBUG   = 0;		// Log all
	case INFO    = 10;		// Log informational
	case WARNING = 20;		// Log warning
	case ERROR   = 30;		// Log error
	case FATAL   = 40;		// Log fatal

	public function getLevel() : string
	{
		return match( $this )
		{
			RunLevel::DEBUG	=> "Debug",
			RunLevel::INFO		=> "Info",
			RunLevel::WARNING	=> "Warning",
			RunLevel::ERROR	=> "Error",
			RunLevel::FATAL	=> "Fatal"
		};
	}
}
