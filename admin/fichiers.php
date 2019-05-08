<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

// Returns the JSON text with filesinfo, for the browser to make the HTML rendering
function send_files_json($files) {
	// Images
	$dossier = '../'.trim(str_replace(dirname(DIR_IMAGES), '', DIR_IMAGES), '/');
	$json_img = '['."\n";
	foreach ($files['images'] as $i => $im) {
		$json_img .= "\t".'{'.
			'"id":'.json_encode($im['bt_id']).', '.
			'"folder":'.json_encode($im['bt_folder']).', '.
			'"absPath":'.json_encode($dossier.'/'.$im['bt_path'].'/').', '.
			'"fileName":'.json_encode($im['bt_filename']).', '.
			'"thbPath":'.json_encode(chemin_thb_img_test($dossier.'/'.$im['bt_path'].'/'.$im['bt_filename'])).', '.
			'"action":'.'""'.', '.
			'"w":'.json_encode($im['bt_dim_w']).', '.
			'"h":'.json_encode($im['bt_dim_h']).
		'},'."\n";
	}
	$json_img = trim(trim($json_img), ',')."\n".']';

	// Documents (non images)
	$dossier = '../'.trim(str_replace(dirname(DIR_DOCUMENTS), '', DIR_DOCUMENTS), '/');
	$json_files = '['."\n";
	foreach ($files['documents'] as $i => $doc) {
		$json_files .= "\t".'{'.
			'"id":'.json_encode($doc['bt_id']).', '.
			'"fileSize":'.json_encode(taille_formate($doc['bt_filesize'])).', '.
			'"fileType":'.json_encode($doc['bt_type']).', '.
			'"absPath":'.json_encode($dossier.'/'.$doc['bt_path']).', '.
			'"action":'.'""'.', '.
			'"fileName":'.json_encode($doc['bt_filename']).
		'},'."\n";
	}
	$json_files = trim(trim($json_files), ',')."\n".']'."\n";

	$out  = '<script id="json_images" type="application/json">'."\n";
	$out .= $json_img."\n";
	$out .= '</script>'."\n";
	$out .= '<script id="json_docs" type="application/json">'."\n";
	$out .= $json_files."\n";
	$out .= '</script>'."\n";

	return $out;
}


