# phpMultiLog
A not PSR compatible multi-transport logging and error/exception handling library for php.

Basic Usage

    $secretvar = "This should not exists in logs";
    $logger = new phpMultiLog ( "TestsphpMultiLog" ); // Give a unique id to your application

    // Errors and unhandled exceptions will go to these transports
    $logger->errTransportAdd ( "errStderr" );
    $logger->errTransportAdd ( "errFile", array (
	  	"filename" => "/tmp/phpMultiLogErr.log" 
    ) );

    // These variables will be excluded from logs
    // Add all your sensitive information (e.g. passwords) here
    $logger->errSecretsAdd ( array (
		    "secretvar" 
	) );

    // System messages will go to these transports
    $logger->logTransportAdd ( "sysSysLog", $logger::DEBUG );
    $logger->logTransportAdd ( "sysFile", $logger::DEBUG, array (
        "filename" => "/tmp/phpMultiLogSys.log" 
    ) );

    // This will go to every transport that has log level less than phpMultiLog::INFO
    $logger->logInfo("This is my message"); 