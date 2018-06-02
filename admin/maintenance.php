<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();
$GLOBALS['liste_flux'] = open_serialzd_file(FEEDS_DB);


/*
 * reconstruit la BDD des fichiers (en synchronisant la liste des fichiers sur le disque avec les fichiers dans BDD)
*/
function rebuilt_file_db() {

	/*
	*
	* BEGIN WITH LISTING FILES ACTUALLY ON DISC
	*/

	$img_on_disk = rm_dots_dir(scandir(DIR_IMAGES));
	// scans also subdir of img/* (in one single array of paths)
	foreach ($img_on_disk as $i => $e) {
		$subelem = DIR_IMAGES.$e;
		if (is_dir($subelem)) {
			unset($img_on_disk[$i]); // rm folder entry itself
			$subidir = rm_dots_dir(scandir($subelem));
			foreach ($subidir as $j => $im) {
				$img_on_disk[] = $e.'/'.$im;
			}
		}
	}
	foreach ($img_on_disk as $i => $e) {
		$img_on_disk[$i] = '/'.$e;
	}

	$docs_on_disc = rm_dots_dir(scandir(DIR_DOCUMENTS));

	// don’t cound thumbnails
	$img_on_disk = array_filter($img_on_disk, function($file){return (!((preg_match('#-thb\.jpg$#', $file)) or (strpos($file, 'index.html') == 4))); });

	/*
	*
	* THEN REMOVES FROM DATABASE THE IMAGES THAT ARE MISSING ON DISK
	*/

	$query = "SELECT bt_filename, bt_path, ID FROM images";
	$img_in_db = liste_elements($query, array(), 'images');

	$img_in_db_path = array(); foreach ($img_in_db as $i => $img) { $img_in_db_path[] = '/'.$img['bt_path'].'/'.$img['bt_filename']; }

	$img_to_rm_from_db = array();

	foreach ($img_in_db as $i => $img) {
		if (!in_array('/'.$img['bt_path'].'/'.$img['bt_filename'], $img_on_disk)) {
			$img_to_rm_from_db[] = $img['ID'];
		}
	}

	if (!empty($img_to_rm_from_db)) {
		try {
			$GLOBALS['db_handle']->beginTransaction();
			foreach($img_to_rm_from_db as $img) {
				$query = 'DELETE FROM images WHERE ID = ? ';
				$req = $GLOBALS['db_handle']->prepare($query);
				$req->execute(array($img));
			}
			$GLOBALS['db_handle']->commit();
		} catch (Exception $e) {
			$req->rollBack();
			die('Erreur 5798 on delete unexistant images : '.$e->getMessage());
		}
	}

	/*
	*
	* ADD THE IMAGSE THAT ARE ON DISK BUT NOT IN DATABASE, TO DATABASE
	*/

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach ($img_on_disk as $file) {
			if (!in_array($file, $img_in_db_path)) {
				$filepath = DIR_IMAGES.$file;
				$time = filemtime($filepath);
				$id = date('YmdHis', $time);
				$checksum = sha1_file($filepath);
				$filesize = filesize($filepath);
				list($img_w, $img_h) = getimagesize($filepath);

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
					$id,
					'image',
					pathinfo($filepath, PATHINFO_EXTENSION),
					$file,
					$filesize,
					'',
					'',
					$checksum,
					0,
					substr($checksum, 0, 2),
					$img_w,
					$img_h
				));
				
				// crée une miniature de l’image
				create_thumbnail($filepath);
			}
		}

	} catch (Exception $e) {
		$req->rollBack();
		return 'Erreur 39787 ajout images du disque dans DB: '.$e->getMessage();
	}

