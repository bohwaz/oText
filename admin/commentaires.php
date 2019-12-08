<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

setcookie('lastAccessComments', time(), time()+365*24*60*60, null, null, false, true);


function afficher_commentaire($comment) {
	$html = '';
	$html .= '<div class="commentbloc'.(!$comment['bt_statut'] ? ' privatebloc' : '').'" id="'.article_anchor($comment['bt_id']).'">'."\n";
	$html .= '<div class="comm-side-icon">'."\n";
	$html .= "\t".'<div class="comm-title">'."\n";
	$html .= "\t\t".'<img class="author-icon" src="'.URL_ROOT.'favatar.php?w=gravatar&amp;q='.md5((!empty($comment['bt_email']) ? $comment['bt_email'] : $comment['bt_author'] )).'&amp;s=48&amp;d=monsterid" alt="favatar" />'."\n";
	$html .= "\t\t".'<span class="date">'.date_formate($comment['bt_id']).'<span>'.heure_formate($comment['bt_id']).'</span></span>'."\n" ;
	$html .= "\t\t".'<span class="reply" onclick="reply(\'[b]@['.str_replace('\'', '\\\'', $comment['bt_author']).'|#'.article_anchor($comment['bt_id']).'] :[/b] \'); ">Reply</span> ';
	if (!empty($comment['bt_webpage']))
	$html .= "\t\t".'<span class="webpage"><a href="'.$comment['bt_webpage'].'" title="'.$comment['bt_webpage'].'">'.$comment['bt_webpage'].'</a></span>'."\n";
	if (!empty($comment['bt_email']))
	$html .= "\t\t".'<span class="email"><a href="mailto:'.$comment['bt_email'].'" title="'.$comment['bt_email'].'">'.$comment['bt_email'].'</a></span>'."\n";
	$html .= "\t".'</div>'."\n";
	$html .= '</div>'."\n";
	$html .= '<div class="comm-main-frame">'."\n";
	$html .= "\t".'<div class="comm-header">'."\n";
	$html .= "\t\t".'<div class="comm-title">'."\n";
	$html .= "\t\t\t".'<span class="author"><a href="?filtre=auteur.'.$comment['bt_author'].'" title="'.$GLOBALS['lang']['label_all_comm_by_author'].'">'.$comment['bt_author'].'</a> :</span>'."\n";
	$html .= "\t\t".'</div>'."\n";
	if (!isset($_GET['post_id']))
	$html .= "\t\t".'<span class="link-article"> '.$GLOBALS['lang']['sur'].' <a href="'.basename($_SERVER['SCRIPT_NAME']).'?post_id='.$comment['bt_article_id'].'">'.$comment['bt_title'].'</a></span>'."\n";
	$html .= "\t\t".'<div class="item-menu-options">'."\n";
	$html .= "\t\t\t".'<ul>'."\n";
	if (isset($_GET['post_id']))
	$html .= "\t\t\t\t".'<li><button type="button" onclick="unfold(this)">'.$GLOBALS['lang']['editer'].'</button></li>'."\n";
	$html .= "\t\t\t\t".'<li><button type="button" onclick="commAction(\'activate\', this)" data-comm-btid="'.$comment['bt_id'].'">'.$GLOBALS['lang'][(!$comment['bt_statut'] ? '' : 'des').'activer'].'</button></li>'."\n";
	$html .= "\t\t\t\t".'<li><button type="button" onclick="commAction(\'delete\', this)" data-comm-btid="'.$comment['bt_id'].'">'.$GLOBALS['lang']['supprimer'].'</button></li>'."\n";
	$html .= "\t\t\t".'</ul>'."\n";
	$html .= "\t\t".'</div>'."\n";
	$html .= "\t".'</div>'."\n";
	$html .= "\t".'<div class="comm-content">'."\n";
	$html .= $comment['bt_content'];
	$html .= "\t".'</div>'."\n";
	$out = '{'.
		'"auth":'.json_encode($comment['bt_author']).', '.
		'"mail":'.json_encode($comment['bt_email']).', '.
		'"webp":'.json_encode($comment['bt_webpage']).', '.
		'"wiki":'.json_encode($comment['bt_wiki_content']).', '.
		'"btid":'.json_encode($comment['bt_id']).
	'}';
	$html .= "\t".'<script id="s'.$comment['bt_id'].'" type="application/json">'.$out.'</script>'."\n";
	$html .= "\t".'</div>'."\n";
	$html .= '</div>'."\n";

	return $html;
}



