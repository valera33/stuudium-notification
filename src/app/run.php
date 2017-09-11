<?php

$rootDir = dirname(dirname(__DIR__));
$tmpDir = $rootDir . '/tmp';
$resultFile = $tmpDir . '/lastResult.html';
$dataFile = $tmpDir . '/lastData.json';
$cookieFile = $tmpDir . '/cookie.txt';
$templateDir = dirname(__DIR__) . '/templates';
$cacheDir = $tmpDir . '/cache';

require $rootDir . '/vendor/autoload.php';
require 'functions.php';

$options = getopt('', array('debug::'));
$debug = iiset($options['debug']) && $options['debug'] === 'true';

prepareIni($tmpDir, $debug);
prepareDirectory($tmpDir);
prepareDirectory($cacheDir);

use Jenssegers\Blade\Blade;
$blade = new Blade($templateDir, $cacheDir);

$dotenv = new Dotenv\Dotenv($rootDir . '/src/config');
$dotenv->load();

$recipients = $debug ? [$_ENV['DEBUG_RECIPIENT']] : json_decode($_ENV['RECIPIENTS']);

if (!$debug || !file_exists($dataFile)) {
	$data = loadPageFromStuudium([
		'url' => $_ENV['STUUDIUM_DATA_URL'],
		'loginUrl' => $_ENV['STUUDIUM_LOGIN_URL'],
		'username' => $_ENV['STUUDUIM_USERNAME'],
		'password' => $_ENV['STUUDIUM_PASSWORD'],
		'cookeFilePath' => $cookieFile,
	]);

	file_put_contents($resultFile, $data);
} else {
	echo 'Running in debug mode...' . PHP_EOL;

	$data = file_get_contents($resultFile);
}

$currentData = parseDashboard($data);

if ($debug) {
	print_r($_ENV);
	print_r($currentData);
}

$currentJson = json_encode($currentData);
$previousJson = @file_get_contents($dataFile);

if ($currentJson != $previousJson || $debug) {
	$previousData = json_decode($previousJson, true);

	$letter = prepareLetter($currentData, $previousData, $blade, $debug);

	file_put_contents($dataFile, $currentJson);

	echo 'Have updates, sending emails...' . PHP_EOL;

	foreach ($recipients as $to) {
		sendtMail($_ENV['MAIL_FROM'], $to, $_ENV['CHILD_NAME'] . ' uuendused koolis', $letter);
	}
} else {
	echo 'No updates, nothing to send' . PHP_EOL;
}