/* TODO
	// fait pareil pour les files/ *
	foreach ($docs_on_disc as $file) {
		if (!in_array($file, $files_db)) {
			$filepath = DIR_DOCUMENTS.$file;
			$time = filemtime($filepath);
			$id = date('YmdHis', $time);
			// vérifie que l’ID ne se trouve pas déjà dans le tableau. Sinon, modifie la date (en allant dans le passé)
			while (array_key_exists($id, $files_db_id)) { $time--; $id = date('YmdHis', $time); } $files_db_id[] = $id;
			$ext = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
			$new_file = array(
				'bt_id' => $id,
				'bt_type' => detection_type_fichier($ext),
				'bt_fileext' => $ext,
				'bt_filesize' => filesize($filepath),
				'bt_filename' => $file,
				'bt_content' => '',
				'bt_wiki_content' => '',
				'bt_dossier' => 'default',
				'bt_checksum' => sha1_file($filepath),
				'bt_statut' => 0,
				'bt_path' => '',
			);
			// l’ajoute au tableau
			$GLOBALS['liste_fichiers'][] = $new_file;
		}
	}
	// tri le tableau fusionné selon les bt_id (selon une des clés d'un sous tableau).
	$GLOBALS['liste_fichiers'] = tri_selon_sous_cle($GLOBALS['liste_fichiers'], 'bt_id');
	// finalement enregistre la liste des fichiers.
	file_put_contents(FILES_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_fichiers']))).' * /');
	*/
}

/*
 * génère le fichier HTML au format de favoris utilisés par tous les navigateurs.
*/
function creer_fich_html() {
	// récupère les liens
	$query = "SELECT * FROM links ORDER BY bt_id DESC";
	$list = liste_elements($query, array(), 'links');

	// génération du code HTML.
	$html = '<!DOCTYPE NETSCAPE-Bookmark-file-1><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">'."\n";
	$html .= '<TITLE>Blogotext links export '.date('Y-M-D').'</TITLE><H1>Blogotext links export</H1>'."\n";
	foreach ($list as $n => $link) {
		$dec = decode_id($link['bt_id']);
		$timestamp = mktime($dec['heure'], $dec['minutes'], $dec['secondes'], $dec['mois'], $dec['jour'], $dec['annee']); // HISMDY : wtf!
		$html .= '<DT><A HREF="'.$link['bt_link'].'" ADD_DATE="'.$timestamp.'" PRIVATE="'.abs(1-$link['bt_statut']).'" TAGS="'.$link['bt_tags'].'">'.$link['bt_title'].'</A>'."\n";
		$html .= '<DD>'.strip_tags($link['bt_wiki_content'])."\n";
	}

	// écriture du fichier
	$file = 'backup-links-'.date('Ymd-His').'.html';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;
	return (file_put_contents(DIR_BACKUP.$file, $html) === FALSE) ? FALSE : $filepath; // écriture du fichier
}


/*
 * liste une table (ex: les commentaires) et comparre avec un tableau de commentaires trouvées dans l’archive
 * Retourne deux tableau : un avec les éléments présents dans la base, et un avec les éléments absents de la base
 */
function diff_trouve_base($table, $tableau_trouve) {
	$tableau_base = $tableau_absents = array();
	try {
		$req = $GLOBALS['db_handle']->prepare('SELECT bt_id FROM '.$table);
		$req->execute();
		while ($ligne = $req->fetch()) {
			$tableau_base[] = $ligne['bt_id'];
		}
	} catch (Exception $e) {
		die('Erreur 20959 : diff_trouve_base avec les "'.$table.'" : '.$e->getMessage());
	}

	// remplit les deux tableaux, pour chaque élément trouvé dans l’archive, en fonction de ceux déjà dans la base
	foreach ($tableau_trouve as $key => $element) {
		if (!in_array($element['bt_id'], $tableau_base)) $tableau_absents[] = $element;
	}
	return $tableau_absents;
}

/* RECOMPTE LES COMMENTAIRES AUX ARTICLES */
function recompte_commentaires() {
	try {
		if (DBMS == 'sqlite') {
			$query = "UPDATE articles SET bt_nb_comments = COALESCE((SELECT count(a.bt_id) FROM articles a INNER JOIN commentaires c ON (c.bt_article_id = a.bt_id) WHERE articles.bt_id = a.bt_id AND c.bt_statut=1 GROUP BY a.bt_id), 0)";
		} elseif (DBMS == 'mysql') {
			$query = "UPDATE articles SET bt_nb_comments = COALESCE((SELECT count(articles.bt_id) FROM commentaires WHERE commentaires.bt_article_id = articles.bt_id), 0)";
		}
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute();
	} catch (Exception $e) {
		$req->rollBack();
		die('Erreur 8942 on recount-commentaires : '.$e->getMessage());
	}
}