function afficher_form_fichier($erreurs, $fichiers, $what) { // ajout d’un fichier
	$max_file_size = taille_formate( min(return_bytes(ini_get('upload_max_filesize')), return_bytes(ini_get('post_max_size'))) );
	$max_file_nb = ini_get('max_file_uploads');
	if ($erreurs) {
		echo erreurs($erreurs);
	}
	$form = '<form id="form-image" enctype="multipart/form-data" method="post" action="'.basename($_SERVER['SCRIPT_NAME']).'" onsubmit="submitdnd(event);">'."\n";

	if (empty($fichiers)) { // si PAS fichier donnée : formulaire nouvel envoi.
		$form .= '<div id="form-dragndrop">'."\n";
			$form .= '<div id="dragndrop-area" ondragover="event.preventDefault();" ondrop="handleDrop(event);" >'."\n";
			$form .= "\t".'<div id="dragndrop-title">'."\n";
			$form .= "\t\t".$GLOBALS['lang']['img_drop_files_here']."\n";
			$form .= "\t\t".'<div class="upload-info">('.$GLOBALS['lang']['label_jusqua'].$max_file_size.$GLOBALS['lang']['label_parfichier'].')</div>'."\n";
			$form .= "\t".'</div>'."\n";
			$form .= "\t".'<p>'.$GLOBALS['lang']['ou'].'</p>';
			$form .= "\t".'<div id="file-input-wrapper"><input name="fichier" id="fichier" class="text" type="file" required="" /><label for="fichier"></label></div>'."\n";
			$form .= "\t".'<button type="button" class="specify-link button-cancel submit" id="click-change-form" onclick="return switchUploadForm();" data-lang-url="'.$GLOBALS['lang']['img_specifier_url'].'" data-lang-file="'.$GLOBALS['lang']['img_upload_un_fichier'].'">'.$GLOBALS['lang']['img_specifier_url'].'</button>'."\n";
			$form .= '</div>'."\n";
			$form .= '<div id="count"></div>'."\n";
			$form .= '<div id="result"></div>'."\n";
		$form .= '</div>'."\n";

		$form .= '<div id="img-others-infos">'."\n";

		$form .= "\t".'<p><input type="text" id="nom_entree" name="nom_entree" placeholder="'.$GLOBALS['lang']['placeholder_nom_fichier'].'" value="" size="60" class="text" /><label for="nom_entree">'.$GLOBALS['lang']['label_dp_nom'].'</label></p>'."\n";
		$form .= "\t".'<p><textarea class="text" id="description" name="description" cols="60" rows="5" placeholder="'.$GLOBALS['lang']['placeholder_description'].'" ></textarea><label for="description">'.$GLOBALS['lang']['label_dp_description'].'</label></p>'."\n";
		$form .= "\t".'<p><input type="text" id="dossier" name="dossier" placeholder="'.$GLOBALS['lang']['placeholder_folder'].'" value="" size="60" class="text" /><label for="dossier">'.$GLOBALS['lang']['label_dp_dossier'].'</label></p>'."\n";
		$form .= "\t".'<p><input type="checkbox" id="statut" name="statut" class="checkbox" /><label for="statut">'.$GLOBALS['lang']['label_file_priv'].'</label></p>';
		$form .= hidden_input('token', new_token(), 'id');
		$form .= hidden_input('_verif_envoi', '1');

		$form .= "\t".'<p class="submit-bttns"><button class="submit button-submit" type="submit" name="upload">'.$GLOBALS['lang']['img_upload'].'</button></p>'."\n";
		$form .= '</div>'."\n";

	}
	// si ID dans l’URL, il s’agit également du seul fichier dans le tableau fichiers, d’où le [0]
	elseif (!empty($fichiers) and isset($_GET['file_id']) and preg_match('/\d{14}/',($_GET['file_id']))) {
		$myfile = $fichiers[0];
		$myfilepath = (empty($myfile['bt_path'])) ? '' : '/'.$myfile['bt_path'];
		# /home/timo/www/blogotext/img/
		$DIR = ($myfile['bt_type'] == 'image') ? DIR_IMAGES : DIR_DOCUMENTS;
		# img
		$filesPath = trim(str_replace(BT_ROOT, '', $DIR), '/');
		# http://example.com/blogotext/img/ab/file.jpg
		$absolute_URI = URL_ROOT.$filesPath.$myfilepath.'/'.$myfile['bt_filename'];
		# img/ab/photofile.jpg
		$relativeFilePath = $filesPath.$myfilepath.'/'.$myfile['bt_filename'];

		$form .= '<div class="edit-fichier">'."\n";

		// codes d’intégrations pour les médias
		// Video
		if ($myfile['bt_type'] == 'video')
		$form .= '<div class="display-media"><video class="media" src="../'.$relativeFilePath.'" type="video/'.$myfile['bt_fileext'].'" load controls="controls"></video></div>'."\n";
		// image
		if ($myfile['bt_type'] == 'image')
		$form .= '<div class="display-media"><a href="../'.$relativeFilePath.'"><img class="media" src="../'.$relativeFilePath.'" alt="'.$myfile['bt_filename'].'" width="'.$myfile['bt_dim_w'].'" height="'.$myfile['bt_dim_h'].'" /></a></div>'."\n";
		// audio
		if ($myfile['bt_type'] == 'music')
		$form .= '<div class="display-media"><audio class="media" src="../'.$relativeFilePath.'" type="audio/'.$myfile['bt_fileext'].'" load controls="controls"></audio></div>'."\n";
		
		// la partie listant les infos du fichier.
		$form .= '<ul id="fichier-meta-info">'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_nom'].'</b> '.$myfile['bt_filename'].'</li>'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_type'].'</b> '.$myfile['bt_type'].' (.'.$myfile['bt_fileext'].')</li>'."\n";
			if ($myfile['bt_type'] == 'image') // si le fichier est une image, on ajout ses dimensions en pixels
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_dimensions'].'</b> '.$myfile['bt_dim_w'].'px × '.$myfile['bt_dim_h'].'px'.'</li>'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_date'].'</b>'.date_formate($myfile['bt_id']).', '.heure_formate($myfile['bt_id']).'</li>'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_poids'].'</b>'.taille_formate($myfile['bt_filesize']).'</li>'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_checksum'].'</b>'.$myfile['bt_checksum'].'</li>'."\n";
			$form .= "\t".'<li><b>'.$GLOBALS['lang']['label_dp_visibilite'].'</b>'.(($myfile['bt_statut'] == 1) ? 'Publique' : 'Privée').'</li>'."\n";
		$form .= '</ul>'."\n";

		// Integration codes.
		$form .= '<div id="interg-codes">'."\n";
		$form .= '<p><strong>'.$GLOBALS['lang']['label_codes'].'</strong></p>'."\n";
		$form .= '<input onfocus="this.select()" class="text" type="text" value=\''.$absolute_URI.'\' />'."\n";
		$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<a href="'.$absolute_URI.'">'.$myfile['bt_filename'].'</a>\' />'."\n";
		// for images
		if ($myfile['bt_type'] == 'image') {
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<img src="'.$absolute_URI.'" alt="i" width="'.$myfile['bt_dim_w'].'" height="'.$myfile['bt_dim_h'].'" />\' />'."\n";
			//$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<img src="/'.$relativeFilePath.'" alt="i" width="'.$myfile['bt_dim_w'].'" height="'.$myfile['bt_dim_h'].'" />\' />'."\n";
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<img src="'.$relativeFilePath.'" alt="i" width="'.$myfile['bt_dim_w'].'" height="'.$myfile['bt_dim_h'].'" />\' />'."\n";
			//$form .= '<input onfocus="this.select()" class="text" type="text" value=\'[img]'.$absolute_URI.'[/img]\' />'."\n";
			//$form .= '<input onfocus="this.select()" class="text" type="text" value=\'[spoiler][img]'.$absolute_URI.'[/img][/spoiler]\' />'."\n";

			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<img src="'.$relativeFilePath.'" alt="i" srcset="'.$relativeFilePath.' '.$myfile['bt_dim_w'].'w, '.substr(chemin_thb_img_test('../'.$relativeFilePath), 3).' 600w" sizes="50vw" class="" />\' />'."\n";
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<figure><img src="'.$relativeFilePath.'" alt="i" width="'.$myfile['bt_dim_w'].'" height="'.$myfile['bt_dim_h'].'" /><figcaption></figcaption></figure>\' />'."\n";

		// video
		} elseif ($myfile['bt_type'] == 'video') {
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<video src="'.$absolute_URI.'" type="video/'.$myfile['bt_fileext'].'" load="" controls="controls"></video>\' />'."\n";
		// audio
		} elseif ($myfile['bt_type'] == 'music') {
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'<audio src="'.$absolute_URI.'" type="audio/'.$myfile['bt_fileext'].'" load="" controls="controls"></audio>\' />'."\n";
		} else {
			$form .= '<input onfocus="this.select()" class="text" type="text" value=\'[url]'.$absolute_URI.'[/url]\' />'."\n";
		}

		$form .= '</div>'."\n";

		// la partie avec l’édition du contenu.
		$form .= '<div id="img-others-infos">'."\n";
		$form .= "\t".'<p><input type="text" id="nom_entree" name="nom_entree" placeholder="" value="'.pathinfo($myfile['bt_filename'], PATHINFO_FILENAME).'" size="60" class="text" /><label for="nom_entree">'.ucfirst($GLOBALS['lang']['label_dp_nom']).'</label></p>'."\n";
		$form .= "\t".'<p><textarea class="text" name="description" id="description" cols="60" rows="5" placeholder="'.$GLOBALS['lang']['placeholder_description'].'" >'.$myfile['bt_content'].'</textarea><label for="description">'.$GLOBALS['lang']['label_dp_description'].'</label></p>'."\n";
		$form .= "\t".'<p><input type="text" name="dossier" placeholder="'.$GLOBALS['lang']['placeholder_folder'].'" value="'.$myfile['bt_folder'].'" size="60" class="text" /><label for="dossier">'.$GLOBALS['lang']['label_dp_dossier'].'</label></p>'."\n";
		$checked = ($myfile['bt_statut'] == 0) ? 'checked ' : '';
		$form .= "\t".'<p><input type="checkbox" id="statut" name="statut" '.$checked.' class="checkbox" /><label for="statut">'.$GLOBALS['lang']['label_file_priv'].'</label></p>';
		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t".'<button class="submit button-delete" type="button" name="supprimer" onclick="rmFichier(this)">'.$GLOBALS['lang']['supprimer'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'fichiers.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-submit" type="submit" name="editer">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= '</div>'."\n";

		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('is_it_edit', 'yes');
		$form .= hidden_input('file_id', $myfile['bt_id']);
		$form .= hidden_input('filename', $myfile['bt_filename']);
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('path', $myfile['bt_path']);
		$form .= '</div>';
	}
	$form .= '</form>'."\n";

	echo $form;
}

