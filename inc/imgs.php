<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***


/*
   À partir du chemin vers une image, trouve la miniature correspondante.
   retourne le chemin de la miniature.
   le nom d’une image est " image.ext ", celui de la miniature sera " image-thb.ext "
*/
function chemin_thb_img($filepath) {
	$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
	// prend le nom, supprime l’extension et le point, ajoute le " -thb.jpg ", puisque les miniatures de BT sont en JPG.
	$miniature = substr($filepath, 0, -(strlen($ext)+1)).'-thb.jpg'; // "+1" is for the "." between name and ext.
	return $miniature;
}

function chemin_thb_img_test($filepath) {
	$thb = chemin_thb_img($filepath);
	if (file_exists($thb)) {
		return $thb;
	} else {
		return $filepath;
	}
}


// filepath : image to create a thumbnail from
function create_thumbnail($filepath) {
	// if GD library is not loaded by PHP, abord. Thumbnails are not required.
	if (!extension_loaded('gd')) return;
	$maxwidth = '700';
	$maxheight = '200';
	$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

	// si l’image est petite (<200), pas besoin de miniature
	list($width_orig, $height_orig) = getimagesize($filepath);
	if ($width_orig <= 200 and $height_orig <= 200) return;
	// largeur et hauteur maximale
	// Cacul des nouvelles dimensions
	if ($width_orig == 0 or $height_orig == 0) return;
	if ($maxwidth and ($width_orig < $height_orig)) {
		$maxwidth = ($maxheight / $height_orig) * $width_orig;
	} else {
		$maxheight = ($maxwidth / $width_orig) * $height_orig;
	}

	// open file with correct format
	$thumb = imagecreatetruecolor($maxwidth, $maxheight);
	imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
	switch ($ext) {
		case 'jpeg':
		case 'jpg': $image = imagecreatefromjpeg($filepath); break;
		case 'png': $image = imagecreatefrompng($filepath); break;
		case 'gif': $image = imagecreatefromgif($filepath); break;
		default : return;
	}

	// resize
	imagecopyresampled($thumb, $image, 0, 0, 0, 0, $maxwidth, $maxheight, $width_orig, $height_orig);
	imagedestroy($image);

	// enregistrement en JPG (meilleur compression) des miniatures
	$destination = chemin_thb_img($filepath); // construit le nom de fichier de la miniature
	imagejpeg($thumb, $destination, 70); // compression à 70%
	imagedestroy($thumb);

}



