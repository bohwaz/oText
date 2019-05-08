<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

set_time_limit (180);
require_once 'inc/boot.php';
operate_session();
$GLOBALS['liste_flux'] = open_serialzd_file(FEEDS_DB);

// création du dossier des backups
creer_dossier(DIR_BACKUP, 0);



/*
 *
 *
 *
 *
 *
 *  E X P O R T     F U N C T I O N S 
 */


///////////////////////////////////////////////////////////////////////////////////
//
// Creates the HTML file containing the links
// The HTML fil has the same format as all major browser "import bookmarks" format.
//

function creer_fich_html() {
	// récupère les liens
	$query = "SELECT * FROM links ORDER BY bt_id DESC";
	$list = liste_elements($query, array());

	// génération du code HTML.
	$html = '<!DOCTYPE NETSCAPE-Bookmark-file-1><META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">'."\n";
	$html .= '<TITLE>Blogotext links export '.date('Y-M-D').'</TITLE><H1>Blogotext links export</H1>'."\n";
	foreach ($list as $n => $link) {
		$dec = decode_id($link['bt_id']);
		$timestamp = mktime($dec['h'], $dec['i'], $dec['s'], $dec['m'], $dec['d'], $dec['y']); // HISMDY : wtf!
		$html .= '<DT><A HREF="'.$link['bt_link'].'" ADD_DATE="'.$timestamp.'" PRIVATE="'.abs(1-$link['bt_statut']).'" TAGS="'.$link['bt_tags'].'">'.$link['bt_title'].'</A>'."\n";
		$html .= '<DD>'.strip_tags($link['bt_wiki_content'])."\n";
	}

	// écriture du fichier
	$file = 'backup-links-'.date('Ymd-His').'.html';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;
	return (file_put_contents(DIR_BACKUP.$file, $html) === FALSE) ? FALSE : $filepath; // écriture du fichier
}


///////////////////////////////////////////////////////////////////////////////////
//
// Creates the Zip archive, with several oText data storage folders
//

function creer_fichier_zip($dossiers) {
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

	foreach($dossiers as $i => $dossier) {
		$dossiers[$i] = '../'.str_replace(BT_ROOT, '', $dossier); // FIXME : find cleaner way for '../';
	}
	$file = 'backup-zip-'.date('Ymd-His').'.zip';
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

///////////////////////////////////////////////////////////////////////////////////
//
// Creates the JSON export file, with a provided data Array.
//

function creer_fichier_json($data_array) {
	$file = 'backup-data-'.date('Ymd-His').'.json';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;
	return (file_put_contents(DIR_BACKUP.$file, json_encode($data_array)) === FALSE) ? FALSE : $filepath;
}

///////////////////////////////////////////////////////////////////////////////////
//
// Creates the OPML file
//

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

	$file = 'backup-feeds-'.date('Ymd-His').'.opml';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;

	return (file_put_contents(DIR_BACKUP.$file, $html) === FALSE) ? FALSE : $filepath;
}

///////////////////////////////////////////////////////////////////////////////////
//
// Creates the XML file for the notes
//

function creer_fichier_xmlnotes() {
	// récupère les notes
	$query = "SELECT * FROM notes ORDER BY bt_id DESC";
	$list = liste_elements($query, array());

	function esc($a) {
		return htmlspecialchars($a, ENT_QUOTES, 'UTF-8');
	}

	$html  = '<?xml version="1.0" encoding="utf-8"?>'."\n";
	$html .= '<stickynotes version="1.20.1">'."\n";
	foreach ($list as $note) {
		$html .= "\t".'<note title="'.esc($note['bt_title']).'" color="'.$note['bt_color'].'" locked="'.$note['bt_pinned'].'" pinned="'.$note['bt_pinned'].'" statut="'.$note['bt_statut'].'" id="'.$note['bt_id'].'">';
		$html .= $note['bt_content'];
		$html .= "\t".'</note>'."\n";
	}
	$html .= "\t".'</stickynotes>';

	$file = 'backup-notes-'.date('Ymd-His').'.xml';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;

	return (file_put_contents(DIR_BACKUP.$file, $html) === FALSE) ? FALSE : $filepath;
}