/* IMPORTER UN FICHIER json AU FORMAT DE BLOGOTEXT */
function insert_bak_table($json) {
	$data = json_decode($json, true);
	$return = array();

	try {
		$GLOBALS['db_handle']->beginTransaction();


		foreach ($data as $type => $array_type) {
			// get only items that are not yet in DB (base on bt_identification)
			$data[$type] = diff_trouve_base($type, $array_type);
			$return[$type] = count($data[$type]);
			if ($return[$type]) {

				switch ($type) {
					case 'articles':
						foreach($data[$type] as $art) {
							$query = 'INSERT INTO articles ( bt_type, bt_id, bt_date, bt_title, bt_abstract, bt_notes, bt_link, bt_content, bt_wiki_content, bt_tags, bt_keywords, bt_nb_comments, bt_allow_comments, bt_statut ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )';
							$array = array( $art['bt_type'], $art['bt_id'], $art['bt_date'], $art['bt_title'], $art['bt_abstract'], $art['bt_notes'], $art['bt_link'], $art['bt_content'], $art['bt_wiki_content'], $art['bt_tags'], $art['bt_keywords'], $art['bt_nb_comments'], $art['bt_allow_comments'], $art['bt_statut'] );
							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);
						}
						break;

					case 'commentaires':
						foreach($data[$type] as $com) {
							$query = 'INSERT INTO commentaires (bt_type, bt_id, bt_article_id, bt_content, bt_wiki_content, bt_author, bt_link, bt_webpage, bt_email, bt_subscribe, bt_statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
							$array = array($com['bt_type'], $com['bt_id'], $com['bt_article_id'], $com['bt_content'], $com['bt_wiki_content'], $com['bt_author'], $com['bt_link'], $com['bt_webpage'], $com['bt_email'], $com['bt_subscribe'], $com['bt_statut']);
							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);

							recompte_commentaires();
						}
						break;

					case 'links':
						foreach($data[$type] as $link) {
							$query = 'INSERT INTO links (bt_type, bt_id, bt_link, bt_content, bt_wiki_content, bt_statut, bt_title, bt_tags ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? ) ';
							$array = array($link['bt_type'], $link['bt_id'], $link['bt_link'], $link['bt_content'], $link['bt_wiki_content'], $link['bt_statut'], $link['bt_title'], $link['bt_tags']);
							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);
						}
						break;
					
					case 'notes':
						foreach($data[$type] as $note) {
							$query = 'INSERT INTO notes ( bt_id, bt_title, bt_content, bt_color ) VALUES (?, ?, ?, ?)';
							$array = array($note['bt_id'], $note['bt_title'], $note['bt_content'], $note['bt_color']);
							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);
						}
						break;

					case 'agenda':
						foreach($data[$type] as $event) {
							$query = 'INSERT INTO agenda ( bt_id, bt_date, bt_event_loc, bt_title, bt_content ) VALUES (?, ?, ?, ?, ?)';
							$array = array( $event['bt_id'], $event['bt_date'], $event['bt_event_loc'], $event['bt_title'], $event['bt_content']);

							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);
						}
						break;
				} // end switch
			} // end if
		} // end forearch

		$GLOBALS['db_handle']->commit();

	} catch (Exception $e) {
		$req->rollBack();
		die('Erreur 7241 on import JSON : '.$e->getMessage());
	}

	return $return;
}


/* AJOUTE TOUS LES DOSSIERS DU TABLEAU $dossiers DANS UNE ARCHIVE ZIP */
function addFolder2zip($zip, $folder) {
	if ($handle = opendir($folder)) {
		while (FALSE !== ($entry = readdir($handle))) {
			if ($entry != "." and $entry != ".." and is_readable($folder.'/'.$entry)) {
				if (is_dir($folder.'/'.$entry)) addFolder2zip($zip, $folder.'/'.$entry);
				else $zip->addFile($folder.'/'.$entry, preg_replace('#^\.\./#', '', $folder.'/'.$entry));
		}	}
		closedir($handle);
	}
}

