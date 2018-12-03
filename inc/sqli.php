<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

/*  Creates a new BlogoText base.
    if file does not exists, it is created, as well as the tables.
    if file does exists, tables are checked and created if not exists
*/
function create_tables() {
	if (file_exists(DIR_CONFIG.'mysql.php')) {
		include(DIR_CONFIG.'mysql.php');
	}
	$auto_increment = (DBMS == 'mysql') ? 'AUTO_INCREMENT' : ''; // SQLite doesn't need this, but MySQL does.
	$index_limit_size = (DBMS == 'mysql') ? '(15)' : ''; // MySQL needs a limit for indexes on TEXT fields.
	$if_not_exists = (DBMS == 'sqlite') ? 'IF NOT EXISTS' : ''; // MySQL doesn’t know this statement for INDEXES

	$dbase_structure['links'] = "CREATE TABLE IF NOT EXISTS links
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_type CHAR(20),
			bt_id BIGINT,
			bt_content TEXT,
			bt_wiki_content TEXT,
			bt_title TEXT,
			bt_tags TEXT,
			bt_link TEXT,
			bt_statut TINYINT
		); CREATE INDEX $if_not_exists dateL ON links ( bt_id );";

	$dbase_structure['commentaires'] = "CREATE TABLE IF NOT EXISTS commentaires
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_type CHAR(20),
			bt_id BIGINT,
			bt_article_id TEXT,
			bt_content TEXT,
			bt_wiki_content TEXT,
			bt_author TEXT,
			bt_link TEXT,
			bt_webpage TEXT,
			bt_email TEXT,
			bt_subscribe TINYINT,
			bt_statut TINYINT
		); CREATE INDEX $if_not_exists dateC ON commentaires ( bt_id );";


	$dbase_structure['articles'] = "CREATE TABLE IF NOT EXISTS articles
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_type CHAR(20),
			bt_id TEXT,
			bt_date BIGINT,
			bt_title TEXT,
			bt_abstract TEXT,
			bt_notes TEXT,
			bt_link TEXT,
			bt_content TEXT,
			bt_wiki_content TEXT,
			bt_tags TEXT,
			bt_keywords TEXT,
			bt_nb_comments INTEGER,
			bt_allow_comments TINYINT,
			bt_statut TINYINT
		); CREATE INDEX $if_not_exists dateidA ON articles ( bt_date, bt_id );";

	/* here bt_ID is a GUID, from the feed, not only a 'YmdHis' date string.*/
	$dbase_structure['rss'] = "CREATE TABLE IF NOT EXISTS rss
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_id TEXT,
			bt_date BIGINT,
			bt_title TEXT,
			bt_link TEXT,
			bt_feed TEXT,
			bt_content TEXT,
			bt_statut TINYINT,
			bt_bookmarked TINYINT,
			bt_folder TEXT
		); CREATE INDEX $if_not_exists dateidR ON rss ( bt_date, bt_id$index_limit_size );";

	$dbase_structure['notes'] = "CREATE TABLE IF NOT EXISTS notes
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_id BIGINT,
			bt_title TEXT,
			bt_content TEXT,
			bt_color CHAR(7)
		); CREATE INDEX $if_not_exists dateN ON notes ( bt_id );";

	$dbase_structure['images'] = "CREATE TABLE IF NOT EXISTS images
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_id BIGINT,
			bt_type TEXT,
			bt_fileext TEXT,
			bt_filename TEXT,
			bt_filesize INT,
			bt_content TEXT,
			bt_checksum TEXT,
			bt_statut TINYINT,
			bt_path TEXT,
			bt_folder TEXT,
			bt_dim_w INT,
			bt_dim_h INT
		); CREATE INDEX $if_not_exists dateidI ON notes ( bt_id );";

	$dbase_structure['agenda'] = "CREATE TABLE IF NOT EXISTS agenda
		(
			ID INTEGER PRIMARY KEY $auto_increment,
			bt_id BIGINT,
			bt_date BIGINT,
			bt_event_loc TEXT,
			bt_title TEXT,
			bt_content TEXT
		); CREATE INDEX $if_not_exists dateE ON agenda ( bt_id );";


	/*
	* SQLite
	*
	*/
	switch (DBMS) {
		case 'sqlite':
				if (!is_file(SQL_DB)) {
					if (!creer_dossier(DIR_DATABASES)) {
						die('Impossible de creer le dossier databases (chmod?)');
					}
				}
				$file = SQL_DB;
				// open tables
				try {
					$db_handle = new PDO('sqlite:'.$file);
					$db_handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$db_handle->query("PRAGMA temp_store=MEMORY; PRAGMA synchronous=OFF; PRAGMA journal_mode=WAL;");


					$wanted_tables = array_keys($dbase_structure);
					foreach ($wanted_tables as $table_name) {
							$results = $db_handle->exec($dbase_structure[$table_name]);
					}
				} catch (Exception $e) {
					die('Erreur 1: '.$e->getMessage());
				}
			break;

		/*
		* MySQL
		*
		*/
		case 'mysql':
				try {

					$options_pdo[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
					$db_handle = new PDO('mysql:host='.MYSQL_HOST.';dbname='.MYSQL_DB.";charset=utf8;sql_mode=PIPES_AS_CONCAT;", MYSQL_LOGIN, MYSQL_PASS, $options_pdo);
					// check each wanted table
					$wanted_tables = array_keys($dbase_structure);
					foreach ($wanted_tables as $table_name) {
							$results = $db_handle->query($dbase_structure[$table_name]."DEFAULT CHARSET=utf8");
							$results->closeCursor();
					}
				} catch (Exception $e) {
					die('Erreur 2: '.$e->getMessage());
				}
			break;
	}

	return $db_handle;
}


