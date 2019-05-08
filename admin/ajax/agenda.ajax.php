<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once '../inc/boot.php';

// Get the Events in an .ics format
// only test here is on install UID.
if (isset($_GET['get_ics'], $_GET['guid'])) {
	if ($_GET['guid'] == BLOG_UID) {
		$GLOBALS['db_handle'] = open_base();

		$query = "SELECT * FROM agenda ORDER BY bt_date_start DESC LIMIT 50";
		$tableau = liste_elements($query, array());

		header('Content-Type:text/calendar', true);
		$out = '';
		$out .= 'BEGIN:VCALENDAR'."\r\n";
		$out .= 'VERSION:2.0'."\r\n";
		$out .= 'PRODID:-// oText '.BLOGOTEXT_VERSION.' Agenda MNG'."\r\n";
		$out .= 'METHOD:PUBLISH'."\r\n";
		$out .= 'X-WR-TIMEZONE:'.$GLOBALS['fuseau_horaire']."\r\n";
		foreach ($tableau as $i => $event) {
			$out .= 'BEGIN:VEVENT'."\r\n";
			$out .= 'UID:'.$event['bt_id']."\r\n";
			$out .= 'DTSTAMP:'.gmdate('Ymd\THis', strtotime($event['bt_date_start'])).'Z'."\r\n";
			$out .= wordwrap('SUMMARY:'.str_replace(array("\n", "\n", "\r\n"), "\\n", $event['bt_title']), 74, "\r\n\t", true)."\r\n";
			$out .= 'DTSTART:'.gmdate('Ymd\THis', strtotime($event['bt_date_start'])).'Z'."\r\n";
			$out .= 'DTEND:'.gmdate('Ymd\THis', strtotime($$event['bt_date_end'])).'Z'."\r\n";
			$out .= wordwrap('LOCATION:'.str_replace(array("\n", "\n", "\r\n"), "\\n", $event['bt_event_loc']), 74, "\r\n\t", true)."\r\n";
			$out .= wordwrap('DESCRIPTION:'.str_replace(array("\n", "\n", "\r\n"), "\\n", $event['bt_content']), 74, "\r\n\t", true)."\r\n";
			$out .= 'END:VEVENT'."\r\n";
		}
		$out .= 'END:VCALENDAR'."\r\n";
		echo $out;
		die();
	} else {
		die();
	}
}

operate_session();

// gets events in JOSN format and saves them to DB
if (isset($_POST['save_events'])) {
	// TODO? Is XSRF protection here really relevant, considering the network latency related problems inherent with this?
	//$erreurs = valider_form_notes_ajax();

	$events = json_decode($_POST['save_events'], TRUE);

	/* remove duplicate IDs in newEvents (ex: 2 events created on same timestamp are differentiated here) */
	$events_ids = array();
	foreach ($events as $i => $event) {
		if ($event['action'] == 'newEvent') {
			while (in_array($event['id'], $events_ids)) {
				$event['id']++;
				$events[$i]['id'] = $event['id'];
			}
			$events_ids[] = $event['id'];
		}
	}

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach ($events as $i => $event) {
			switch ($event['action']) {
				case 'newEvent':
					$req = $GLOBALS['db_handle']->prepare('INSERT INTO agenda ( bt_id, bt_date_start, bt_date_end, bt_color, bt_event_loc, bt_title, bt_persons, bt_content ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
					$req->execute(array($event['id'], $event['date_start'], $event['date_end'], $event['color'], $event['loc'], $event['title'], json_encode($event['persons']), $event['content'] ));
					break;
				case 'deleteEvent':
					$req = $GLOBALS['db_handle']->prepare('DELETE FROM agenda WHERE bt_id = ?');
					$req->execute(array($event['id']));
					break;
				case 'updateEvent':
					$req = $GLOBALS['db_handle']->prepare('UPDATE agenda SET bt_date_start = ?, bt_date_end = ?, bt_color = ?, bt_event_loc = ?, bt_title = ?, bt_persons = ?, bt_content = ? WHERE bt_id = ?');
					$req->execute(array($event['date_start'], $event['date_end'], $event['color'], $event['loc'], $event['title'], json_encode($event['persons']), $event['content'], $event['id']));
					break;
			}
		}



		$GLOBALS['db_handle']->commit();
		die('Success');
	} catch (Exception $e) {
		die('SQL Agenda-update Error: '.$e->getMessage());
	}

}

exit;