function creer_fichier_zip($dossiers) {
	foreach($dossiers as $i => $dossier) {
		$dossiers[$i] = '../'.str_replace(BT_ROOT, '', $dossier); // FIXME : find cleaner way for '../';
	}
	$file = 'archive_site-'.date('Ymd').'-'.substr(md5(rand(10,99)),3,5).'.zip';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;

	$zip = new ZipArchive;
	if ($zip->open(DIR_BACKUP.$file, ZipArchive::CREATE) === TRUE) {
		foreach ($dossiers as $dossier) {
			addFolder2zip($zip, $dossier);
		}
		$zip->close();
		if (is_file(DIR_BACKUP.$file)) return $filepath;
	}
	else return FALSE;
}

/* FABRIQUE LE FICHIER JSON (très simple en fait) */
function creer_fichier_json($data_array) {
	$file = 'backup-data-'.date('Ymd-His').'.json';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;
	return (file_put_contents(DIR_BACKUP.$file, json_encode($data_array)) === FALSE) ? FALSE : $filepath;
}

/* Crée la liste des RSS et met tout ça dans un fichier OPML */
function creer_fichier_opml() {
	// sort feeds by folder
	$folders = array();
	foreach ($GLOBALS['liste_flux'] as $i => $feed) {
		$folders[$feed['folder']][] = $feed;
	}
	ksort($folders);

	$html  = '<?xml version="1.0" encoding="utf-8"?>'."\n";
	$html .= '<opml version="1.0">'."\n";
	$html .= "\t".'<head>'."\n";
	$html .= "\t\t".'<title>Newsfeeds '.BLOGOTEXT_NAME.' '.BLOGOTEXT_VERSION.' on '.date('Y/m/d').'</title>'."\n";
	$html .= "\t".'</head>'."\n";
	$html .= "\t".'<body>'."\n";
	function esc($a) {
		return htmlspecialchars($a, ENT_QUOTES, 'UTF-8');
	}

	foreach ($folders as $i => $folder) {
		$outline = '';
		foreach ($folder as $j => $feed) {
			$outline .= ($i ? "\t" : '')."\t\t".'<outline text="'.esc($feed['title']).'" title="'.esc($feed['title']).'" type="rss" xmlUrl="'.esc($feed['link']).'" />'."\n";
		}
		if ($i != '') {
			$html .= "\t\t".'<outline text="'.esc($i).'" title="'.esc($i).'" >'."\n";
			$html .= $outline;
			$html .= "\t\t".'</outline>'."\n";	
		} else {
			$html .= $outline;
		}
	}

	$html .= "\t".'</body>'."\n".'</opml>';

	// écriture du fichier
	$file = 'backup-data-'.date('Ymd-His').'.opml';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;
	return (file_put_contents(DIR_BACKUP.$file, $html) === FALSE) ? FALSE : $filepath;

}

// Parse et importe un fichier de liste de flux OPML
function importer_opml($opml_content) {
	$GLOBALS['array_new'] = array();

	function parseOpmlRecursive($xmlObj) {
		// si c’est un sous dossier avec d’autres flux à l’intérieur : note le nom du dossier
		$folder = $xmlObj->attributes()->text;
		foreach($xmlObj->children() as $child) {
			if (!empty($child['xmlUrl'])) {
				$url = (string)$child['xmlUrl'];
				$title = ( !empty($child['text']) ) ? (string)$child['text'] : (string)$child['title'];
				$GLOBALS['array_new'][$url] = array(
					'link' => $url,
					'title' => ucfirst($title),
					'favicon' => 'style/rss-feed-icon.png',
					'checksum' => '0',
					'time' => '0',
					'folder' => (string)$folder,
					'iserror' => 0,
				);
			}
	 		parseOpmlRecursive($child);
		}
	}
	$opmlFile = new SimpleXMLElement($opml_content);
	parseOpmlRecursive($opmlFile->body);

	$old_len = count($GLOBALS['liste_flux']);
	$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));
	$GLOBALS['liste_flux'] = array_merge($GLOBALS['array_new'], $GLOBALS['liste_flux']);
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');

	return (count($GLOBALS['liste_flux']) - $old_len);
}