/* Open a base */
function open_base() {
	$handle = create_tables();
	$handle->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
	return $handle;
}


/* lists articles with search criterias given in $array. Returns an array containing the data*/
function liste_elements($query, $array, $data_type) {
	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		$return = array();

		switch ($data_type) {
			case 'articles':
				while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
					$return[] = init_list_articles($row);
				}
				break;
			case 'commentaires':
				while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
					$return[] = init_list_comments($row);
				}
				break;
			case 'links':
			case 'rss':
			case 'notes':
			case 'images':
			case 'agenda':
				while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
					$return[] = $row;
				}
				break;
			default:
				break;
		}

		return $return;
	} catch (Exception $e) {
		die($query);
		die('Erreur 89208 : '.$e->getMessage());
	}
}

/* same as above, but return the amount of entries */
function liste_elements_count($query, $array) {
	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		$result = $req->fetch();
		return $result['nbr'];
	} catch (Exception $e) {
		die('Erreur 0003: '.$e->getMessage());
	}
}

// returns or prints an entry of some element of some table (very basic)
function get_entry($table, $entry, $id, $retour_mode) {
	$query = "SELECT $entry FROM $table WHERE bt_id=?";
	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute(array($id));
		$result = $req->fetch();
		//echo '<pre>';print_r($result);
	} catch (Exception $e) {
		die('Erreur : '.$e->getMessage());
	}

	if ($retour_mode == 'return' and !empty($result[$entry])) {
		return $result[$entry];
	}
	if ($retour_mode == 'echo' and !empty($result[$entry])) {
		echo $result[$entry];
	}
	return '';
}



// from an array given by SQLite's requests, this function adds some more stuf to data stored by DB.
function init_list_articles($article) {
	if (!empty($article)) {
		//$article = array_merge($article, decode_id($article['bt_date']));
		$article['bt_link'] = get_blogpath($article['bt_id'], $article['bt_title']);
	}
	return $article;
}

function init_list_comments($comment) {
		$comment['auteur_lien'] = (!empty($comment['bt_webpage'])) ? '<a href="'.$comment['bt_webpage'].'" class="webpage">'.$comment['bt_author'].'</a>' : $comment['bt_author'] ;
		$comment['anchor'] = article_anchor($comment['bt_id']);
		if (isset($comment['bt_title'])) {
			$comment['bt_link'] = get_blogpath($comment['bt_article_id'], $comment['bt_title']).'#'.$comment['anchor'];
		}
		$comment = array_merge($comment, decode_id($comment['bt_id']));
	return $comment;
}


// POST ARTICLE
/*
 * On post of an article (always on admin sides)
 * gets posted informations and turn them into
 * an array
 *
 */

