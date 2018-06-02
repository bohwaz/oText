<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

// DRAG AND DROP FILES : Upload
if (isset($_POST['do']) and $_POST['do'] == 'upload') {
	if (isset($_FILES['fichier'])) {
		$fichier = init_post_fichier();

		// avoid ID collisions
		$IDs = array();
		try {
			$req = $GLOBALS['db_handle']->query("SELECT bt_id FROM images");
			while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
				$IDs[] = $row['bt_id'];
			}
			$req->closeCursor();
			$IDs = array_flip($IDs);
		} catch (Exception $e) {
			die('file upload DB Error : '.$e->getMessage());
		}

		while (isset($IDs[$fichier['bt_id']])) {
			$fichier['bt_id']--;
		}

		$erreurs = valider_form_fichier($fichier);

		// on success
		if (empty($erreurs)) {
			traiter_form_fichier($fichier);
			echo '{';
				echo '"url": "fichiers.php?file_id='.$fichier['bt_id'].'&edit-'.count($IDs).'",';
				echo '"status": "success",';
				echo '"token": "'.new_token().'"';
			echo '}';
			exit;
		}
		// on error
		else {
			echo '{';
				echo '"url": "0",';
				echo '"status": "failure",';
				echo '"token": "0"';
			echo '}';
			exit;
		}
	}
	// if file is not send by JS but token() is ok, proceed with next files.
	elseif ( isset($_POST['token']) and check_token($_POST['token']) ) {
		echo '{';
			echo '"url": "0",';
			echo '"status": "failure",';
			echo '"token": "'.new_token().'"';
		echo '}';

	}
	// problem with file AND token : abord, Captain, my Captain! !
	else {
		echo '{';
			echo '"url": "0",';
			echo '"status": "failure",';
			echo '"token": "0"';
		echo '}';
	}
}

// DELETTING A FILE WITH AJAX
elseif (isset($_POST['do']) and $_POST['do'] == 'delete') {
	if (isset($_POST['file_id']) and preg_match('#\d{14}#',($_POST['file_id'])) ) {
		$_POST['supprimer'] = '1';
		$fichier = array();
		$fichier['bt_id'] = $_POST['file_id'];
		$fichier['bt_filename'] = get_entry('images', 'bt_filename', $_POST['file_id'], 'return');
		$fichier['bt_type'] = get_entry('images', 'bt_type', $_POST['file_id'], 'return');
		$fichier['bt_path'] = get_entry('images', 'bt_path', $_POST['file_id'], 'return');
		$return = traiter_form_fichier($fichier);

		if ($return == TRUE) {
			echo 'success';
		} else {
			echo var_dump($return);
		}
		exit;
	}
	echo 'failure';
	exit;
}
