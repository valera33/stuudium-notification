<?php

function prepareIni($tmpDir, $debug) {
	ini_set('error_reporting', E_ALL);

	if ($debug) {
		return;
	}

	ini_set('display_errors', 0);
	ini_set('log_errors', 1);
	ini_set('error_log', $tmpDir . '/error-' . date('Y-m') . '.log');
}

function loadPageFromStuudium($requestData) {
	$postFields = sprintf('data[User][username]=%s&data[User][password]=%s',
		$requestData['username'], $requestData['password']);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_NOBODY, false);
	curl_setopt($ch, CURLOPT_URL, $requestData['loginUrl']);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

	curl_setopt($ch, CURLOPT_COOKIEJAR, $requestData['cookeFilePath']);
	// curl_setopt($ch, CURLOPT_COOKIE, 'cookiename=0');
	curl_setopt($ch, CURLOPT_USERAGENT,
		'Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

	if (isset($_ENV['CURL_INTERFACE'])) {
		curl_setopt($ch, CURLOPT_INTERFACE, $_ENV['CURL_INTERFACE']);
	}

	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
	curl_exec($ch);

	curl_setopt($ch, CURLOPT_URL, $requestData['url']);

	$output = curl_exec($ch);
	curl_close($ch);

	return $output;
}

function parseDashboard($data) {
	$result = [
		'marks' => [],
	];

	if (!preg_match_all('/<div class=\"entry_content\">(.+)<\/div>[^<>]*<!-- .entry_content -->/isU', $data, $matches)) {
		throw new Exception('Parse Error', 1);
	}

	foreach ($matches[1] as $entry) {
		// mark
		if (preg_match('/<span class=\"grade[^\"]*"><span[^<>]*>(.+)<\/span>/isU', $entry, $m1)) {
			$mark = trim($m1[1]);
		} else {
			$mark = '';
		}

		if (preg_match('/<span class=\"grade_notes\">([^<>]*)<\/span>/isU', $entry, $m2)) {
			$notes = trim($m2[1]);
		} else {
			$notes = '';
		}

		// absence
		if (preg_match('/<span class=\"grade_param_is_absent ([^\"]*)\"><abbr title="([^<>]*)">(.+)<\/abbr>/isU', $entry, $a1)) {
			$mark = 'PUUDUS';
			$notes = $a1[1] == 'grade_param_is_absent_excused' ? 'OK: ' . $a1[2] : '!NOT OK!';
		}

		if (preg_match('/<a class=\"subject_name\"[^<>]*>(.*),/isU', $entry, $m3) &&
			preg_match('/<span class=\"lesson_notes\">([^<>]*)<\/span>/isU', $entry, $m4)) {
			$lesson = trim(strip_tags($m3[1])) . ' - ' . trim($m4[1]);
		} elseif (preg_match('/<a class=\"subject_name\"[^<>]*>(.*)<\/a>/isU', $entry, $m3) &&
			preg_match('/<span class=\"grade_type\">([^<>]*)<\/span>/isU', $entry, $m4)) {
			$lesson = trim(strip_tags($m3[1])) . trim($m4[1]);
		} else {
			$lesson = '';
		}

		if (preg_match('/<span class=\"lesson_date\">([^<>]*)<\/span>/isU', $entry, $m5)) {
			$date = trim(strip_tags($m5[1]));
		} else {
			$date = '';
		}

		$signature = md5($mark . $notes . $lesson);
		$result['marks'][] = array('mark' => $mark, 'nr' => (int) $mark, 'date' => $date, 'notes' => $notes,
			'lesson' => $lesson, 'signature' => $signature);
	}

	preg_match('/<td>Puudumised kokku<\/td>[^<>]*<td class="nr">(.*)<\/td>[^<>]*<td class="nr unexcused">(.*)<\/td>/isU', $data, $matches);
	$result['absence'] = array('all' => $matches[1], 'bad' => $matches[2]);

	return $result;
}

function prepareLetter($currentData, $previousData, $blade, $debug) {
	$data = [
		'name' => $_ENV['CHILD_NAME'],
		'marks' => [],
		'previousMarks' => [],
		'url' => $_ENV['STUUDIUM_URL'],
	];

	if (!isset($previousData) || $currentData['absence']['all'] != $previousData['absence']['all'] || $debug) {
		$data['absenceTotal'] = ['from' => $previousData['absence']['all'], 'to' => $currentData['absence']['all']];
	}

	if (!isset($previousData) || $currentData['absence']['bad'] != $previousData['absence']['bad'] || $debug) {
		$data['absenceBad'] = ['from' => $previousData['absence']['bad'], 'to' => $currentData['absence']['bad']];
	}

	if (!isset($previousData)) {
		$data['marks'] = $currentData['marks'];
	} elseif ($currentData['marks'] != $previousData['marks'] || $debug) {
		$previousSignatures = array_column($previousData['marks'], 'signature');

		foreach ($currentData['marks'] as $mark) {
			if (!in_array($mark['signature'], $previousSignatures) || $debug) {
				$data['marks'][] = $mark;
			} else {
				$data['previousMarks'][] = $mark;
			}
		}
	}

	return $blade->make('notification', $data);
}

function sendtMail($from, $to, $subject, $message) {
	// Sendmail
	if ($_ENV['MAIL_DRIVER'] === 'sendmail') {
		$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');
	}

	// SMTP
	if ($_ENV['MAIL_DRIVER'] === 'smtp') {
		$transport = Swift_SmtpTransport::newInstance($_ENV['MAIL_HOST'], $_ENV['MAIL_PORT'], $_ENV['MAIL_ENCRYPTION'])
			->setUsername($_ENV['MAIL_USERNAME'])
			->setPassword($_ENV['MAIL_PASSWORD']);
	}

	// Mail
	if ($_ENV['MAIL_DRIVER'] === 'mail') {
		$transport = Swift_MailTransport::newInstance();
	}

	$mailer = Swift_Mailer::newInstance($transport);

	$message = Swift_Message::newInstance($subject)
		->setFrom($from)
		->setTo($to)
		->setBody($message, 'text/html');

	return $mailer->send($message);
}

function prepareDirectory($path) {
	if (!file_exists($path)) {
		mkdir($path, 0777, true);
	}
}