function init_post_article() { //no $mode : it's always admin.
	$formated_contenu = markup_articles(clean_txt($_POST['contenu']));
	if ($GLOBALS['automatic_keywords'] == '0') {
		$keywords = protect($_POST['mots_cles']);
	} else {
		$keywords = extraire_mots($_POST['titre'].' '.$formated_contenu);
	}
	$date = str_replace('-', '', $_POST['ymd']) . str_replace(':', '', $_POST['his']);

	$new_id = ($GLOBALS['format_id_blog'] == '1') ? $date : substr(md5($date), 0, 6) ;

	$id = (isset($_POST['article_id'])) ? $_POST['article_id'] : $new_id ;

	$article = array (
		'bt_id'				=> $id,
		'bt_date'			=> $date,
		'bt_title'			=> protect($_POST['titre']),
		'bt_abstract'		=> (empty($_POST['chapo']) ? '' : clean_txt($_POST['chapo'])),
		'bt_notes'			=> protect($_POST['notes']),
		'bt_content'		=> $formated_contenu,
		'bt_wiki_content'	=> clean_txt($_POST['contenu']),
		'bt_link'			=> '', // this one is not needed yet. Maybe in the futur. I dunno why it is still in the DB…
		'bt_keywords'		=> $keywords,
		'bt_tags'			=> (isset($_POST['categories']) ? htmlspecialchars(traiter_tags($_POST['categories'])) : ''),
		'bt_statut'			=> $_POST['statut'],
		'bt_allow_comments'	=> $_POST['allowcomment'],
	);

	if ( isset($_POST['ID']) and is_numeric($_POST['ID']) ) { // ID only added on edit.
		$article['ID'] = $_POST['ID'];
	}
	return $article;
}

// POST COMMENT
/*
 * Same as init_post_article()
 * but, this one can be used on admin side and on public side.
 *
 */
function init_post_comment($article_id, $mode) {
	$comment = array();
	$is_edit = 0;
	if ( $mode == 'admin' and (isset($_POST['com_supprimer']) or isset($_POST['com_activer']) or (isset($_POST['comment_id']) and is_numeric($_POST['comment_id']) )) ) {
		$is_edit = 1;
	}


	if ( isset($article_id) ) {
		if ( ($mode == 'admin') and ($is_edit)) {
			$status = '1';
			$comment_id = $_POST['comment_id'];
		} elseif ($mode == 'admin' and !$is_edit) {
			$status = '1';
			$comment_id = date('YmdHis');
		} else {
			$status = $GLOBALS['comm_defaut_status'];
			$comment_id = date('YmdHis');
		}

		// verif url.
		if (!empty($_POST['webpage'])) {
			$url = protect(  (strpos($_POST['webpage'], 'http://')===0 or strpos($_POST['webpage'], 'https://')===0)? $_POST['webpage'] : 'http://'.$_POST['webpage'] );
		} else { $url = $_POST['webpage']; }

		$comment = array (
			'bt_id'				=> $comment_id,
			'bt_article_id'		=> $article_id,
			'bt_content'		=> markup(htmlspecialchars(clean_txt($_POST['commentaire']), ENT_NOQUOTES)),
			'bt_wiki_content'	=> clean_txt($_POST['commentaire']),
			'bt_author'			=> protect($_POST['auteur']),
			'bt_email'			=> protect($_POST['email']),
			'bt_link'			=> '', // this is empty, 'cause bt_link is created on reading of DB, not written in DB (useful if we change server or site name some day).
			'bt_webpage'		=> $url,
			'bt_subscribe'		=> (isset($_POST['subscribe']) and $_POST['subscribe'] == 'on') ? '1' : '0',
			'bt_statut'			=> $status,
		);
	}
	if ( isset($_POST['ID']) and !empty($_POST['ID']) and is_numeric($_POST['ID']) ) { // ID only added on edit.
		$comment['ID'] = $_POST['ID'];
	}

	return $comment;
}