// HANDLES FILE RM/ADD/EDIT-FORM
function traiter_form_fichier($fichier) {
	$dossier = ($fichier['bt_type'] == 'image') ? DIR_IMAGES.$fichier['bt_path'] : DIR_DOCUMENTS;
	if (FALSE === creer_dossier($dossier, 0)) {
		die($GLOBALS['lang']['err_file_write']);
	}

	// ADDING A NEW FILE
	if ( isset($_POST['upload']) ) {
		$prefix = '';
		// tests if the same file is already there (based on checksum)
		try {
			$req = $GLOBALS['db_handle']->prepare("SELECT * FROM images WHERE bt_checksum=?");
			$req->execute(array($fichier['bt_checksum']));
			$result = $req->fetch();
			if (!empty($result)) return $result;
		} catch (Exception $e) {
			die('Erreur testChecksum : '.$e->getMessage());
		}
		// avoid filename collisions
		// if filename exists, add random prefix
		while (file_exists($dossier.'/'.$prefix.$fichier['bt_filename'])) { $prefix .= rand(0,9); }
		$fichier['bt_filename'] = $prefix.$fichier['bt_filename'];

		// par $_FILES
		if (isset($_FILES['fichier'])) {
			$new_file = $_FILES['fichier']['tmp_name'];
			// saving file
			if (move_uploaded_file($new_file, $dossier.'/'. $fichier['bt_filename']) ) {
				$fichier['bt_checksum'] = sha1_file($dossier.'/'. $fichier['bt_filename']);
			} else {
				$redir = basename($_SERVER['SCRIPT_NAME']).'?errmsg=error_fichier_ajout_2';
			}
		}
		// par $_POST d’une url
		if (isset($_POST['fichier'])) {
			// saving file locally
			$new_file = $_POST['fichier'];
			if (copy($new_file, $dossier.'/'. $fichier['bt_filename']) ) {
				$fichier['bt_filesize'] = filesize($dossier.'/'. $fichier['bt_filename']);
			} else {
				$redir = basename($_SERVER['SCRIPT_NAME']).'?errmsg=error_fichier_ajout_2_download';
			}
		}

		// if file is an image → create a thumbnail
		if ($fichier['bt_type'] == 'image') {
			create_thumbnail($dossier.'/'. $fichier['bt_filename']);
			list($fichier['bt_dim_w'], $fichier['bt_dim_h']) = getimagesize($dossier.'/'. $fichier['bt_filename']);
		}
		// else, if other type of file (pdf…) rm $path
		else {
			$fichier['bt_path'] = '';
		}

		// and add to DB
		$result = bdd_fichier($fichier, 'ajout-nouveau');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?file_id='.$fichier['bt_id'].'&msg=confirm_fichier_ajout';
	}

	// EDITING AN EXISTING FILE
	elseif ( isset($_POST['editer']) and !isset($_GET['suppr']) ) {
		$old_filename = $_POST['filename']; // Name can be edited too. This is old name, the new one is in $fichier[].
		$new_filename = $fichier['bt_filename'];

		// filename changed ? Move file
		if ($new_filename != $old_filename) {
			// avoid filename collisions
			$prefix = '';
			while (file_exists($dossier.'/'.$prefix.$new_filename)) {
				$prefix .= rand(0,9);
			}
			$new_filename = $prefix.$fichier['bt_filename'];
			$fichier['bt_filename'] = $new_filename; // update file name in $fichier array(), with the new prefix.

			// rename file on disk
			if (rename($dossier.'/'.$old_filename, $dossier.'/'.$new_filename)) {
				// if file is image, also rename the thumbnail (or create one if none).
				if ($fichier['bt_type'] == 'image') {
					if ( ($old_thb = chemin_thb_img_test($dossier.'/'.$old_filename)) != $dossier.'/'.$old_filename ) {
						rename($old_thb, chemin_thb_img($dossier.'/'.$new_filename));
					} else {
						create_thumbnail($dossier.'/'.$new_filename);
					}
				}
			// error rename ficher
			} else {
				$redir = basename($_SERVER['SCRIPT_NAME']).'?file_id='.$fichier['bt_id'].'&errmsg=error_fichier_rename';
			}
		}
		// reupdate filesize.
		list($fichier['bt_dim_w'], $fichier['bt_dim_h']) = getimagesize($dossier.'/'.$new_filename);

		$redir = basename($_SERVER['SCRIPT_NAME']).'?file_id='.$fichier['bt_id'].'&edit&msg=confirm_fichier_edit';
		$result = bdd_fichier($fichier, 'editer-existant');
	}

	// DELETING AN EXISTING FILE
	elseif ( (isset($_POST['supprimer']) and preg_match('#^\d{14}$#', $_POST['file_id'])) ) {
		// remove physical file on disk if it exists
		if (is_file($dossier.'/'.$fichier['bt_filename'])) {
			$liste_fichiers = rm_dots_dir(scandir($dossier)); // lists actual files in folder
			if (TRUE === unlink($dossier.'/'.$fichier['bt_filename'])) { // delete matching file
				if ($fichier['bt_type'] == 'image') @unlink(chemin_thb_img($dossier.'/'.$fichier['bt_filename'])); // also delete thumbnail if any
				$redir = basename($_SERVER['SCRIPT_NAME']).'?msg=confirm_fichier_suppr';

			} else { // error removing file from disk
				$redir = basename($_SERVER['SCRIPT_NAME']).'?errmsg=error_fichier_suppr&what=file_suppr_error_on_disk';
			}
		} else {
			$redir = basename($_SERVER['SCRIPT_NAME']).'?msg=error_fichier_suppr&what=not_file_on_disk';
		}
		// remove from DB anyway
		$result = bdd_fichier($fichier, 'supprimer-existant');

	}

	// if DB is ok
	if ($result === TRUE) {
		// if not AJAX request
		if (!isset($_POST['do'])) {
			redirection($redir);
		} else {
			return TRUE;
		}
	}
	else { die(var_dump($result)); }

}