///////////////////////////////////////////////////////////////////////////////////
//
// Creates the VCF file for contacts
//

function creer_fichier_vcfcontacts() {
	// fetch contacts
	$query = "SELECT * FROM contacts ORDER BY LOWER(bt_fullname) ASC";
	$list = liste_elements($query, array());

	// parse some JSON stored elements
	foreach ($list as $i => $contact) {
		$list[$i]['bt_address'] = json_decode($contact['bt_address'], true); // true : to array() instead of object()
		$list[$i]['bt_phone'] = json_decode($contact['bt_phone']);
		$list[$i]['bt_email'] = json_decode($contact['bt_email']);
		$list[$i]['bt_websites'] = json_decode($contact['bt_websites']);
		$list[$i]['bt_social'] = json_decode($contact['bt_social']);
	}

	$vcard = '';

	foreach ($list as $i => $contact) {
		$vcard .= 'BEGIN:VCARD'."\n";
		$vcard .= 'VERSION:3.0'."\n";

		$vcard .= 'FN:'.$contact['bt_fullname']."\n";
		$vcard .= ((strpos($contact['bt_fullname'], ' ') !== FALSE) ? 'N:'.implode(';', (explode(' ', $contact['bt_fullname'], 2))) : 'N:;'.$contact['bt_fullname']) . ';;'.$contact['bt_title'].';'."\n";

		if ($contact['bt_surname'])
		$vcard .= 'NICKNAME:'.$contact['bt_surname']."\n";

		foreach ($contact['bt_phone'] as $tel => $value)
		$vcard .= 'TEL;TYPE=CELL:'.$value."\n";

		foreach ($contact['bt_email'] as $mail => $value)
		$vcard .= 'EMAIL;TYPE=INTERNET:'.$value."\n";

		foreach ($contact['bt_websites'] as $url => $value)
		$vcard .= 'URL:'.$value."\n";

		foreach ($contact['bt_social'] as $link => $value)
		$vcard .= 'SOCIALPROFILE:'.$value."\n";

		if ($contact['bt_notes'])
		$vcard .= 'NOTES:'.$contact['bt_notes']."\n";

		if ($contact['bt_birthday'])
		$vcard .= 'BDAY:'.$contact['bt_birthday']."\n";

		if (implode($contact['bt_address']))
		$vcard .= 'ADR:'.$contact['bt_address']['nb'].";".$contact['bt_address']['co'].";".$contact['bt_address']['st'].";".$contact['bt_address']['ci'].";".$contact['bt_address']['sa'].";".$contact['bt_address']['cp'].";".$contact['bt_address']['cn']."\n";

		if ($contact['bt_label'])
		$vcard .= 'X-OLABEL:'.$contact['bt_label']."\n";

		if ($contact['bt_image'])
		$vcard .= 'PHOTO:'.$contact['bt_image']."\n";

		$vcard .= 'END:VCARD'."\n";
	}

	$file = 'backup-contacts-'.date('Ymd-His').'.vcf';
	$filepath = str_replace(realpath($_SERVER['DOCUMENT_ROOT']), '', dirname(__DIR__)).'/'.str_replace(BT_ROOT, '', DIR_BACKUP).$file;

	return (file_put_contents(DIR_BACKUP.$file, $vcard) === FALSE) ? FALSE : $filepath;

}



/*
 *
 *
 *
 *
 *
 *  I M P O R T     F U N C T I O N S 
 */


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
	$img_in_db = liste_elements($query, array());

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
 * liste une table (ex: les commentaires) et comparre avec un tableau de commentaires trouvées dans l’archive
 * Retourne un tableau avec les éléments absents de la base
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

	// remplit le tableau
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