// POST LINK
function init_post_link2() { // second init : the whole link data needs to be stored
	$id = protect($_POST['bt_id']);
	$link = array (
		'bt_id'				=> $id,
		'bt_type'			=> htmlspecialchars($_POST['type']),
		'bt_content'		=> markup(htmlspecialchars(clean_txt($_POST['description']), ENT_NOQUOTES)),
		'bt_wiki_content'	=> protect($_POST['description']),
		'bt_title'			=> protect($_POST['title']),
		'bt_link'			=> (empty($_POST['url'])) ? $GLOBALS['racine'].'?mode=links&amp;id='.$id : protect($_POST['url']),
		'bt_tags'			=> htmlspecialchars(traiter_tags($_POST['categories'])),
		'bt_statut'			=> (isset($_POST['statut'])) ? 0 : 1
	);
	if ( isset($_POST['ID']) and is_numeric($_POST['ID']) ) { // ID only added on edit.
		$link['ID'] = $_POST['ID'];
	}

	return $link;
}

// once form is initiated, and no errors are found, treat it (save it to DB).
function traiter_form_billet($billet) {
	if ( isset($_POST['enregistrer']) and !isset($billet['ID']) ) {
		$result = bdd_article($billet, 'enregistrer-nouveau');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?post_id='.$billet['bt_id'].'&msg=confirm_article_maj';
	}
	elseif ( isset($_POST['enregistrer']) and isset($billet['ID']) ) {
		$result = bdd_article($billet, 'modifier-existant');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?post_id='.$billet['bt_id'].'&msg=confirm_article_ajout';
	}
	elseif ( isset($_POST['supprimer']) and isset($_POST['ID']) and is_numeric($_POST['ID']) ) {
		$result = bdd_article($billet, 'supprimer-existant');
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM commentaires WHERE bt_article_id=?');
			$req->execute(array($_POST['article_id']));
		} catch (Exception $e) {
			die('Erreur Suppr Comm associés: '.$e->getMessage());
		}

		$redir = 'articles.php?msg=confirm_article_suppr';
	}
	if ($result === TRUE) {
		rafraichir_cache_lv1();
		redirection($redir);
	}
	else { die($result); }
}




function bdd_article($billet, $what) {
	// l'article n'existe pas, on le crée
	if ( $what == 'enregistrer-nouveau' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO articles
				(	bt_type,
					bt_id,
					bt_date,
					bt_title,
					bt_abstract,
					bt_link,
					bt_notes,
					bt_content,
					bt_wiki_content,
					bt_tags,
					bt_keywords,
					bt_allow_comments,
					bt_nb_comments,
					bt_statut
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				'article',
				$billet['bt_id'],
				$billet['bt_date'],
				$billet['bt_title'],
				$billet['bt_abstract'],
				$billet['bt_link'],
				$billet['bt_notes'],
				$billet['bt_content'],
				$billet['bt_wiki_content'],
				$billet['bt_tags'],
				$billet['bt_keywords'],
				$billet['bt_allow_comments'],
				0,
				$billet['bt_statut']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur ajout article: '.$e->getMessage();
		}
	// l'article existe, et il faut le mettre à jour alors.
	} elseif ( $what == 'modifier-existant' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE articles SET
				bt_date=?,
				bt_title=?,
				bt_link=?,
				bt_abstract=?,
				bt_notes=?,
				bt_content=?,
				bt_wiki_content=?,
				bt_tags=?,
				bt_keywords=?,
				bt_allow_comments=?,
				bt_statut=?
				WHERE ID=?');
			$req->execute(array(
					$billet['bt_date'],
					$billet['bt_title'],
					$billet['bt_link'],
					$billet['bt_abstract'],
					$billet['bt_notes'],
					$billet['bt_content'],
					$billet['bt_wiki_content'],
					$billet['bt_tags'],
					$billet['bt_keywords'],
					$billet['bt_allow_comments'],
					$billet['bt_statut'],
					$_POST['ID']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur mise à jour de l’article: '.$e->getMessage();
		}
	// Suppression d'un article
	} elseif ( $what == 'supprimer-existant' ) {
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM articles WHERE ID=?');
			$req->execute(array($_POST['ID']));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 123456 : '.$e->getMessage();
		}
	}
}