// TRAITEMENT FORM
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {

	if (isset($_POST['com_supprimer']) or isset($_POST['com_activer'])) {
		$comm_action = (isset($_POST['com_supprimer']) ? $_POST['com_supprimer'] : $_POST['com_activer']);
		$erreurs_form = valider_form_commentaire_ajax($comm_action);
		if (empty($erreurs_form)) {
			traiter_form_commentaire($comm_action, 'admin');
		} else {
			echo implode("\n", $erreurs_form);
			die();
		}
	}
	else {
		$comment = init_post_comment($_GET['post_id'], 'admin');
		$erreurs_form = valider_form_commentaire($comment, 'admin');
		if (empty($erreurs_form)) {
			traiter_form_commentaire($comment, 'admin');
		}
	}
}


// if article ID is given in query string : list comments related to that Article
if ( isset($_GET['post_id']))  {
	$article_id = $_GET['post_id'];

	$query = "SELECT c.*, a.bt_title FROM commentaires AS c, articles AS a WHERE c.bt_article_id=? AND c.bt_article_id=a.bt_id ORDER BY c.bt_id";
	$commentaires = liste_elements($query, array($article_id));

	if (!empty($commentaires)) {
		$article_title = $commentaires[0]['bt_title'];
	} else {
		$article_title = get_entry('articles', 'bt_title', $article_id, 'return');
	}

}

// else, no ID
else {
	if ( !empty($_GET['filtre']) ) {
		// filter for date
		if (preg_match('#^\d{6}(\d{1,8})?$#', ($_GET['filtre'])) ) {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id = c.bt_article_id WHERE c.bt_id LIKE ? ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array($_GET['filtre'].'%'));
		}
		// filter for statut
		elseif ($_GET['filtre'] == 'draft') {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_statut=0 ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array());
		}
		elseif ($_GET['filtre'] == 'pub') {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_statut=1 ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array());
		}
		// filter for author
		elseif (substr($_GET['filtre'], 0, -strlen(strstr($_GET['filtre'], '.'))) == 'auteur') { //and $search != '') { // for "authors" the requests is "auteur.$search"
			$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_author=? ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array($search));
		}
		// no filter
		else {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id ORDER BY c.bt_id DESC LIMIT ".$GLOBALS['max_comm_admin'];
			$commentaires = liste_elements($query, array());
		}
	}
	elseif (!empty($_GET['q'])) {
		$arr = parse_search($_GET['q']);
		$sql_where = implode(array_fill(0, count($arr), 'c.bt_content LIKE ? '), 'AND ');
		$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE ".$sql_where."ORDER BY c.bt_id DESC";
		$commentaires = liste_elements($query, $arr);
	}
	else { // no filter, so list'em all
		$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id ORDER BY c.bt_id DESC LIMIT ".$GLOBALS['max_comm_admin'];
		$commentaires = liste_elements($query, array());
	}
	$nb_total_comms = liste_elements_count("SELECT count(*) AS nbr FROM commentaires", array());
}

// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['titre_commentaires']. ((!empty($article_title)) ?' | '.$article_title : ''), "comments");
afficher_topnav($GLOBALS['lang']['titre_commentaires']); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	afficher_form_filtre('commentaires', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo '<div class="nombre-elem">'."\n";
	if (!empty($article_id)) {
		echo '<ul>'."\n";
		echo "\t".'<li><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].$article_title.'</a></li>'."\n";
		echo "\t".'<li><a href="'.URL_ROOT.get_blogpath($article_id, $article_title).'">'.$GLOBALS['lang']['lien_article'].'</a></li>'."\n";
		echo '</ul>'."\n";
		echo '– &nbsp; '.ucfirst(nombre_objets(count($commentaires), 'commentaire'));
	} else {
		echo ucfirst(nombre_objets(count($commentaires), 'commentaire')).' '.$GLOBALS['lang']['sur'].' '.$nb_total_comms;
	}
	echo '</div>'."\n";
echo '</div>'."\n";

echo '<div id="page">'."\n";


// COMMENTAIRES
echo '<div id="liste-commentaires">';
foreach ($commentaires as $content) {
	echo "\n".afficher_commentaire($content);
}
echo '</div>'."\n";


if (!empty($article_id)) {
	echo '<div id="post-nv-commentaire">'."\n";
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>'."\n";
	$out = '{'.
		'"auth":'.json_encode($GLOBALS['auteur']).', '.
		'"mail":'.json_encode($GLOBALS['email']).', '.
		'"webp":'.json_encode($GLOBALS['racine']).', '.
		'"wiki":'.json_encode('').', '.
		'"btid":'.json_encode('').
	'}';
	echo "\t".'<script id="snv" type="application/json">'.$out.'</script>'."\n";


	echo afficher_form_commentaire($article_id, 'admin', $erreurs_form, '');
	echo '</div>'."\n";
}

// popup notif node
echo "\t".'<span id="popup-notif"><span id="count-posts"><span id="counter"></span></span><span id="message-return"></span></span>'."\n";

echo php_lang_to_js();
echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
echo '<script>';
echo 'var csrf_token = \''.new_token().'\''."\n";
echo 'new writeForm();'."\n";
echo '</script>'."\n";

footer($begin);