// recherche / tri
if ( !empty($_GET['filtre']) ) {
	if ( preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) {
		$query = "SELECT * FROM images WHERE bt_id LIKE ? ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array($_GET['filtre'].'%'));
	}
	elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') {
		$query = "SELECT * FROM images WHERE bt_statut=? ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)));
	}
	elseif (strpos($_GET['filtre'], 'type.') === 0) {
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		$query = "SELECT * FROM images WHERE bt_type LIKE ? ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array($search));
	}
	else {
		$query = "SELECT * FROM images WHERE bt_type=? ORDER BY bt_id DESC LIMIT 25";
		$tableau = liste_elements($query, array('image'));
	}
// recheche par mot clé
} elseif (!empty($_GET['q'])) {
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_filename ) LIKE ? '), 'AND ');
	$query = "SELECT * FROM images WHERE ".$sql_where."ORDER BY bt_id DESC";
	$tableau = liste_elements($query, $arr);

// par extension
} elseif (!empty($_GET['extension'])) {
	$query = "SELECT * FROM images WHERE bt_fileext=? ORDER BY bt_id DESC";
	$tableau = liste_elements($query, array($_GET['extension']));
// par fichier unique (id)
} elseif (isset($_GET['file_id']) and preg_match('/\d{14}/',($_GET['file_id']))) {
	$query = "SELECT * FROM images WHERE bt_id=? LIMIT 1";
	$tableau = liste_elements($query, array($_GET['file_id']));
}
else {
	$query = "SELECT * FROM images WHERE bt_type=? ORDER BY bt_id DESC LIMIT 25";
	$tableau = liste_elements($query, array('image'));
}


