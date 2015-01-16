<?php
date_default_timezone_set ( "UTC" );
error_reporting ( E_ALL );

syslog(LOG_WARNING, "Log message " . time());

use google\appengine\api\log\LogService;

// LogService API usage sample to display application logs for last 24 hours.

$options = [
// Fetch last 24 hours of log data
'start_time' => (time() - (24 * 60 * 60)) * 1e6,
// End time is Now
'end_time' => time() * 1e6,
// Include all Application Logs (i.e. your debugging output)
'include_app_logs' => true,
// Filter out log records based on severity
'minimum_log_level' => LogService::LEVEL_WARNING,
];

$logs = LogService::fetch($options);

foreach ($logs as $log) {
	echo '<ul>REQUEST LOG';
	echo '<li>IP: ', $log->getIp(), '</li>',
	'<li>Status: ', $log->getStatus(), '</li>',
	'<li>Method: ', $log->getMethod(), '</li>',
	'<li>Resource: ', $log->getResource(), '</li>';
	$end_date_time = $log->getEndDateTime();
	echo '<li>Date: ',$end_date_time->format('c'), '</li>';

	$app_logs = $log->getAppLogs();

	foreach ($app_logs as $app_log) {
		echo '<ul>APP LOG';
		echo '<li>Message: ', $app_log->getMessage(), '</li>';
		$app_log_date_time = $app_log->getDateTime();
		echo '<li>Date: ', $app_log_date_time->format('c'), '</li></ul>';
	}
	echo '</ul>';
}
