<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once '../inc/boot.php';
operate_session();

// gets contacts in JOSN format and saves them to DB
if (isset($_POST['save_contacts'])) {
	// TODO? Is XSRF protection here really relevant, considering the network latency related problems inherent with this?
	//$erreurs = valider_form_notes_ajax();

	$contacts = json_decode($_POST['save_contacts'], TRUE);

	/* remove duplicate IDs in newContacts (ex: 2 contacts created on same timestamp are differentiated here) */
	$contacts_ids = array();
	foreach ($contacts as $i => $contact) {
		if ($contact['action'] == 'newContact') {
			while (in_array($contact['id'], $contacts_ids)) {
				$contact['id']++;
				$contacts[$i]['id'] = $contact['id'];
			}
			$contacts_ids[] = $contact['id'];
		}
	}

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach ($contacts as $i => $contact) {
			switch ($contact['action']) {
				case 'newContact':
					$req = $GLOBALS['db_handle']->prepare('INSERT INTO contacts ( bt_id, bt_type, bt_title, bt_fullname, bt_surname, bt_birthday, bt_address, bt_phone, bt_email, bt_websites, bt_social, bt_image, bt_label, bt_notes, bt_stared, bt_other ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		 			$req->execute(array($contact['id'], $contact['type'], $contact['title'], $contact['fullname'], $contact['pseudo'], $contact['birthday'], json_encode($contact['address']), json_encode($contact['tel']), json_encode($contact['email']), json_encode($contact['websites']), json_encode($contact['social']), $contact['img'], $contact['label'], $contact['notes'], $contact['star'], $contact['other']));
					break;

				case 'deleteContact':
					$req = $GLOBALS['db_handle']->prepare('DELETE FROM contacts WHERE bt_id = ?');
					$req->execute(array($contact['id']));
					break;

				case 'updateContact':
					// if image has changer, we update it.
					// image is sent only if it has been changed, in order to limit data transfer.
					if ($contact['imgIsNew'] == TRUE) {
						$req = $GLOBALS['db_handle']->prepare('UPDATE contacts SET bt_title = ?, bt_fullname = ?, bt_surname = ?, bt_birthday = ?, bt_address = ?, bt_phone = ?, bt_email = ?, bt_websites = ?, bt_social = ?, bt_image = ?, bt_label = ?, bt_notes = ?, bt_stared = ?, bt_other = ? WHERE bt_id = ?');
						$array = array($contact['title'], $contact['fullname'], $contact['pseudo'], $contact['birthday'], json_encode($contact['address']), json_encode($contact['tel']), json_encode($contact['email']), json_encode($contact['websites']), json_encode($contact['social']), $contact['img'], $contact['label'], $contact['notes'], $contact['star'], $contact['other'], $contact['id']);
					} else {
						$req = $GLOBALS['db_handle']->prepare('UPDATE contacts SET bt_title = ?, bt_fullname = ?, bt_surname = ?, bt_birthday = ?, bt_address = ?, bt_phone = ?, bt_email = ?, bt_websites = ?, bt_social = ?, bt_label = ?, bt_notes = ?, bt_stared = ?, bt_other = ? WHERE bt_id = ?');
						$array = array($contact['title'], $contact['fullname'], $contact['pseudo'], $contact['birthday'], json_encode($contact['address']), json_encode($contact['tel']), json_encode($contact['email']), json_encode($contact['websites']), json_encode($contact['social']), $contact['label'], $contact['notes'], $contact['star'], $contact['other'], $contact['id']);

					}
	
		 			$req->execute($array);
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
