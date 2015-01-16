<?php
date_default_timezone_set ( "UTC" );
error_reporting ( E_ERROR | E_PARSE );
set_time_limit ( 0 );

require_once '../vendor/autoload.php';

// Get crontab.txt from Google Drive
$crontab = 'https://docs.google.com/uc?id=0B_aFZjJihx3Jd2hhdjlTWmNWWUU&export=download';
$jobs = file_get_contents($crontab);

if ($jobs === FALSE) {
	error_log("Can't load " . $crontab);
	die();
}

foreach(preg_split("/((\r?\n)|(\r\n?))/", $jobs) as $line){
	if (strlen($line) == 0) {
		continue;
	}
	$job = explode(",", $line);
	if (count($job) !== 3) {
		error_log("Incorrect crontab line " . $line);
		continue;
	}
	for ($i = 0; $i < 3; $i++) {
		$job[$i] = trim($job[$i]);
	}
	if ((int) $job[0] !== 1) {
		continue;
	}

	$cron = Cron\CronExpression::factory($job[1]);
	/*
	 * Deterime if the cron is due to run based on the current date or a
	 * specific date.  This method assumes that the current number of
	 * seconds are irrelevant, and should be called once per minute.
	 *
	 * @param string|DateTime $currentTime (optional) Relative calculation date
	 *
	 * @return bool Returns TRUE if the cron is due to run or FALSE if not
	 */
	if ($cron->isDue()) {
		error_log("Started " . $job[2]);
		$response = file_get_contents($job[2]);
		if ($response === FALSE) {
			error_log("Failed " . $job[2]);
		}
	}
}
