<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once '../inc/boot.php';
operate_session();

// gets notes in JOSN format and saves them to DB
if (isset($_POST['save_notes'])) {
	// TODO? Is XSRF protection here really relevant, considering the network latency related problems inherent with this?
	//$erreurs = valider_form_notes_ajax();

	$notes = json_decode($_POST['save_notes'], TRUE);

	/* remove duplicate IDs in newNotes (ex: 2 notes created on same timestamp are differentiated here) */
	$notes_ids = array();
	foreach ($notes as $i => $note) {
		if ($note['action'] == 'newNote') {
			while (in_array($note['id'], $notes_ids)) {
				$note['id']++;
				$notes[$i]['id'] = $note['id'];
			}
			$notes_ids[] = $note['id'];
		}
	}

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach ($notes as $i => $note) {
			switch ($note['action']) {
				case 'newNote':
					$req = $GLOBALS['db_handle']->prepare('INSERT INTO notes ( bt_id, bt_title, bt_content, bt_color, bt_statut, bt_pinned ) VALUES (?, ?, ?, ?, ?, ?)');
					$req->execute(array($note['id'], $note['title'], $note['content'], $note['color'], $note['isstatut'], $note['ispinned'] ));
					break;
				case 'deleteNote':
					$req = $GLOBALS['db_handle']->prepare('DELETE FROM notes WHERE bt_id = ?');
					$req->execute(array($note['id']));
					break;
				case 'updateNote':
					$req = $GLOBALS['db_handle']->prepare('UPDATE notes SET bt_title = ?, bt_content = ?, bt_color = ?, bt_statut = ?, bt_pinned = ? WHERE bt_id = ?');
					$req->execute(array($note['title'], $note['content'], $note['color'], $note['isstatut'], $note['ispinned'], $note['id']));
					break;
			}
		}

		$GLOBALS['db_handle']->commit();
		die('Success');
	} catch (Exception $e) {
		die('SQL Notes-update Error: '.$e->getMessage());
	}

}

exit;