// traiter un ajout de lien prend deux étapes :
//  1) on donne le lien > il donne un form avec lien+titre
//  2) après ajout d'une description, on clic pour l'ajouter à la bdd.
// une fois le lien donné (étape 1) et les champs renseignés (étape 2) on traite dans la BDD
function traiter_form_link($link) {
	$query_string = str_replace(((isset($_GET['msg'])) ? '&msg='.$_GET['msg'] : ''), '', $_SERVER['QUERY_STRING']);
	if ( isset($_POST['enregistrer'])) {
		$result = bdd_lien($link, 'enregistrer-nouveau');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?msg=confirm_link_ajout';
	}

	elseif (isset($_POST['editer'])) {
		$result = bdd_lien($link, 'modifier-existant');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?msg=confirm_link_edit';
	}

	elseif ( isset($_POST['supprimer'])) {
		$result = bdd_lien($link, 'supprimer-existant');
		$redir = basename($_SERVER['SCRIPT_NAME']).'?msg=confirm_link_suppr';
	}

	if ($result === TRUE) {
		rafraichir_cache_lv1();
		redirection($redir);
	} else { die($result); }

}


function bdd_lien($link, $what) {
	if ($what == 'enregistrer-nouveau') {
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO links
			(	bt_type,
				bt_id,
				bt_content,
				bt_wiki_content,
				bt_title,
				bt_link,
				bt_tags,
				bt_statut
			)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				$link['bt_type'],
				$link['bt_id'],
				$link['bt_content'],
				$link['bt_wiki_content'],
				$link['bt_title'],
				$link['bt_link'],
				$link['bt_tags'],
				$link['bt_statut']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 5867 : '.$e->getMessage();
		}

	} elseif ($what == 'modifier-existant') {
		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE links SET
				bt_content=?,
				bt_wiki_content=?,
				bt_title=?,
				bt_link=?,
				bt_tags=?,
				bt_statut=?
				WHERE ID=?');
			$req->execute(array(
				$link['bt_content'],
				$link['bt_wiki_content'],
				$link['bt_title'],
				$link['bt_link'],
				$link['bt_tags'],
				$link['bt_statut'],
				$link['ID']
			));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 435678 : '.$e->getMessage();
		}
	}

	elseif ($what == 'supprimer-existant') {
		try {
			$req = $GLOBALS['db_handle']->prepare('DELETE FROM links WHERE ID=?');
			$req->execute(array($link['ID']));
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 97652 : '.$e->getMessage();
		}
	}
}

// Called when a new comment is posted (public side or admin side) or on edit/activating/removing
//  when adding, redirects with message after processing
//  when edit/activating/removing, dies with message after processing (message is then caught with AJAX)

function traiter_form_commentaire($commentaire, $admin) {
	$msg_param_to_trim = (isset($_GET['msg'])) ? '&msg='.$_GET['msg'] : '';
	$query_string = str_replace($msg_param_to_trim, '', $_SERVER['QUERY_STRING']);

	$is_edit = 0;
	if ( $admin == 'admin' and (isset($_POST['com_supprimer']) or isset($_POST['com_activer']) or (isset($_POST['comment_id']) and is_numeric($_POST['comment_id']) )) ) {
		$is_edit = 1;
	}


	// add new comment (admin + public)
	if (isset($_POST['enregistrer']) and !$is_edit) {
		$result = bdd_commentaire($commentaire, 'enregistrer-nouveau');
		if ($result === TRUE) {
			if ($GLOBALS['comm_defaut_status'] == 1) { // send subscribe emails only if comments are not hidden
				send_emails($commentaire['bt_id']);
			}
			if ($admin == 'admin') { $query_string .= '&msg=confirm_comment_ajout'; }
			$redir = basename($_SERVER['SCRIPT_NAME']).'?'.$query_string.'#'.article_anchor($commentaire['bt_id']);
		}
		else { die($result); }
	}

	// admin operations
	elseif ($admin == 'admin') {
		// edit
		if (isset($_POST['enregistrer']) and $is_edit ) {
			$result = bdd_commentaire($commentaire, 'editer-existant');
			$redir = basename($_SERVER['SCRIPT_NAME']).'?'.$query_string.'&msg=confirm_comment_edit';
		}
		// comm.supp() OR comm.changeStatus() (ajax)
		elseif (isset($_POST['com_supprimer']) or isset($_POST['com_activer']) ) {
			$bt_id = (isset($_POST['com_supprimer']) ? htmlspecialchars($_POST['com_supprimer']) : htmlspecialchars($_POST['com_activer']));
			$action = (isset($_POST['com_supprimer']) ? 'supprimer-existant' : 'activer-existant');
				$comm = array('bt_id' => $bt_id);
				$result = bdd_commentaire($comm, $action);
				// Ajax response
				if ($result === TRUE) {
					if (isset($_POST['com_activer']) and $GLOBALS['comm_defaut_status'] == 0) { // send subscribe emails if comments just got activated
						send_emails(htmlspecialchars($_POST['com_activer']));
					}
					rafraichir_cache_lv1();
					echo 'Success'.new_token();
				}
				else { echo 'Error'.new_token(); }
				exit;
		}
	}

	// do nothing & die (admin + public)
	else {
		redirection(basename($_SERVER['SCRIPT_NAME']).'?'.$query_string.'&msg=nothing_happend_oO');
	}

	if ($result === TRUE) {
		rafraichir_cache_lv1();
		redirection($redir);
	}
	else { die($result); }
}

