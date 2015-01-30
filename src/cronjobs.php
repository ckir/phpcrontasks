<?php
date_default_timezone_set ( "UTC" );
error_reporting ( E_ERROR | E_PARSE );

use phpMultiLog\phpMultiLog;

require_once '../vendor/autoload.php';

$logger = new phpMultiLog ( "phpcron", true, null );
$logger->errTransportAdd ( "errStderr", array () );
$logger->logTransportAdd ( "sysSysLog", $logger::INFO, array () );
//$logger->logTransportAdd ( "sysEcho", $logger::INFO, array () );

$cronfile = file_get_contents ( 'https://docs.google.com/uc?id=0B_aFZjJihx3JRHQ5NVMzVXR3N1k&export=download' );
$cronfile = json_decode ( $cronfile, true );

if (! $cronfile) {
	$logger->logCritical ( "Error in cronfile" );
	die ();
}

foreach ( $cronfile ['cronjobs'] as $timezones ) {
	$timezone = $timezones ['timezone'];
	$logger->logDebug ( "Processing timezone: " . $timezone . ". Time at server (" . date_default_timezone_get () . ") is: " . getServerTine () . ". === Time at timezone ($timezone) is: " . getTimezoneTine ( $timezone ) );
	
	foreach ( $timezones ["jobs"] as $job ) {
		if (! $job ["active"]) {
			$logger->logDebug($job ["url"] . " is disabled");
			continue;
		}
		$cron = Cron\CronExpression::factory ( $job ["cron"] );
		

		// Deterime if the cron is due to run based on the current date or
		// a specific date. This method assumes that the current number of
		// seconds are irrelevant, and should be called once per minute.
		// @param string|DateTime $currentTime (optional) Relative calculation date 
		// @return bool Returns TRUE if the cron is due to run or FALSE if not

		if ($cron->isDue ( new \DateTime ( "now", new DateTimeZone ( $timezone ) ) )) {
			
			$joburl = $job ["url"];
			$tssrv = new \DateTime ( "now", new \DateTimeZone ( date_default_timezone_get () ) );
			$tssrv = $tssrv->format ( "Y-m-d_H-i-s" );
			$tstz = new \DateTime ( "now", new \DateTimeZone ( $timezone ) );
			$tstz = $tstz->format ( "Y-m-d_H-i-s" );
			if (preg_match ( "/keepalive/", $job ["url"] )) {
				$joburl = $job ["url"] . "?tssrv=$tssrv&tstz=$tstz";
			}
			
			$response = @file_get_contents($joburl);
			//$response = "OK";
			if ($response === FALSE) {
				$logger->logInfo ("At $tstz local time ($timezone) Failed to start $joburl because " . json_encode ( $http_response_header ) );
			} else {
				$logger->logInfo ("At $tstz local time ($timezone) Started " . $joburl . " Response : " . json_encode ( $response ) );
			}
			
		} else {
			$tssrv = $cron->getNextRunDate ( new \DateTime ( "now", new \DateTimeZone ( date_default_timezone_get () ) ) );
			$tssrv = $tssrv->format ( "Y-m-d H:i:s" );
			$tstz = $cron->getNextRunDate ( new \DateTime ( "now", new \DateTimeZone ( $timezone ) ) );
			$tstz = $tstz->format ( "Y-m-d H:i:s" );
			$logger->logDebug ( "I will start " . $job ['url'] . " at $tssrv (" . date_default_timezone_get () . ") server time or $tstz ($timezone) local time" );
		}
	}
}
function getServerTine() {
	$st = new \DateTime ( "now", new DateTimeZone ( date_default_timezone_get () ) );
	return $st->format ( "Y-m-d H:i:s" );
}
function getTimezoneTine($timezone) {
	$st = new \DateTime ( "now", new DateTimeZone ( $timezone ) );
	return $st->format ( "Y-m-d H:i:s" );
}