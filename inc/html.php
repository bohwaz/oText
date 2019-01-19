<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***


function afficher_html_head($titre, $page_css_class) {
	$html = '<!DOCTYPE html>'."\n";
	$html .= '<html lang="'.$GLOBALS['lang']['id'].'">'."\n";
	$html .= '<head>'."\n";
	$html .= "\t".'<meta charset="UTF-8" />'."\n";
	$html .= "\t".'<title>'.$titre.' | '.BLOGOTEXT_NAME.'</title>'."\n";
	$html .= "\t".'<meta name="viewport" content="initial-scale=1.0, user-scalable=yes" />'."\n";
	$html .= "\t".'<link type="text/css" rel="stylesheet" href="style/styles/style.css.php" />'."\n";
	$html .= '</head>'."\n";
	$html .= '<body id="body" class="'.$page_css_class.'">'."\n";
	echo $html;
}

function footer($begin_time='') {
	$msg = '';
	if ($begin_time != '') {
		$dt = round((microtime(TRUE) - $begin_time),6);
		$msg = ' - '.$GLOBALS['lang']['rendered'].' '.$dt.' s '.$GLOBALS['lang']['using'].' '.DBMS;
	}

	$html = '</div>'."\n";
	$html .= '</div>'."\n";
	$html .= '<p id="footer"><a href="'.BLOGOTEXT_SITE.'">'.BLOGOTEXT_NAME.' '.BLOGOTEXT_VERSION.'</a>'.$msg.'</p>'."\n";
	$html .= '</body>'."\n";
	$html .= '</html>';
	echo $html;
}