function bdd_fichier($fichier, $form_action) {
	// adding a new file
	if ($form_action == 'ajout-nouveau') {
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO images
				(	bt_id,
					bt_type,
					bt_fileext,
					bt_filename,
					bt_filesize,
					bt_content,
					bt_folder,
					bt_checksum,
					bt_statut,
					bt_path,
					bt_dim_w,
					bt_dim_h
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				$fichier['bt_id'],
				$fichier['bt_type'],
				$fichier['bt_fileext'],
				$fichier['bt_filename'],
				$fichier['bt_filesize'],
				$fichier['bt_content'],
				$fichier['bt_folder'],
				$fichier['bt_checksum'],
				$fichier['bt_statut'],
				$fichier['bt_path'],
				$fichier['bt_dim_w'],
				$fichier['bt_dim_h']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur ajout article: '.$e->getMessage();
		}
	}

	// editing an existing file
	elseif ($form_action == 'editer-existant') {
		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE images SET
				bt_filename=?,
				bt_content=?,
				bt_folder=?,
				bt_statut=?
				WHERE bt_id=?');
			$req->execute(array(
				$fichier['bt_filename'],
				$fichier['bt_content'],
				$fichier['bt_folder'],
				$fichier['bt_statut'],
				$fichier['bt_id']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'DB error update file: '.$e->getMessage();
		}

	}

	// deleting a file (from DB ; from disk is done in traiter_form_fichier() )
	elseif ( $form_action == 'supprimer-existant' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM images WHERE bt_id=?');
			$req->execute(array($fichier['bt_id']));
			return TRUE;
		} catch (Exception $e) {
			return 'DB error suppr file: '.$e->getMessage();
		}
	}
}




// POST FILE
/*
 * On post of a file (always on admin sides)
 * gets posted informations and turn them into
 * an array
 *
 */
function init_post_fichier() { //no $mode : it's always admin.
	// on edit : get file info from form
	if (isset($_POST['is_it_edit']) and $_POST['is_it_edit'] == 'yes') {
		$file_id = htmlspecialchars($_POST['file_id']);
			$filename = pathinfo(htmlspecialchars($_POST['filename']), PATHINFO_FILENAME);
			$ext = strtolower(pathinfo(htmlspecialchars($_POST['filename']), PATHINFO_EXTENSION));
			$checksum = htmlspecialchars($_POST['sha1_file']);
			$size = htmlspecialchars($_POST['filesize']);
			$dossier = htmlspecialchars($_POST['dossier']);
			$path = htmlspecialchars($_POST['path']);
			$type = detection_type_fichier($ext);
	// on new post, get info from the file itself
	} else {
		$file_id = date('YmdHis');
		// ajout de fichier par upload
		if (!empty($_FILES['fichier']) and ($_FILES['fichier']['error'] == 0)) {
			$filename = pathinfo($_FILES['fichier']['name'], PATHINFO_FILENAME);
			$ext = strtolower(pathinfo($_FILES['fichier']['name'], PATHINFO_EXTENSION));
			$checksum = sha1_file($_FILES['fichier']['tmp_name']);
			$size = $_FILES['fichier']['size'];
			$path = substr($checksum, 0, 2);
			$dossier = htmlspecialchars($_POST['dossier']);
			$type = detection_type_fichier($ext);
		// ajout par une URL d’un fichier distant
		} elseif ( !empty($_POST['fichier']) ) {
			$filename = pathinfo(parse_url($_POST['fichier'], PHP_URL_PATH), PATHINFO_FILENAME);
			$ext = strtolower(pathinfo(parse_url($_POST['fichier'], PHP_URL_PATH), PATHINFO_EXTENSION));
			$checksum = sha1_file($_POST['fichier']); // works with URL files
			$size = '';// same (even if we could use "filesize" with the URL, it would over-use data-transfer)
			$path = substr($checksum, 0, 2);
			$dossier = htmlspecialchars($_POST['dossier']);
			$type = detection_type_fichier($ext);
		} else {
			// ERROR
			redirection(basename($_SERVER['SCRIPT_NAME']).'?errmsg=error_image_add');
			return FALSE;
		}
	}
	// nom du fichier : si nom donné, sinon nom du fichier inchangé
	$filename = diacritique(htmlspecialchars((!empty($_POST['nom_entree'])) ? $_POST['nom_entree'] : $filename)).'.'.$ext;
	$statut = (isset($_POST['statut']) and $_POST['statut'] == 'on') ? '0' : '1';
	$fichier = array (
		'bt_id' => $file_id,
		'bt_type' => $type,
		'bt_fileext' => $ext,
		'bt_filesize' => $size,
		'bt_filename' => $filename, // le nom du final du fichier peut changer à la fin, si le nom est déjà pris par exemple
		'bt_content' => clean_txt($_POST['description']),
		'bt_checksum' => $checksum,
		'bt_statut' => $statut,
		'bt_folder' => $dossier, // tags
		'bt_path' => $path, // path on disk (rand subdir to avoid too many files in same dir)
		'bt_dim_w' => '',
		'bt_dim_h' => '',
	);
	return $fichier;
}





