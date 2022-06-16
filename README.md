# About Neuron PHP

## Logging

Log to a multitude of unique destinations and formats simultaneously

### Destinations

* Echo
* Email
* File
* Null
* Slack
* Socket
* StErr
* StdOut
* Webhook

### Formats

* CSV
* HTML
* HTMLEmail
* JSon
* PlainText

## Installation

Install php composer from https://getcomposer.org/

Install the neuron logging component:

    composer require neuron-php/logging

## Examples

### Singleton Logger
The default log is the Echoer using plain text format.

The quickest way to get started is using the singleton
facade:

    // Optionally set the runlevel..
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

[2022-06-03 12:00:00][Info] [UserId=15, SessionId=1234] New Login
