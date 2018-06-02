<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
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
					$req = $GLOBALS['db_handle']->prepare('INSERT INTO agenda ( bt_id, bt_date, bt_event_loc, bt_title, bt_content ) VALUES (?, ?, ?, ?, ?)');
					$req->execute(array($event['id'], $event['ymdhisDate'], $event['loc'], $event['title'], $event['content'] ));
					break;
				case 'deleteEvent':
					$req = $GLOBALS['db_handle']->prepare('DELETE FROM agenda WHERE bt_id = ?');
					$req->execute(array($event['id']));
					break;
				case 'updateEvent':
					$req = $GLOBALS['db_handle']->prepare('UPDATE agenda SET bt_date = ?, bt_event_loc = ?, bt_title = ?, bt_content = ? WHERE bt_id = ?');
					$req->execute(array($event['ymdhisDate'], $event['loc'], $event['title'], $event['content'], $event['id']));
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