function bdd_commentaire($commentaire, $what) {

	// ENREGISTREMENT D'UN NOUVEAU COMMENTAIRE.
	if ($what == 'enregistrer-nouveau') {
		$article_id = $commentaire['bt_article_id'];
		try {
			$req = $GLOBALS['db_handle']->prepare('INSERT INTO commentaires
				(	bt_type,
					bt_id,
					bt_article_id,
					bt_content,
					bt_wiki_content,
					bt_author,
					bt_link,
					bt_webpage,
					bt_email,
					bt_subscribe,
					bt_statut
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
			$req->execute(array(
				'comment',
				$commentaire['bt_id'],
				$commentaire['bt_article_id'],
				$commentaire['bt_content'],
				$commentaire['bt_wiki_content'],
				$commentaire['bt_author'],
				$commentaire['bt_link'],
				$commentaire['bt_webpage'],
				$commentaire['bt_email'],
				$commentaire['bt_subscribe'],
				$commentaire['bt_statut']
			));
		} catch (Exception $e) {
			return 'Erreur : '.$e->getMessage();
		}
	}
	// ÉDITION D'UN COMMENTAIRE DÉJÀ EXISTANT. (hors activation)
	elseif ($what == 'editer-existant') {
		$article_id = $commentaire['bt_article_id'];

		try {
			$req = $GLOBALS['db_handle']->prepare('UPDATE commentaires SET
				bt_content=?,
				bt_wiki_content=?,
				bt_author=?,
				bt_link=?,
				bt_webpage=?,
				bt_email=?,
				bt_subscribe=?
				WHERE bt_id=?');
			$req->execute(array(
				$commentaire['bt_content'],
				$commentaire['bt_wiki_content'],
				$commentaire['bt_author'],
				$commentaire['bt_link'],
				$commentaire['bt_webpage'],
				$commentaire['bt_email'],
				$commentaire['bt_subscribe'],
				$commentaire['bt_id'],
			));
		} catch (Exception $e) {
			return 'Erreur : '.$e->getMessage();
		}
	}

	// SUPPRESSION D'UN COMMENTAIRE
	elseif ($what == 'supprimer-existant') {
		try {
			// get article_id
			$req = $GLOBALS['db_handle']->prepare("SELECT bt_article_id FROM commentaires WHERE bt_id=?");
			$req->execute(array($commentaire['bt_id']));
			$result = $req->fetch();
			$article_id = $result['bt_article_id'];

			$req = $GLOBALS['db_handle']->prepare('DELETE FROM commentaires WHERE bt_id=?');
			$req->execute(array($commentaire['bt_id']));

		} catch (Exception $e) {
			return 'Erreur : '.$e->getMessage();
		}
	}

	// CHANGEMENT STATUS COMMENTAIRE
	elseif ($what == 'activer-existant') {
		try {
			// get article_id
			$req = $GLOBALS['db_handle']->prepare("SELECT bt_article_id FROM commentaires WHERE bt_id=?");
			$req->execute(array($commentaire['bt_id']));
			$result = $req->fetch();
			$article_id = $result['bt_article_id'];

			$req = $GLOBALS['db_handle']->prepare('UPDATE commentaires SET bt_statut=ABS(bt_statut-1) WHERE bt_id=?');
			$req->execute(array($commentaire['bt_id']));

		} catch (Exception $e) {
			return 'Erreur : '.$e->getMessage();
		}
	}


	// After new comm, activate_comm & suppr_comm, update nb_comm in articles.
	try {
		// remet à jour le nombre de commentaires associés à l’article.
		$nb_comments_art = liste_elements_count("SELECT count(*) AS nbr FROM commentaires WHERE bt_article_id=? and bt_statut=1", array($article_id));
		$req2 = $GLOBALS['db_handle']->prepare('UPDATE articles SET bt_nb_comments=? WHERE bt_id=?');
		$req2->execute( array($nb_comments_art, $article_id) );

		return TRUE;
	} catch (Exception $e) {
		return 'Erreur 248959 : mise à jour nb_comm() 248959 : '.$e->getMessage();
	}
}

/* FOR COMMENTS : RETUNS nb_com per author */
function nb_entries_as($table, $what) {
	$result = array();
	$query = "SELECT count($what) AS nb, $what FROM $table GROUP BY $what ORDER BY nb DESC";
	try {
		$result = $GLOBALS['db_handle']->query($query)->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	} catch (Exception $e) {
		die('Erreur 0349 : '.$e->getMessage());
	}
}


function list_all_tags($table, $statut) {
	try {
		if ($statut !== FALSE) {
			$res = $GLOBALS['db_handle']->query("SELECT bt_tags FROM $table WHERE bt_statut = $statut");
		} else {
			$res = $GLOBALS['db_handle']->query("SELECT bt_tags FROM $table");
		}
		$liste_tags = '';
		// met tous les tags de tous les articles bout à bout
		while ($entry = $res->fetch()) {
			if (trim($entry['bt_tags']) != '') {
				$liste_tags .= $entry['bt_tags'].',';
			}
		}
		$res->closeCursor();
		$liste_tags = rtrim($liste_tags, ',');
	} catch (Exception $e) {
		die('Erreur 4354768 : '.$e->getMessage());
	}

	$liste_tags = str_replace(array(', ', ' ,'), ',', $liste_tags);
	$tab_tags = explode(',', $liste_tags);
	sort($tab_tags);
	unset($tab_tags['']);
	return array_count_values($tab_tags);
}

/* Lists folders from images */
function list_image_folders() {
	try {
		$res = $GLOBALS['db_handle']->query("SELECT bt_folder FROM images WHERE bt_type='image'");
		$list_folders = '';
		// put folders in one string
		while ($entry = $res->fetch()) { $list_folders .= $entry['bt_folder'].','; }
		$res->closeCursor();
		$list_folders = rtrim($list_folders, ',');
	} catch (Exception $e) {
		die('Erreur 4354768 : '.$e->getMessage());
	}

	$tab_folders = explode(',', str_replace(array(', ', ' ,'), ',', $list_folders));
	$tab_folders = array_count_values($tab_folders);
	unset($tab_folders['']);
	return $tab_folders;
}


/* Enregistre le flux dans une BDD.
   $flux est un Array avec les données dedans.
	$flux ne contient que les entrées qui doivent être enregistrées
	 (la recherche de doublons est fait en amont)
*/
function bdd_rss($flux, $what) {
	if ($what == 'enregistrer-nouveau') {
		try {
			$GLOBALS['db_handle']->beginTransaction();
			foreach ($flux as $post) {
				$req = $GLOBALS['db_handle']->prepare('INSERT INTO rss
				(  bt_id,
					bt_date,
					bt_title,
					bt_link,
					bt_feed,
					bt_content,
					bt_statut,
					bt_bookmarked,
					bt_folder
				)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
				$req->execute(array(
					$post['bt_id'],
					$post['bt_date'],
					$post['bt_title'],
					$post['bt_link'],
					$post['bt_feed'],
					$post['bt_content'],
					$post['bt_statut'],
					$post['bt_bookmarked'],
					$post['bt_folder']
				));
			}
			$GLOBALS['db_handle']->commit();
			return TRUE;
		} catch (Exception $e) {
			return 'Erreur 5867-rss-add-sql : '.$e->getMessage();
		}
	}
}

/* FOR RSS : RETUNS list of GUID in whole DB */
function rss_list_guid() {
	$result = array();
	$query = "SELECT bt_id FROM rss";
	try {
		$result = $GLOBALS['db_handle']->query($query)->fetchAll(PDO::FETCH_COLUMN, 0);
		return $result;
	} catch (Exception $e) {
		die('Erreur 0329-rss-get_guid : '.$e->getMessage());
	}
}

/* FOR RSS : RETUNS nb of articles per feed */
function rss_count_feed() {
	$result = array();
	$query = "SELECT bt_feed, SUM(bt_statut) AS nbrun, SUM(bt_bookmarked) AS nbfav, SUM(CASE WHEN bt_date >= 20180527000000 AND bt_statut = 1 THEN 1 ELSE 0 END) AS nbtoday FROM rss GROUP BY bt_feed";

	//$query = "SELECT bt_feed, SUM(bt_statut) AS nbrun, SUM(bt_bookmarked) AS nbfav FROM rss GROUP BY bt_feed";
	try {
		$result = $GLOBALS['db_handle']->query($query)->fetchAll(PDO::FETCH_ASSOC);
		return $result;
	} catch (Exception $e) {
		die('Erreur 0329-rss-count_per_feed : '.$e->getMessage());
	}
}

/* FOR RSS : get $_POST and update feeds (title, url…) for feeds.php?config */
function traiter_form_rssconf() {
	$msg_param_to_trim = (isset($_GET['msg'])) ? '&msg='.$_GET['msg'] : '';
	$query_string = str_replace($msg_param_to_trim, '', $_SERVER['QUERY_STRING']);
	// traitement
	$GLOBALS['db_handle']->beginTransaction();
	foreach($GLOBALS['liste_flux'] as $i => $feed) {
		if (isset($_POST['i_'.$feed['checksum']])) {
			// feed marked to be removed
			if ($_POST['k_'.$feed['checksum']] == 0) {
				unset($GLOBALS['liste_flux'][$i]);
				try {
					$req = $GLOBALS['db_handle']->prepare('DELETE FROM rss WHERE bt_feed=?');
					$req->execute(array($feed['link']));
				} catch (Exception $e) {
					die('Error : Rss?conf RM-from db: '.$e->getMessage());
				}
			}
			// title, url or folders have changed
			else {
				// title has change
				$GLOBALS['liste_flux'][$i]['title'] = $_POST['i_'.$feed['checksum']];
				// folder has changed : update & change folder where it must be changed
				if ($GLOBALS['liste_flux'][$i]['folder'] != $_POST['l_'.$feed['checksum']]) {
					$GLOBALS['liste_flux'][$i]['folder'] = $_POST['l_'.$feed['checksum']];
					try {
						$req = $GLOBALS['db_handle']->prepare('UPDATE rss SET bt_folder=? WHERE bt_feed=?');
						$req->execute(array($_POST['l_'.$feed['checksum']], $feed['link']));
					} catch (Exception $e) {
						die('Error : Rss?conf Update-feed db: '.$e->getMessage());
					}
				}

				// URL has change
				if ($_POST['j_'.$feed['checksum']] != $GLOBALS['liste_flux'][$i]['link']) {
					$a = $GLOBALS['liste_flux'][$i];
					$a['link'] = $_POST['j_'.$feed['checksum']];
					unset($GLOBALS['liste_flux'][$i]);
					$GLOBALS['liste_flux'][$a['link']] = $a;
					try {
						$req = $GLOBALS['db_handle']->prepare('UPDATE rss SET bt_feed=? WHERE bt_feed=?');
						$req->execute(array($_POST['j_'.$feed['checksum']], $feed['link']));
					} catch (Exception $e) {
						die('Error : Rss?conf Update-feed db: '.$e->getMessage());
					}
				}
			}
		}
	}
	$GLOBALS['db_handle']->commit();

	// sort list with title
	$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');

	$redir = basename($_SERVER['SCRIPT_NAME']).'?'.$query_string.'&msg=confirm_feeds_edit';
	redirection($redir);

}