/// menu haut panneau admin /////////
function afficher_topnav($titre, $html_sub_menu) {
	$tab = pathinfo($_SERVER['SCRIPT_NAME'], PATHINFO_BASENAME);
	if (strlen($titre) == 0) $titre = BLOGOTEXT_NAME;

	$html = '<header id="header">'."\n";

	$html .= "\t".'<div id="top">'."\n";


	// page title
	$html .=  "\t\t".'<h1 id="titre-page"><a href="'.$tab.'">'.$titre.'</a></h1>'."\n";

	// search form
	if (in_array($tab, array('articles.php', 'commentaires.php', 'fichiers.php', 'links.php', 'notes.php', 'feed.php', 'agenda.php', 'contacts.php'))) {
		$html .= moteur_recherche();
	}

	// app navs
	$html .= "\t\t".'<div id="nav">'."\n";
	$html .= "\t\t\t".'<ul>'."\n";
	$html .= "\t\t\t\t".'<li><a href="index.php" id="lien-index">'.$GLOBALS['lang']['label_resume'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="articles.php" id="lien-liste">'.$GLOBALS['lang']['mesarticles'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="ecrire.php" id="lien-nouveau">'.$GLOBALS['lang']['nouveau'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="commentaires.php" id="lien-lscom">'.$GLOBALS['lang']['titre_commentaires'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="fichiers.php" id="lien-fichiers">'.ucfirst($GLOBALS['lang']['label_fichiers']).'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="links.php" id="lien-links">'.ucfirst($GLOBALS['lang']['label_links']).'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="notes.php" id="lien-notes">'.ucfirst($GLOBALS['lang']['label_notes']).'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="feed.php" id="lien-rss">'.ucfirst($GLOBALS['lang']['label_feeds']).'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="agenda.php" id="lien-agenda">'.ucfirst($GLOBALS['lang']['label_agenda']).'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="contacts.php" id="lien-contacts">'.ucfirst($GLOBALS['lang']['label_contacts']).'</a></li>'."\n";
	$html .= "\t\t\t".'</ul>'."\n";
	$html .= "\t\t".'</div>'."\n";

	// notif icons
	$html .= get_notifications();

	// account nav
	$html .= "\t\t".'<div id="nav-acc">'."\n";
	$html .= "\t\t\t".'<ul>'."\n";
	$html .= "\t\t\t\t".'<li><a href="preferences.php" id="lien-preferences">'.$GLOBALS['lang']['preferences'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="'.$GLOBALS['racine'].'" id="lien-site">'.$GLOBALS['lang']['lien_blog'].'</a></li>'."\n";
	$html .= "\t\t\t\t".'<li><a href="logout.php" id="lien-deconnexion">'.$GLOBALS['lang']['deconnexion'].'</a></li>'."\n";
	$html .= "\t\t\t".'</ul>'."\n";
	$html .= "\t\t".'</div>'."\n";


	$html .= "\t".'</div>'."\n";

	// Sub-menu-bar (for RSS, notes, agenda…)
	$html .= $html_sub_menu;

	// Popup node
	if (isset($_GET['msg']) and array_key_exists($_GET['msg'], $GLOBALS['lang']) ) {
		$message = $GLOBALS['lang'][$_GET['msg']];
		$message .= (isset($_GET['nbnew'])) ? htmlspecialchars($_GET['nbnew']).' '.$GLOBALS['lang']['rss_nouveau_flux'] : ''; // nb new RSS
		$html .= '<div class="confirmation">'.$message.'</div>'."\n";

	} elseif (isset($_GET['errmsg']) and array_key_exists($_GET['errmsg'], $GLOBALS['lang'])) {
		$message = $GLOBALS['lang'][$_GET['errmsg']];
		$html .= '<div class="no_confirmation">'.$message.'</div>'."\n";
	}

	$html .= '</header>'."\n";

	echo $html;
}



function get_notifications() {
	$html = '';
	$lis = '';
	$hasNotifs = 0;

	// get last RSS posts
	if (isset($_COOKIE['lastAccessRss']) and is_numeric($_COOKIE['lastAccessRss'])) {
		$query = 'SELECT count(ID) AS nbr FROM rss WHERE bt_date >=?';
		$array = array(date('YmdHis', $_COOKIE['lastAccessRss']));
		$nb_new = liste_elements_count($query, $array);
		if ($nb_new > 0) {
			$hasNotifs += $nb_new;
			$lis .= "\t\t\t".'<li><a href="feed.php">'.$nb_new .' new RSS entries</a></li>'."\n";
		}
	}

	// get last Comments
	if (isset($_COOKIE['lastAccessComments']) and is_numeric($_COOKIE['lastAccessComments'])) {
		$query = 'SELECT count(ID) AS nbr FROM commentaires WHERE bt_id >=?';
		$array = array(date('YmdHis', $_COOKIE['lastAccessComments']));
		$nb_new = liste_elements_count($query, $array);
		if ($nb_new > 0) {
			$hasNotifs += $nb_new;
			$lis .= "\t\t\t".'<li><a href="commentaires.php">'.$nb_new .' new comments</a></li>'."\n";
		}
	}

	// get near events
	//if (isset($_COOKIE['lastAccessAgenda']) and is_numeric($_COOKIE['lastAccessAgenda'])) {
	$query = 'SELECT count(ID) AS nbr FROM agenda WHERE bt_date >=? AND bt_date <=?';
	$array = array( date('YmdHis', time()), date('YmdHis', (time()+24*60*60)) );
	$nb_new = liste_elements_count($query, $array);
	if ($nb_new > 0) {
		$hasNotifs += $nb_new;
		$lis .= "\t\t\t\t".'<li><a href="agenda.php">'.$nb_new .' near events</a></li>'."\n";
	}
//}

	$html .= "\t\t".'<div id="notif-icon" data-nb-notifs="'.$hasNotifs.'">'."\n";
	$html .= "\t\t\t".'<ul>'."\n";

	$lis .= ($lis) ? '' : "\t\t\t\t".'<li>'.$GLOBALS['lang']['note_no_notifs'].'</li>'."\n";

	$html .= $lis;

	$html .= "\t\t\t".'</ul>'."\n";
	$html .= "\t\t".'</div>'."\n";

	return $html;
}


function erreurs($erreurs) {
	$html = '';
	if ($erreurs) {
		$html .= '<div id="erreurs">'.'<strong>'.$GLOBALS['lang']['erreurs'].'</strong> :' ;
		$html .= '<ul><li>';
		$html .= implode('</li><li>', $erreurs);
		$html .= '</li></ul></div>'."\n";
	}
	return $html;
}


function moteur_recherche() {
	$requete='';
	if (isset($_GET['q'])) {
		$requete = htmlspecialchars(stripslashes($_GET['q']));
	}
	$return  = "\t\t".'<form action="?" method="get" id="search">'."\n";
	$return .= "\t\t\t".'<input id="q" name="q" type="search" size="20" value="'.$requete.'" placeholder="'.$GLOBALS['lang']['placeholder_search'].'" accesskey="f" />'."\n";
	$return .= "\t\t\t".'<label id="label_q" for="q">'.$GLOBALS['lang']['rechercher'].'</label>'."\n";
	$return .= "\t\t\t".'<button id="input-rechercher" type="submit">'.$GLOBALS['lang']['rechercher'].'</button>'."\n";
	if (isset($_GET['mode']))
	$return .= "\t\t\t".'<input id="mode" name="mode" type="hidden" value="'.htmlspecialchars(stripslashes($_GET['mode'])).'"/>'."\n";
	$return .= "\t\t".'</form>'."\n";
	return $return;
}

function encart_commentaires() {
	$query = "SELECT a.bt_title, a.bt_link AS bt_art_link, c.bt_author, c.bt_id, c.bt_article_id, c.bt_content, c.bt_link FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_statut=1 AND a.bt_statut=1 ORDER BY c.bt_id DESC LIMIT 5";
	$tableau = liste_elements($query, array(), 'commentaires');
	if (isset($tableau)) {
		$listeLastComments = '<ul class="encart_lastcom">'."\n";
		foreach ($tableau as $i => $comment) {
			$comment['contenu_abbr'] = strip_tags($comment['bt_content']);
			// limits length of comment abbreviation and name
			if (strlen($comment['contenu_abbr']) >= 60) {
				$comment['contenu_abbr'] = mb_substr($comment['contenu_abbr'], 0, 59).'…';
			}
			if (strlen($comment['bt_author']) >= 30) {
				$comment['bt_author'] = mb_substr($comment['bt_author'], 0, 29).'…';
			}
			$comm_link = URL_ROOT . $comment['bt_art_link'] . $comment['bt_link'];
			$listeLastComments .= '<li title="'.date_formate($comment['bt_id']).'"><strong>'.$comment['bt_author'].' : </strong><a href="'.$comm_link.'">'.$comment['contenu_abbr'].'</a>'.'</li>'."\n";
		}
		$listeLastComments .= '</ul>'."\n";
		return $listeLastComments;
	} else {
		return $GLOBALS['lang']['no_comments'];
	}
}

function encart_categories($mode) {
	if ($GLOBALS['activer_categories'] == '1') {
		$where = ($mode == 'links') ? 'links' : 'articles';
		$ampmode = ($mode == 'links') ? '&amp;mode=links' : '';

		$liste = list_all_tags($where, '1');

		// attach non-diacritic versions of tag, so that "é" does not pass after "z" and re-indexes
		foreach ($liste as $tag => $nb) {
			$liste[$tag] = array(diacritique(trim($tag)), $nb);
		}
		// sort tags according non-diacritics versions of tags
		$liste = array_reverse(tri_selon_sous_cle($liste, 0));
		$uliste = '<ul>'."\n";

		// create the <UL> with "tags (nb) "
		foreach($liste as $tag => $nb) {
			if ($tag != '' and $nb[1] > 2) {
				$uliste .= "\t".'<li><a href="?tag='.urlencode(trim($tag)).$ampmode.'" rel="tag">'.ucfirst($tag).' ('.$nb[1].')</a><a href="rss.php?tag='.urlencode($tag).$ampmode.'" rel="alternate"></a></li>'."\n";
			}
		}
		$uliste .= '</ul>'."\n";
		return $uliste;
	}
}

function lien_pagination() {
	if (!isset($GLOBALS['param_pagination']) or isset($_GET['d']) or isset($_GET['liste']) or isset($_GET['id']) ) {
		return '';
	}
	else {
		$nb = $GLOBALS['param_pagination']['nb'];
		$nb_par_page = $GLOBALS['param_pagination']['nb_par_page'];
	}
	$page_courante = (isset($_GET['p']) and is_numeric($_GET['p'])) ? $_GET['p'] : 0;
	$qstring = remove_url_param('p');
//	debug($qstring);
	if ($page_courante <=0) {
		$lien_precede = '';
		$lien_suivant = '<a href="?'.$qstring.'&amp;p=1" rel="next">'.$GLOBALS['lang']['label_suivant'].'</a>';
		if ($nb < $nb_par_page) { // évite de pouvoir aller dans la passé s’il y a moins de 10 posts
			$lien_suivant = '';
		}
	}
	elseif ($nb < $nb_par_page) { // évite de pouvoir aller dans l’infini en arrière dans les pages, nottament pour les robots.
		$lien_precede = '<a href="?'.$qstring.'&amp;p='.($page_courante-1).'" rel="prev">'.$GLOBALS['lang']['label_precedent'].'</a>';
		$lien_suivant = '';
	} else {
		$lien_precede = '<a href="?'.$qstring.'&amp;p='.($page_courante-1).'" rel="prev">'.$GLOBALS['lang']['label_precedent'].'</a>';
		$lien_suivant = '<a href="?'.$qstring.'&amp;p='.($page_courante+1).'" rel="next">'.$GLOBALS['lang']['label_suivant'].'</a>';
	}
	return '<p class="pagination">'.$lien_precede.$lien_suivant.'</p>';
}


function liste_tags($billet, $html_link) {
	$mode = ($billet['bt_type'] == 'article') ? '' : '&amp;mode=links';
	$liste = '';
	if (!empty($billet['bt_tags'])) {
		$tag_list = explode(', ', $billet['bt_tags']);
		// remove diacritics, so that "ééé" does not passe after "zzz" and re-indexes
		foreach ($tag_list as $i => $tag) {
			$tag_list[$i] = array('t' => trim($tag), 'tt' => diacritique(trim($tag)));
		}
		$tag_list = array_reverse(tri_selon_sous_cle($tag_list, 'tt'));

		foreach($tag_list as $tag) {
			$tag = trim($tag['t']);
			if ($html_link == 1) {
				$liste .= '<a href="?tag='.urlencode($tag).$mode.'" rel="tag">'.$tag.'</a>';
			} else {
				$liste .= $tag.' ';
			}
		}
	}
	return $liste;
}



// returns a list of days containing at least one post for a given month
function table_list_date($date, $table) {
	$return = array();
	$column = ($table == 'articles') ? 'bt_date' : 'bt_id';
	$and_date = 'AND '.$column.' <= '.date('YmdHis');

	$query = "SELECT DISTINCT SUBSTR($column, 7, 2) AS date FROM $table WHERE bt_statut = 1 AND $column LIKE '$date%' $and_date";

	try {
		$req = $GLOBALS['db_handle']->query($query);
		while ($row = $req->fetch(PDO::FETCH_ASSOC)) {
			$return[] = $row['date'];
		}
		return $return;
	} catch (Exception $e) {
		die('Erreur 21436 : '.$e->getMessage());
	}
}

// returns dates of the previous and next visible posts
function prev_next_posts($year, $month, $table) {
	$column = ($table == 'articles') ? 'bt_date' : 'bt_id';
	$and_date = 'AND '.$column.' <= '.date('YmdHis');

	$date = new DateTime();
	$date->setDate($year, $month, 1)->setTime(0, 0, 0);
	$date_min = $date->format('YmdHis');
	$date->modify('+1 month');
	$date_max = $date->format('YmdHis');

	$query = "SELECT
		(SELECT SUBSTR($column, 0, 7) FROM $table WHERE bt_statut = 1 AND $column < $date_min ORDER BY $column DESC LIMIT 1),
		(SELECT SUBSTR($column, 0, 7) FROM $table WHERE bt_statut = 1 AND $column > $date_max $and_date ORDER BY $column ASC LIMIT 1)";

	try {
		$req = $GLOBALS['db_handle']->query($query);
		return array_values($req->fetch(PDO::FETCH_ASSOC));
	} catch (Exception $e) {
		die('Erreur 21436 : '.$e->getMessage());
	}
}

// returns HTML <table> calendar
function html_calendrier() {
	// article
	if ( isset($_GET['d']) and preg_match('#^\d{4}(/\d{2}){5}#', $_GET['d'])) {
		$id = substr(str_replace('/', '', $_GET['d']), 0, 14);
		$date = substr(get_entry('articles', 'bt_date', $id, 'return'), 0, 8);
		$date = ($date <= date('Ymd')) ? $date : date('Ym');
	} elseif ( isset($_GET['d']) and preg_match('#^\d{4}/\d{2}(/\d{2})?#', $_GET['d']) ) {
		$date = str_replace('/', '', $_GET['d']);
		$date = (preg_match('#^\d{6}\d{2}#', $date)) ? substr($date, 0, 8) : substr($date, 0, 6); // avec jour ?
	} elseif (isset($_GET['id']) and preg_match('#^\d{14}#', $_GET['id']) ) {
		$date = substr($_GET['id'], 0, 8);
	} else {
		$date = date('Ym');
	}

	$annee = (int)substr($date, 0, 4);
	$ce_mois = substr($date, 4, 2);
	$ce_jour = (strlen(substr($date, 6, 2)) == 2) ? substr($date, 6, 2) : '';

	$qstring = (isset($_GET['mode']) and !empty($_GET['mode'])) ? 'mode='.htmlspecialchars($_GET['mode']).'&amp;' : '';

	$premier_jour = mktime(0, 0, 0, $ce_mois, 1, $annee);
	$jours_dans_mois = date('t', $premier_jour);
	$decalage_jour = date('w', $premier_jour-1);

	// On verifie si il y a un ou des articles/liens/commentaire du jour dans le mois courant
	$tableau = array();
	$mode = ( !empty($_GET['mode']) ) ? $_GET['mode'] : 'blog';
	switch($mode) {
		case 'comments':
			$where = 'commentaires'; break;
		case 'links':
			$where = 'links'; break;
		case 'blog':
		default:
			$where = 'articles'; break;
	}

	// On cherche les dates des articles précédent et suivant
	list($previous_post, $next_post) = prev_next_posts($annee, $ce_mois, $where);
	$prev_mois = '?'.$qstring.'d='.substr($previous_post, 0, 4).'/'.substr($previous_post, 4, 2);
	$next_mois = '?'.$qstring.'d='.substr($next_post, 0, 4).'/'.substr($next_post, 4, 2);

	$tableau = table_list_date($annee.$ce_mois, $where);

	$html = '<table id="calendrier">'."\n";
	$html .= '<caption>';
	if ($previous_post !== null) {
		$html .= '<a href="'.$prev_mois.'">&#171;</a>&nbsp;';
	}

	// Si on affiche un jour on ajoute le lien sur le mois
	$html .= '<a href="?'.$qstring.'d='.$annee.'/'.$ce_mois.'">'.mois_en_lettres($ce_mois).' '.$annee.'</a>';
	// On ne peut pas aller dans le futur
	if ($next_post !== null) {
		$html .= '&nbsp;<a href="'.$next_mois.'">&#187;</a>';
	}
	$html .= '</caption>'."\n".'<tr>'."\n";
	if ($decalage_jour > 0) {
		for ($i = 0; $i < $decalage_jour; $i++) {
			$html .=  '<td></td>';
		}
	}
	// Indique le jour consulte
	for ($jour = 1; $jour <= $jours_dans_mois; $jour++) {
		if ($jour == $ce_jour) {
			$class = ' class="active"';
		} else {
			$class = '';
		}
		if ( in_array($jour, $tableau) ) {
			$lien = '<a href="?'.$qstring.'d='.$annee.'/'.$ce_mois.'/'.str2($jour).'">'.$jour.'</a>';
		} else {
			$lien = $jour;
		}
		$html .= '<td'.$class.'>';
		$html .= $lien;
		$html .= '</td>';
		$decalage_jour++;
		if ($decalage_jour == 7) {
			$decalage_jour = 0;
			$html .=  '</tr>';
			if ($jour < $jours_dans_mois) {
				$html .= '<tr>';
			}
		}
	}
	if ($decalage_jour > 0) {
		for ($i = $decalage_jour; $i < 7; $i++) {
			$html .= '<td> </td>';
		}
		$html .= '</tr>'."\n";
	}
	$html .= '</table>'."\n";
	return $html;

}


// returns HTML <table> calender
function html_readmore() {
	$nb_art = 4;
	// lists IDs
	try {
		$result = $GLOBALS['db_handle']->query("SELECT ID FROM articles WHERE bt_statut=1 AND bt_date <= ".date('YmdHis'))->fetchAll(PDO::FETCH_ASSOC);
	} catch (Exception $e) {
		die('Erreur rand addon_readmore(): '.$e->getMessage());
	}

	// clean array
	foreach ($result as $i => $art) {
		$result[$i] = $art['ID'];
	}
	// randomize array
	shuffle($result);

	// select nth entries (PHP does take care about "nb_arts > count($result)")
	$art = array_slice($result, 0, $nb_art);

	// get articles
	try {
		$array_qmark = str_pad('', count($art)*3-2, "?, ");
		$query = "SELECT bt_title, bt_id, bt_content FROM articles WHERE bt_statut=1 AND bt_date <= ".date('YmdHis')." AND ID IN (".$array_qmark.")";
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($art);
		$articles = $req->fetchAll(PDO::FETCH_ASSOC);

	} catch (Exception $e) {
		die('Erreur fetch content addon_readmore(): '.$e->getMessage());
	}

	foreach ($articles as $i => $article) {
		// extract image from $article[bt_content]
		preg_match('<img *.* src=(["|\']?)(([^\1 ])*)(\1).*>', $article['bt_content'], $matches);
		$articles[$i]['bt_img'] = '';
		if (!empty($matches)) {
			$articles[$i]['bt_img'] = chemin_thb_img_test($matches[2]);
		}
		unset($articles[$i]['bt_content']);
		// generates link
		//$articles[$i]['bt_link'] = get_blogpath($article['bt_id'], $article['bt_title']);
	}

	// generates the UL/LI list.
	$html = '<ul>'."\n";
	foreach ($articles as $art) {
		$html .= "\t".'<li style="background-image: url('.$art['bt_img'].');"><a href="'.URL_ROOT.$art['bt_link'].'">'.$art['bt_title'].'</a></li>'."\n";
	}
	$html .= '</ul>'."\n";

	return $html;


}


// returns the first image of an article
/* @return : path of image
*/
function html_get_image_from_article($article) {
	// extract image from $article
	preg_match('#<img *.* src=(["|\']?)([^\1 ]*)(\1)[^>]*>#', $article, $matches);
	$img = array('<img src="favicon.ico" alt="default_favicon" />', '', 'favicon.ico', '');
	if (!empty($matches)) {
		$img = $matches;
	}
	return $img;

}


function php_lang_to_js() {
	$frontend_str = array();
	$frontend_str['maxFilesSize'] = min(return_bytes(ini_get('upload_max_filesize')), return_bytes(ini_get('post_max_size')));
	$frontend_str['rssJsAlertNewLink'] = $GLOBALS['lang']['rss_jsalert_new_link'];
	$frontend_str['rssJsAlertNewLinkFolder'] = $GLOBALS['lang']['rss_jsalert_new_link_folder'];
	$frontend_str['confirmFeedClean'] = $GLOBALS['lang']['confirm_feed_clean'];
	$frontend_str['confirmFeedSaved'] = $GLOBALS['lang']['confirm_feeds_edit'];
	$frontend_str['confirmCommentSuppr'] = $GLOBALS['lang']['confirm_comment_suppr'];
	$frontend_str['confirmNotesSaved'] = $GLOBALS['lang']['confirm_note_enregistree'];
	$frontend_str['confirmContactsSaved'] = $GLOBALS['lang']['confirm_contacts_saved'];
	$frontend_str['confirmEventsSaved'] = $GLOBALS['lang']['confirm_agenda_updated'];
	$frontend_str['activer'] = $GLOBALS['lang']['activer'];
	$frontend_str['desactiver'] = $GLOBALS['lang']['desactiver'];
	$frontend_str['supprimer'] = $GLOBALS['lang']['supprimer'];
	//$frontend_str['save'] = $GLOBALS['lang']['enregistrer'];
	//$frontend_str['add_title'] = $GLOBALS['lang']['label_add_title'];
	//$frontend_str['add_description'] = $GLOBALS['lang']['label_add_description'];
	//$frontend_str['add_location'] = $GLOBALS['lang']['label_add_location'];
	//$frontend_str['cancel'] = $GLOBALS['lang']['annuler'];
	$frontend_str['errorPhpAjax'] = $GLOBALS['lang']['error_phpajax'];
	$frontend_str['errorCommentSuppr'] = $GLOBALS['lang']['error_comment_suppr'];
	$frontend_str['errorCommentValid'] = $GLOBALS['lang']['error_comment_valid'];
	$frontend_str['questionQuitPage'] = $GLOBALS['lang']['question_quit_page'];
	//$frontend_str['questionCleanRss'] = $GLOBALS['lang']['question_clean_rss'];
	$frontend_str['questionSupprComment'] = $GLOBALS['lang']['question_suppr_comment'];
	$frontend_str['questionSupprArticle'] = $GLOBALS['lang']['question_suppr_article'];
	$frontend_str['questionSupprFichier'] = $GLOBALS['lang']['question_suppr_fichier'];
	$frontend_str['questionSupprFlux'] = $GLOBALS['lang']['question_suppr_feed'];
	$frontend_str['questionSupprNote'] = $GLOBALS['lang']['question_suppr_note'];
	$frontend_str['questionSupprEvent'] = $GLOBALS['lang']['question_suppr_event'];
	$frontend_str['questionSupprContact'] = $GLOBALS['lang']['question_suppr_contact'];
	$frontend_str['notesLabelTitle'] = $GLOBALS['lang']['label_titre'];
	//$frontend_str['notesLabelContent'] = $GLOBALS['lang']['label_contenu'];
	//$frontend_str['createdOn'] = $GLOBALS['lang']['label_creee_le'];
	//$frontend_str['questionPastEvents'] = $GLOBALS['lang']['question_show_past_events'];
	//$frontend_str['entireDay'] = $GLOBALS['lang']['question_entire_day'];

	$sc = json_encode($frontend_str, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
	$sc = '<script id="jsonLang" type="application/json">'."\n".$sc."\n".'</script>'."\n";
	return $sc;
}