/* IMPORTER UN FICHIER json AU FORMAT DE oTEXT */
function import_json_file($json) {
	$data = json_decode($json, true);
	$return = array();
	$flag_has_comms = false;

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
							if (preg_match('#\d{14}#', $com['bt_article_id'])) {
								$com['bt_article_id'] = substr(md5($com['bt_article_id']), 0, 6);
							}

							$query = 'INSERT INTO commentaires (bt_type, bt_id, bt_article_id, bt_content, bt_wiki_content, bt_author, bt_link, bt_webpage, bt_email, bt_subscribe, bt_statut) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
							$array = array($com['bt_type'], $com['bt_id'], $com['bt_article_id'], $com['bt_content'], $com['bt_wiki_content'], $com['bt_author'], $com['bt_link'], $com['bt_webpage'], $com['bt_email'], $com['bt_subscribe'], $com['bt_statut']);
							$req = $GLOBALS['db_handle']->prepare($query);
							$req->execute($array);
							$flag_has_comms = true;
						}
						break;

				} // end switch
			} // end if
		} // end forearch

		$GLOBALS['db_handle']->commit();

		if ($flag_has_comms === true) {
			recompte_commentaires();
		}

	} catch (Exception $e) {
		$req->rollBack();
		die('Erreur 7241 on import JSON : '.$e->getMessage());
	}

	return $return;
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
					'favicon' => '',
					'checksum' => '0',
					'time' => '0',
					'folder' => (string)$folder,
					'iserror' => 0,
					'nbrun' => 0,
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

// Parse HTML bookmarks (netscape/Firefox bookmarks export) file
function import_html_links($content) {
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
				$raw_content = (isset($d[1]) ? html_entity_decode(trim($d[1]), ENT_QUOTES,'utf-8') : '');
				$link['bt_content'] = markup(htmlspecialchars(clean_txt($raw_content), ENT_NOQUOTES));  // Get description (optional)
				$link['bt_wiki_content'] = $raw_content;

				preg_match('!<A .*?>(.*?)</A>!i',$d[0],$matches); $link['bt_title'] = (isset($matches[1]) ? trim($matches[1]) : '');  // Get title
				$link['bt_title'] = html_entity_decode($link['bt_title'], ENT_QUOTES, 'utf-8');
				preg_match_all('# ([A-Z_]+)=\"(.*?)"#i', $dt, $matches, PREG_SET_ORDER); // Get all other attributes
				$raw_add_date = time();
				foreach($matches as $m) {
					$attr = $m[1]; $value = $m[2];
					if ($attr == 'HREF') { $link['bt_link'] = html_entity_decode($value, ENT_QUOTES, 'utf-8'); }
					elseif ($attr == 'ADD_DATE') { $raw_add_date = intval($value); }
					elseif ($attr == 'PRIVATE') { $link['bt_statut'] = ($value == '1') ? '0' : '1'; } // value=1 =>> statut=0 (it’s reversed)
					elseif ($attr == 'TAGS') { $link['bt_tags'] = str_replace('  ', ' ', str_replace(',', ', ', html_entity_decode($value, ENT_QUOTES, 'utf-8'))); }
				}
				while (in_array(date('YmdHis', $raw_add_date), $ids_array)) $raw_add_date--; // avoids duplicate IDs
				$link['bt_id'] = date('YmdHis', $raw_add_date); // converts date to YmdHis format

				if ($link['bt_link'] != '') {
					$ids_array[] = $link['bt_id'];
					$out_array[] = $link;
				}
			}
		}
	}

	$to_save_links = diff_trouve_base('links', $out_array);

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach($to_save_links as $link) {
			$query = 'INSERT INTO links (bt_type, bt_id, bt_link, bt_content, bt_wiki_content, bt_statut, bt_title, bt_tags ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ? ) ';
			$array = array($link['bt_type'], $link['bt_id'], $link['bt_link'], $link['bt_content'], $link['bt_wiki_content'], $link['bt_statut'], $link['bt_title'], $link['bt_tags']);
			$req = $GLOBALS['db_handle']->prepare($query);
			$req->execute($array);
		}

		$GLOBALS['db_handle']->commit();

	} catch (Exception $e) {
		$req->rollBack();
		die('Erreur 7241 on import HTML links : '.$e->getMessage());
	}

	return count($to_save_links);
}