// Parse and import HTML bookmarks (netscape/Firefox bookmarks export)
function parse_html($content) {
	$out_array = array();
	// Netscape bookmark file (Firefox).
	if (strcmp(substr($content, 0, strlen('<!DOCTYPE NETSCAPE-Bookmark-file-1>')), '<!DOCTYPE NETSCAPE-Bookmark-file-1>') === 0) {
		// This format is supported by all browsers (except IE, of course), also delicious, diigo and others.
		$ids_array = array();
		$tab1_DT = explode('<DT>',$content);
		foreach ($tab1_DT as $dt) {
			$link = array('bt_id' => '', 'bt_title' => '', 'bt_link' => '', 'bt_content' => '', 'bt_wiki_content' => '', 'bt_tags' => '', 'bt_statut' => 1, 'bt_type' => 'link');
			$d = explode('<DD>', $dt);
			if (strcmp(substr($d[0], 0, strlen('<A ')), '<A ') === 0) {
				$link['bt_content'] = (isset($d[1]) ? html_entity_decode(trim($d[1]), ENT_QUOTES,'utf-8') : '');  // Get description (optional)
				$link['bt_wiki_content'] = $link['bt_content'];
				preg_match('!<A .*?>(.*?)</A>!i',$d[0],$matches); $link['bt_title'] = (isset($matches[1]) ? trim($matches[1]) : '');  // Get title
				$link['bt_title'] = html_entity_decode($link['bt_title'], ENT_QUOTES, 'utf-8');
				preg_match_all('# ([A-Z_]+)=\"(.*?)"#i', $dt, $matches, PREG_SET_ORDER); // Get all other attributes
				$raw_add_date = 0;
				foreach($matches as $m) {
					$attr = $m[1]; $value = $m[2];
					if ($attr == 'HREF') { $link['bt_link'] = html_entity_decode($value, ENT_QUOTES, 'utf-8'); }
					elseif ($attr == 'ADD_DATE') { $raw_add_date = intval($value); }
					elseif ($attr == 'PRIVATE') { $link['bt_statut'] = ($value == '1') ? '0' : '1'; } // value=1 =>> statut=0 (it’s reversed)
					elseif ($attr == 'TAGS') { $link['bt_tags'] = str_replace('  ', ' ', str_replace(',', ', ', html_entity_decode($value, ENT_QUOTES, 'utf-8'))); }
				}
				if ($link['bt_link'] != '') {
					$raw_add_date = (empty($raw_add_date)) ? time() : $raw_add_date; // In case of shitty bookmark file with no ADD_DATE
					while (in_array(date('YmdHis', $raw_add_date), $ids_array)) $raw_add_date--; // avoids duplicate IDs
					$ids_array[] = $link['bt_id'] = date('YmdHis', $raw_add_date); // converts date to YmdHis format
					$out_array[] = $link;
				}
			}
		}
	}
	return $out_array;
}




// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['titre_maintenance'], "maintenance");
afficher_topnav($GLOBALS['lang']['titre_maintenance'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

// création du dossier des backups
creer_dossier(DIR_BACKUP, 0);


/*
 * Affiches les formulaires qui demandent quoi faire.
 * Font le traitement dans les autres cas.
*/

// no $do nor $file : ask what to do
echo '<div id="maintenance-form">'."\n";
if (!isset($_GET['do']) and !isset($_FILES['file'])) {
	$token = new_token();
	$nbs = array('10'=>'10', '20'=>'20', '50'=>'50', '100'=>'100', '200'=>'200', '500'=>'500', '-1' => $GLOBALS['lang']['pref_all']);

	echo '<form action="maintenance.php" method="get" class="bordered-formbloc" id="form_todo">'."\n";
	echo '<label for="select_todo">'.$GLOBALS['lang']['maintenance_ask_do_what'].'</label>'."\n";
	echo '<select id="select_todo" name="select_todo" onchange="switch_form(this.value)">'."\n";
	echo "\t".'<option selected disabled hidden value=""></option>'."\n";
	echo "\t".'<option value="form_export">'.$GLOBALS['lang']['maintenance_export'].'</option>'."\n";
	echo "\t".'<option value="form_import">'.$GLOBALS['lang']['maintenance_import'].'</option>'."\n";
	echo "\t".'<option value="form_optimi">'.$GLOBALS['lang']['maintenance_optim'].'</option>'."\n";
	echo '</select>'."\n";
	echo '</form>'."\n";

	// Form export
	echo '<form action="maintenance.php" onsubmit="hide_forms(\'exp-format\')" method="get" class="bordered-formbloc" id="form_export">'."\n";
	// choose export what ?
		echo '<fieldset>'."\n";
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_export'].'</legend>';
		echo "\t".'<p><label for="json">'.$GLOBALS['lang']['bak_export_json'].'</label>'.
			'<input type="radio" name="exp-format" value="json" id="json" onchange="switch_export_type(\'e_json\')" /></p>'."\n";
		echo "\t".'<p><label for="html">'.$GLOBALS['lang']['bak_export_netscape'].'</label>'.
			'<input type="radio" name="exp-format" value="html" id="html" onchange="switch_export_type(\'e_html\')" /></p>'."\n";
		echo "\t".'<p><label for="opml">'.$GLOBALS['lang']['bak_export_opml'].'</label>'.
			'<input type="radio" name="exp-format" value="opml"  id="opml"  onchange="switch_export_type(\'e_opml\')"  /></p>'."\n";
		echo "\t".'<p><label for="zip">'.$GLOBALS['lang']['bak_export_zip'].'</label>'.
			'<input type="radio" name="exp-format" value="zip"  id="zip"  onchange="switch_export_type(\'e_zip\')"  /></p>'."\n";
		echo '</fieldset>'."\n";

		// export in JSON.
		echo '<fieldset id="e_json">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_incl_quoi'].'</legend>';
		echo "\t".'<p>'.form_checkbox('incl-artic', 0, $GLOBALS['lang']['bak_articles_do']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-comms', 0, $GLOBALS['lang']['bak_comments_do']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-links', 0, $GLOBALS['lang']['bak_links_do']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-notes', 0, $GLOBALS['lang']['bak_notes_do']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-agend', 0, $GLOBALS['lang']['bak_agenda_do']).'</p>'."\n";
		echo '</fieldset>'."\n";

		// export links in html
		echo '<fieldset id="e_html">'."\n";
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['bak_combien_linx'].'</legend>';
		echo "\t".'<p>'.form_select('nb-links2', $nbs, 50, $GLOBALS['lang']['bak_combien_linx']).'</p>'."\n";
		echo '</fieldset>'."\n";

		// export data in zip
		echo '<fieldset id="e_zip">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_incl_quoi'].'</legend>';
		if (DBMS == 'sqlite')
		echo "\t".'<p>'.form_checkbox('incl-sqlit', 0, $GLOBALS['lang']['bak_incl_sqlit']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-files', 0, $GLOBALS['lang']['bak_incl_files']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-confi', 0, $GLOBALS['lang']['bak_incl_confi']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-theme', 0, $GLOBALS['lang']['bak_incl_theme']).'</p>'."\n";
		echo '</fieldset>'."\n";
		echo '<p class="submit-bttns">'."\n";
		echo "\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'maintenance.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		echo "\t".'<button class="submit button-submit" type="submit" name="do" value="export">'.$GLOBALS['lang']['valider'].'</button>'."\n";
		echo '</p>'."\n";
		echo hidden_input('token', $token);
	echo '</form>'."\n";

	// Form import
	$importformats = array(
		'jsonbak' => $GLOBALS['lang']['bak_import_btjson'],
		'htmllinks' => $GLOBALS['lang']['bak_import_netscape'],
		'rssopml' => $GLOBALS['lang']['bak_import_rssopml']
	);
	echo '<form action="maintenance.php" method="post" enctype="multipart/form-data" class="bordered-formbloc" id="form_import">'."\n";
		echo '<fieldset class="pref valid-center">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_import'].'</legend>';
		echo "\t".'<p>'.form_select_no_label('imp-format', $importformats, 'jsonbak');
		echo '<input type="file" name="file" id="file" class="text" /></p>'."\n";
		echo '</fieldset>'."\n";
		echo '<p class="submit-bttns">'."\n";
		echo "\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'maintenance.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		echo "\t".'<button class="submit button-submit" type="submit" name="valider">'.$GLOBALS['lang']['valider'].'</button>'."\n";
		echo '</p>'."\n";

		echo hidden_input('token', $token);
	echo '</form>'."\n";

	// Form optimi
	echo '<form action="maintenance.php" method="get" class="bordered-formbloc" id="form_optimi">'."\n";
		echo '<fieldset class="pref valid-center">';
		echo '<legend class="legend-sweep">'.$GLOBALS['lang']['maintenance_optim'].'</legend>';

		echo "\t".'<p>'.select_yes_no('opti-file', 0, $GLOBALS['lang']['bak_opti_miniature']).'</p>'."\n";
		if (DBMS == 'sqlite') {
			echo "\t".'<p>'.select_yes_no('opti-vacu', 0, $GLOBALS['lang']['bak_opti_vacuum']).'</p>'."\n";
		} else {
			echo hidden_input('opti-vacu', 0);
		}
		echo "\t".'<p>'.select_yes_no('opti-comm', 0, $GLOBALS['lang']['bak_opti_recountcomm']).'</p>'."\n";
		echo "\t".'<p>'.select_yes_no('opti-rss', 0, $GLOBALS['lang']['bak_opti_supprreadrss']).'</p>'."\n";
		echo '</fieldset>'."\n";
		echo '<p class="submit-bttns">'."\n";
		echo "\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'maintenance.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		echo "\t".'<button class="submit button-submit" type="submit" name="do" value="optim">'.$GLOBALS['lang']['valider'].'</button>'."\n";
		echo '</p>'."\n";
		echo hidden_input('token', $token);
	echo '</form>'."\n";

// either $do or $file
// $do
} else {
	// vérifie Token
	if ($erreurs_form = valider_form_maintenance()) {
		echo '<div class="bordered-formbloc">'."\n";
		echo '<fieldset class="pref valid-center">'."\n";
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['bak_restor_done'].'</legend>';
		echo erreurs($erreurs_form);
		echo '<p class="submit-bttns"><button class="submit button-submit" type="button" onclick="goToUrl(\'maintenance.php\')">'.$GLOBALS['lang']['valider'].'</button></p>'."\n";
		echo '</fieldset>'."\n";
		echo '</div>'."\n";

	} else {
		// token : ok, go on !
		if (isset($_GET['do'])) {
			if ($_GET['do'] == 'export') {
				// Export in JSON file
				if (@$_GET['exp-format'] == 'json') {
					$data_array = array(
						'articles' => array(),
						'links' => array(),
						'commentaires' => array(),
						'notes' => array(),
						'agenda' => array()
					);

					// retreive data that has been asked
					if (isset($_GET['incl-links'])) $data_array['links'] = liste_elements('SELECT * FROM links ORDER BY bt_id DESC ', array(), 'links');
					if (isset($_GET['incl-artic'])) $data_array['articles'] = liste_elements('SELECT * FROM articles ORDER BY bt_id DESC ', array(), 'articles');
					if (isset($_GET['incl-comms'])) $data_array['commentaires'] = liste_elements('SELECT * FROM commentaires ORDER BY bt_id DESC', array(), 'commentaires');
					if (isset($_GET['incl-notes'])) $data_array['notes'] = liste_elements('SELECT * FROM notes ORDER BY bt_id DESC', array(), 'notes');
					if (isset($_GET['incl-agend'])) $data_array['agenda'] = liste_elements('SELECT * FROM agenda ORDER BY bt_id DESC', array(), 'agenda');
					// jsonise it
					$file_archive = creer_fichier_json($data_array);

				// Export links in HTML format
				} elseif (@$_GET['exp-format'] == 'html') {
					$file_archive = creer_fich_html('');

				// Export a ZIP archive
				} elseif (@$_GET['exp-format'] == 'zip') {
					$dossiers = array();
					if (isset($_GET['incl-sqlit'])) {
						$dossiers[] = DIR_DATABASES;
					}
					if (isset($_GET['incl-files'])) {
						$dossiers[] = DIR_DOCUMENTS;
						$dossiers[] = DIR_IMAGES;
					}
					if (isset($_GET['incl-confi'])) {
						$dossiers[] = DIR_CONFIG;
					}
					if (isset($_GET['incl-theme'])) {
						$dossiers[] = DIR_THEMES;
					}
					$file_archive = creer_fichier_zip($dossiers);

				// Export a OPML rss lsit
				} elseif (@$_GET['exp-format'] == 'opml') {
					$file_archive = creer_fichier_opml();

				} else {
					echo 'nothing to do';
				}

				// affiche le formulaire de téléchargement et de validation.
				if (!empty($file_archive)) {
					echo '<form action="maintenance.php" method="get" class="bordered-formbloc">'."\n";
					echo '<fieldset class="pref valid-center">';
					echo '<legend class="legend-backup">'.$GLOBALS['lang']['bak_succes_save'].'</legend>';
					echo '<p><a href="'.$file_archive.'" download>'.$GLOBALS['lang']['bak_dl_fichier'].'</a></p>'."\n";
					echo '<p class="submit-bttns"><button class="submit button-submit" type="submit">'.$GLOBALS['lang']['valider'].'</button></p>'."\n";
					echo '</fieldset>'."\n";
					echo '</form>'."\n";
				}

			} elseif ($_GET['do'] == 'optim') {
					// recount files DB
					if ($_GET['opti-file'] == 1) {
						rebuilt_file_db();
					}
					// vacuum SQLite DB
					if ($_GET['opti-vacu'] == 1) {
						try {
							$req = $GLOBALS['db_handle']->prepare('VACUUM');
							$req->execute();
						} catch (Exception $e) {
							die('Erreur 1429 vacuum : '.$e->getMessage());
						}
					}
					// recount comms/articles
					if ($_GET['opti-comm'] == 1) {
						recompte_commentaires();
					}
					// delete old RSS entries
					if ($_GET['opti-rss'] == 1) {
						try {
							$req = $GLOBALS['db_handle']->prepare('DELETE FROM rss WHERE bt_statut=0 AND WHERE bt_bookmarked=0');
							$req->execute(array());
						} catch (Exception $e) {
							die('Erreur : 7873 : rss delete old entries : '.$e->getMessage());
						}
					}
					echo '<form action="maintenance.php" method="get" class="bordered-formbloc">'."\n";
					echo '<fieldset class="pref valid-center">';
					echo '<legend class="legend-backup">'.$GLOBALS['lang']['bak_optim_done'].'</legend>';
					echo '<p class="submit-bttns"><button class="submit button-submit" type="submit">'.$GLOBALS['lang']['valider'].'</button></p>'."\n";
					echo '</fieldset>'."\n";
					echo '</form>'."\n";

			} else {
				echo 'nothing to do.';
			}

		// $file
		} elseif (isset($_POST['valider']) and !empty($_FILES['file']['tmp_name']) ) {
				$message = array();
				switch($_POST['imp-format']) {
					case 'jsonbak':
						$json = file_get_contents($_FILES['file']['tmp_name']);
						$message = insert_bak_table($json);
					break;
					case 'htmllinks':
						$html = file_get_contents($_FILES['file']['tmp_name']);
						$message['links'] = insert_table_links(parse_html($html));
					break;
					case 'rssopml':
						$xml = file_get_contents($_FILES['file']['tmp_name']);
						$message['feeds'] = importer_opml($xml);
					break;
					default: die('nothing'); break;
				}
				if (!empty($message)) {
					echo '<form action="maintenance.php" method="get" class="bordered-formbloc">'."\n";
					echo '<fieldset class="pref valid-center">';
					echo '<legend class="legend-backup">'.$GLOBALS['lang']['bak_restor_done'].'</legend>';
					echo '<ul>';
					foreach ($message as $type => $nb) echo '<li>'.$GLOBALS['lang']['label_'.$type].' : '.$nb.'</li>'."\n";
					echo '</ul>';
					echo '<p class="submit-bttns"><button class="submit button-submit" type="submit">'.$GLOBALS['lang']['valider'].'</button></p>'."\n";
					echo '</fieldset>'."\n";
					echo '</form>'."\n";
				}

		} else {
			echo 'nothing to do.';
		}
	}
}

echo '</div>'."\n";


echo "\n".'<script src="style/javascript.js" type="text/javascript"></script>'."\n";

footer($begin);