// traitement d’une action sur le fichier
$erreurs = array();
if (isset($_POST['_verif_envoi'])) {
	$fichier = init_post_fichier();
	$erreurs = valider_form_fichier($fichier);
	if (empty($erreurs)) {
		traiter_form_fichier($fichier);
	}
}


// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['titre_fichier'], "files");
afficher_topnav($GLOBALS['lang']['titre_fichier'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
afficher_form_filtre('images', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
echo '<div class="nombre-elem">'."\n";
$count_element = count($tableau);
$count_element_total = liste_elements_count("SELECT count(ID) AS nbr FROM images WHERE bt_type=? ", array('image'));
$diffcount = $count_element_total - $count_element;
echo ucfirst(nombre_objets($count_element, 'fichier')).' '.$GLOBALS['lang']['sur'].' '.$count_element_total;
echo '</div>'."\n";

echo '</div>'."\n";

echo '<div id="page">'."\n";

// édition d’un fichier ?
if ( isset($_GET['file_id']) ) {
	afficher_form_fichier($erreurs, $tableau, 'fichier');
}
// affichage de la liste des fichiers.
else {
	if (empty($_GET['filtre']) && empty($_GET['q']) && empty($_GET['extension']) ) {
		afficher_form_fichier($erreurs, '', 'fichier');
	}

	// séparation des images des autres types de fichiers
	$sorted_files = array('images' => array(), 'documents' => array());
	$folders_files = array('images' => '', 'documents' => '');
	foreach ($tableau as $file) {
		if ($file['bt_type'] == 'image') {
			$sorted_files['images'][] = $file;
			$folders_files['images'] .= (!empty($file['bt_folder'])) ? $file['bt_folder'].',' : '';
		} else {
			$sorted_files['documents'][] = $file;
			$folders_files['documents'] .= (!empty($file['bt_type'])) ? $file['bt_type'].',' : '';
		}
	}

	// Send JSON data to browser
	$out_html = send_files_json($sorted_files);

	// img-wall
	if (!empty($sorted_files['images'])) {
		$out_html .= '<div id="image-section">'."\n";
		$out_html .= "\t".'<div class="list-buttons" id="list-albums">'."\n";
		$out_html .= "\t\t".'<button class="current" data-folder="" data-count="0">'.$GLOBALS['lang']['label_images'].'</button>'."\n";
		$out_html .= "\t\t".'<button id="load_all" class="submit button-cancel" data-diff="'.$diffcount.'">Charger les '.$diffcount.' autres images</button>'."\n";
		$out_html .= "\t".'</div>'."\n";
		$out_html .= "\t".'<div id="image-wall">'."\n";
		$out_html .= "\t\t".'<div id="" class="image_bloc" data-folder="" hidden>'."\n";
		$out_html .= "\t\t\t".'<img src="" alt="#" width="" height="" />'."\n";
		$out_html .= "\t\t\t".'<span>'."\n";
		$out_html .= "\t\t\t\t".'<a class="vignetteAction imgShow" href=""></a>'."\n";
		$out_html .= "\t\t\t\t".'<a class="vignetteAction imgEdit" href=""></a>'."\n";
		$out_html .= "\t\t\t\t".'<a class="vignetteAction imgDL" href="" download=""></a>'."\n";
		$out_html .= "\t\t\t\t".'<button class="vignetteAction imgSuppr" data-id=""></button>'."\n";
		$out_html .= "\t\t\t".'</span>'."\n";
		$out_html .= "\t\t".'</div>'."\n";

		$out_html .= "\t".'</div>'."\n";
		$out_html .= '</div>'."\n";
	}

	// documents/files table
	if (!empty($sorted_files['documents'])) {
		$out_html .= '<div id="files-section">'."\n";
		$out_html .= "\t".'<div class="list-buttons" id="list-types">'."\n";
		$out_html .= "\t\t".'<button data-type="" class="current">'.count($sorted_files['documents']).' '.$GLOBALS['lang']['label_fichiers'].'</button>'."\n";
		// create folder list
		$tab_types = explode(',', str_replace(array(', ', ' ,'), ',', trim($folders_files['documents'], ',')));
		$tab_types = array_count_values($tab_types);
		foreach ($tab_types as $type => $nb) {
			$out_html .= "\t\t".'<button data-type="'.$type.'">'.$type.' ('.$nb.')</button>'."\n";
		}
		$out_html .= "\t".'</div>'."\n";
		$out_html .= "\t".'<table id="file-list">'."\n";
		$out_html .= "\t\t".'<thead>'."\n";
		$out_html .= "\t\t\t".'<tr><th></th><th>'.$GLOBALS['lang']['label_dp_nom'].'</th><th>'.$GLOBALS['lang']['label_dp_poids'].'</th><th>'.$GLOBALS['lang']['label_dp_date'].'</th><th></th><th></th></tr>'."\n";
		$out_html .= "\t\t".'</thead>'."\n";
		$out_html .= "\t\t".'<tbody>'."\n";
		$out_html .= "\t\t\t".'<tr id="" data-type="" hidden>'."\n";
		$out_html .= "\t\t\t\t".'<td><img id="" alt="" src=""></td>'."\n";
		$out_html .= "\t\t\t\t".'<td><a href=""></a></td>'."\n";
		$out_html .= "\t\t\t\t".'<td></td>'."\n";
		$out_html .= "\t\t\t\t".'<td></td>'."\n";
		$out_html .= "\t\t\t\t".'<td><a href="" download="">DL</a></td>'."\n";
		$out_html .= "\t\t\t\t".'<td><a href="#" data-id="">DEL</a></td>'."\n";
		$out_html .= "\t\t\t".'</tr>'."\n";
		$out_html .= "\t\t".'</tbody>'."\n";
		$out_html .= "\t".'</table>'."\n";
		$out_html .= '</div>'."\n";

	}
	echo $out_html;
}

echo php_lang_to_js();
echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
echo "\n".'<script>'."\n";
echo 'var counter = 0;'."\n";
echo 'var nbDraged = false;'."\n";
echo 'var diffCount = '.($diffcount).';'."\n";
echo 'var nbDone = 0;'."\n";
echo 'var list = []; // list of uploaded files'."\n";

echo 'if (null != document.getElementById(\'dragndrop-area\')) {'."\n";
echo "\t".'document.getElementById(\'dragndrop-area\').addEventListener(\'dragenter\', handleDragEnter, false);'."\n";
echo "\t".'document.getElementById(\'dragndrop-area\').addEventListener(\'dragover\', handleDragOver, true);'."\n";
echo "\t".'document.getElementById(\'dragndrop-area\').addEventListener(\'dragleave\', handleDragLeave, false);'."\n";
echo "\t".'document.getElementById(\'dragndrop-area\').addEventListener(\'dragend\', handleDragEnd, false);'."\n";
echo '}'."\n";

echo 'var imageWall = new imgListWall();'."\n";
echo 'var filesWall = new docListWall();'."\n";
echo "\n".'</script>'."\n";


footer($begin);