// Parse and import VCF contacts
function importer_vcf($vcf_content) {

	// get existing contacts (names and ID)
	$query = "SELECT bt_fullname, bt_id FROM contacts";
	$contacts_in_db = liste_elements($query, array());

	// rearange
	foreach ($contacts_in_db as $i => $contact) {
		$contacts_in_db[$contact['bt_fullname']] = $contact['bt_id'];
		unset($contacts_in_db[$i]);
	}

	// ID-only array
	$id_contacts = array_values($contacts_in_db);

	// begin   //
	// parsing //
	$contacts_raw = explode("BEGIN:VCARD", $vcf_content);

	foreach($contacts_raw as $i => $contact) {
		$contacts_raw[$i] = explode("\n", $contact);
		foreach ($contacts_raw[$i] as $line => $field) {
			$contacts_raw[$i][$line] = trim($field);
			if ($contacts_raw[$i][$line] == "") unset($contacts_raw[$i][$line]);
			elseif ($contacts_raw[$i][$line] == "VERSION:3.0") unset($contacts_raw[$i][$line]);
			elseif ($contacts_raw[$i][$line] == "END:VCARD") unset($contacts_raw[$i][$line]);
		}
		if (count($contacts_raw[$i]) == 0) unset($contacts_raw[$i]);
	}

	$contacts_parsed = array();
	foreach($contacts_raw as $i => $contact) {
		// create new ID (based on current time), and check if already exists. If it does, decrement is by one.
		$date_for_id = time();
		$new_id = date('YmdHis', $date_for_id);
		while (in_array($new_id, $id_contacts)) { $new_id = date('YmdHis', $date_for_id--); } $id_contacts[] = $new_id;

		$new = array(
			'fullname' => '',
			'title' => '',
			'tel' => array(),
			'email' => array(),
			'pseudo' => '',
			'birthday' => '',
			'websites' => array(),
			'notes' => '',
			'img' => '',
			'label' => '',
			'star' => '',
			'other' => '',
			'imgIsNew' => FALSE,
			'social' => array(),
			'id' => $new_id,
			'address' => array('nb' => '','st' => '','co' => '','cp' => '','ci' => '','sa' => '','cn' => ''),
		);

		foreach ($contacts_raw[$i] as $j => $field) {
			// FN : formated name (required)
			if (strpos($field, 'FN:') === 0) {
				preg_match('#^FN:(.*)$#', $field, $matches);
				$new['fullname'] = $matches[1];
			}
			// TITLE : Title
			elseif (strpos($field, 'TITLE') !== FALSE) {
				preg_match('#TITLE(.*):(.*)$#', $field, $matches);
				$new['title'] = $matches[2];
			}
			// BDAY : Birthday
			elseif (strpos($field, 'BDAY') !== FALSE) {
				preg_match('#BDAY(.*):(.*)$#', $field, $matches);
				$new['birthday'] = $matches[2];
			}
			// NICKNAME : Nickname
			elseif (strpos($field, 'NICKNAME') !== FALSE) {
				preg_match('#NICKNAME(.*):(.*)$#', $field, $matches);
				$new['pseudo'] = $matches[2];
			}
			// TEL : phones
			elseif (strpos($field, 'TEL') === 0) {
				preg_match('#^TEL(.*):(.*)$#', $field, $matches);
				$new['tel'][] = str_replace(array(' ', '-', '.', '_'), '', $matches[2]);
			}
			// EMAIL : emails
			elseif (strpos($field, 'EMAIL') !== FALSE) {
				preg_match('#EMAIL(.*):(.*)$#', $field, $matches);
				$new['email'][] = $matches[2];
			}
			// URL : websites, blogs, url
			elseif (strpos($field, 'URL') !== FALSE) {
				preg_match('#URL:(.*)$#', $field, $matches);
				$new['websites'][] = str_replace('\:', ':', $matches[1]);
			}
			// ADR : adress (nb;complement;street;city;state;zip;country)
			elseif (strpos($field, 'ADR') !== FALSE) {
				preg_match('#ADR(.*):(.*)$#', $field, $matches);
				list($new['address']['nb'], $new['address']['co'], $new['address']['st'], $new['address']['ci'], $new['address']['sa'], $new['address']['cp'], $new['address']['cn']) = explode(';', $matches[2]);
			}
			// NOTE : some misc info
			elseif (strpos($field, 'NOTE') !== FALSE) {
				preg_match('#NOTE:(.*)$#', $field, $matches);
				$new['note'] = $matches[1];
			}
			// PHOTO : some image (uri, base64…) associated with the person
			elseif (strpos($field, 'PHOTO') !== FALSE) {
				preg_match('#PHOTO:(.*)$#', $field, $matches);
				$new['img'] = $matches[1];
			}
			// NON STANDARD FIELDS
			elseif (strpos($field, 'X-') === 0) {
				// oText Label
				preg_match('#^X-OLABEL:(.*)$#', $field, $matches);
				if (isset($matches[1])) {
					$new['label'] = $matches[1];
				}
				else {
					// X-TWITTER / X-FACEBOOK : some social media accounts 
					preg_match('#^X-(AIM|ICQ|GTALK|JABBER|MSN|TWITTER|SKYPE|FACEBOOK|SOCIALPROFILE):(.+)$#', $field, $matches);
					$new['social'][] = str_replace('\:', ':', $matches[2]);
				}
			}
			elseif (strpos($field, 'SOCIALPROFILE') === 0) {
				preg_match('#^SOCIALPROFILE:(.*)$#', $field, $matches);
				$new['social'][] = $matches[1];
			}
		}
		// if valid contact (with a name) AND not already existing
		if (!empty($new['fullname']) and !isset($contacts_in_db[$new['fullname']])) {
			$new['address'] = json_encode($new['address']);
			$new['tel'] = json_encode($new['tel']);
			$new['email'] = json_encode($new['email']);
			$new['social'] = json_encode($new['social']);
			$new['websites'] = json_encode($new['websites']);
			$contacts_parsed[] = $new;
		}
	}

	// finally, append them to DB.
	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach ($contacts_parsed as $i => $contact) {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO contacts ( bt_id, bt_type, bt_title, bt_fullname, bt_surname, bt_birthday, bt_address, bt_phone, bt_email, bt_websites, bt_social, bt_image, bt_label, bt_notes, bt_stared, bt_other ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array($contact['id'], 'person', $contact['title'], $contact['fullname'], $contact['pseudo'], $contact['birthday'], $contact['address'], $contact['tel'], $contact['email'], $contact['websites'], $contact['social'], $contact['img'], $contact['label'], $contact['notes'], $contact['star'], $contact['other']));
		}

		$GLOBALS['db_handle']->commit();

	} catch (Exception $e) {
		die('Erreur 8282 on import VCF contacts : '.$e->getMessage());
	}

	return count($contacts_parsed);
}



// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['titre_maintenance'], "maintenance");
afficher_topnav($GLOBALS['lang']['titre_maintenance'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

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
		echo "\t".'<p><label for="json">'.$GLOBALS['lang']['bak_export_json'].'</label><input type="radio" name="exp-format" value="json" id="json" onchange="switch_export_type(\'e_json\')" /></p>'."\n";
		echo "\t".'<p><label for="html">'.$GLOBALS['lang']['bak_export_netscape'].'</label><input type="radio" name="exp-format" value="html" id="html" onchange="switch_export_type(\'\')" /></p>'."\n";
		echo "\t".'<p><label for="opml">'.$GLOBALS['lang']['bak_export_opml'].'</label><input type="radio" name="exp-format" value="opml" id="opml" onchange="switch_export_type(\'\')" /></p>'."\n";
		echo "\t".'<p><label for="xmln">'.$GLOBALS['lang']['bak_export_xmln'].'</label><input type="radio" name="exp-format" value="xmln" id="xmln" onchange="switch_export_type(\'\')" /></p>'."\n";
		echo "\t".'<p><label for="zip">'.$GLOBALS['lang']['bak_export_zip'].'</label><input type="radio" name="exp-format" value="zip" id="zip" onchange="switch_export_type(\'e_zip\')" /></p>'."\n";
		echo "\t".'<p><label for="vcf">BAK_EXPORT_VCF</label><input type="radio" name="exp-format" value="vcf" id="vcf" onchange="switch_export_type(\'\')" /></p>'."\n";
		echo '</fieldset>'."\n";

		// export in JSON.
		echo '<fieldset id="e_json">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_incl_quoi'].'</legend>';
		echo "\t".'<p>'.form_checkbox('incl-artic', 0, $GLOBALS['lang']['bak_articles_do']).'</p>'."\n";
		echo "\t".'<p>'.form_checkbox('incl-comms', 0, $GLOBALS['lang']['bak_comments_do']).'</p>'."\n";
		echo '</fieldset>'."\n";

		// export data in zip
		echo '<fieldset id="e_zip">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_incl_quoi'].'</legend>';
		if (DBMS == 'sqlite') {
			echo "\t".'<p>'.form_checkbox('incl-sqlit', 0, $GLOBALS['lang']['bak_incl_sqlit']).'</p>'."\n";
		}
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
		'rssopml' => $GLOBALS['lang']['bak_import_rssopml'],
		'ctctvcf' => $GLOBALS['lang']['bak_import_ctctvcf']
	);
	echo '<form action="maintenance.php" method="post" enctype="multipart/form-data" class="bordered-formbloc" id="form_import">'."\n";
		echo '<fieldset class="pref valid-center">';
		echo '<legend class="legend-backup">'.$GLOBALS['lang']['maintenance_import'].'</legend>';
		echo "\t".'<p>'.form_select('imp-format', $importformats, 'jsonbak', '');
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
						'commentaires' => array(),
					);

					// retreive data that has been asked
					if (isset($_GET['incl-artic'])) $data_array['articles'] = liste_elements('SELECT * FROM articles ORDER BY bt_id DESC ', array());
					if (isset($_GET['incl-comms'])) $data_array['commentaires'] = liste_elements('SELECT * FROM commentaires ORDER BY bt_id DESC', array());
					// jsonise it
					$file_archive = creer_fichier_json($data_array);

				// Export links in HTML format
				} elseif (@$_GET['exp-format'] == 'html') {
					$file_archive = creer_fich_html('');

				// Export a ZIP archive
				} elseif (@$_GET['exp-format'] == 'zip') {
					$dossiers = array();
					if (isset($_GET['incl-sqlit'])) { $dossiers[] = DIR_DATABASES; }
					if (isset($_GET['incl-files'])) { $dossiers[] = DIR_DOCUMENTS; $dossiers[] = DIR_IMAGES; }
					if (isset($_GET['incl-confi'])) { $dossiers[] = DIR_CONFIG; }
					if (isset($_GET['incl-theme'])) { $dossiers[] = DIR_THEMES; }
					$file_archive = creer_fichier_zip($dossiers);

				// Export a OPML rss list
				} elseif (@$_GET['exp-format'] == 'opml') {
					$file_archive = creer_fichier_opml();

				// Export an XML notes file
				} elseif (@$_GET['exp-format'] == 'xmln') {
					$file_archive = creer_fichier_xmlnotes();

				// Export an VCF contact files
				} elseif (@$_GET['exp-format'] == 'vcf') {
					$file_archive = creer_fichier_vcfcontacts();

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
						$message = import_json_file($json);
					break;
					case 'htmllinks':
						$html = file_get_contents($_FILES['file']['tmp_name']);
						$message['links'] = import_html_links($html);
					break;
					case 'rssopml':
						$xml = file_get_contents($_FILES['file']['tmp_name']);
						$message['feeds'] = importer_opml($xml);
					break;
					case 'ctctvcf':
						$vcf = file_get_contents($_FILES['file']['tmp_name']);
						$message['contacts'] = importer_vcf($vcf);
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

echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";

footer($begin);
