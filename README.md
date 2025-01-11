# About Neuron PHP

## Installation

Install php composer from https://getcomposer.org/

Install the neuron logging component:

    composer require neuron-php/logging

## Logging

A logger writes log entries to a destination using a specific format.

### Destinations

* Echo
* Email
* File
* Memory
* Null
* Slack
* Socket
* StErr
* StdOut
* StdOutStdErr
* SysLog
* Webhook

### Formats

* CSV
* HTML
* HTMLEmail
* JSON
* PlainText
* Raw
* Slack

## Multiplexer

A LogMux implements the ILogger interface but contains and writes to multiple logs
simultaneously. Each Logger can have a separate run level so only certain logs may
be written to depending on the run level of the independent Logger.


## Logger Singleton

The logger singleton is a LogMux wrapper that exists as a singleton/cross-cutting concern
so it can be accessed anywhere in the code base.

The default log is the Echoer using plain text format.

## Examples

### Logger Singleton
The quickest way to get started is using the singleton
facade:

    Log::setRunLevel( 'debug' );
    Log::debug( "Log message." );
    
### Slack
To configure slack:

    $Log = Log::getInstance();

    $Slack = new Slack(
        new PlainText( true )
    );

    $Slack->open(
        [
            'endpoint' => env( 'LOG_SLACK_WEBHOOK_URL' ),
            'params' => [
                'channel'  => env( 'LOG_SLACK_CHANNEL' ),
                'username' => 'Log'
            ]
        ]
    );

    $SlackLogger = new Logger( $Slack );
    $SlackLogger->setRunLevel( 'error' );
    $Log->Logger->addLog( $SlackLogger );

In this example, any log with a level of ERROR or
higher will be written to the slack channel.

### Contexts

    Log::setContext( 'UserId', $UserId );
    Log::setContext( 'SessionId', $SessionId );

    Log::info( "New login." );

Outputs:

[2022-06-03 12:00:00][Info] [UserId=15, SessionId=1234] New Login.

### Channels

Channels are independent loggers that can be accessed by name.

    $Log = Log::getInstance();

    $Slack = new Slack(
        new PlainText( true )
    );

    $Slack->open(
        [
            'endpoint' => env( 'LOG_SLACK_WEBHOOK_URL' ),
            'params' => [
                'channel'  => env( 'LOG_SLACK_CHANNEL' ),
                'username' => 'Log'
            ]
        ]
    );

    $SlackLogger = new Logger( $Slack );
    $SlackLogger->setRunLevel( 'info' );

    Log::addChannel( 'RealTime', $SlackLogger );

    // Write directly to slack an any time..

    Log::getChannel( 'RealTime' )->info( "Slack notification." );
