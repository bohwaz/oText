<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

setcookie('lastAccessComments', time(), time()+365*24*60*60, null, null, true, true);


function afficher_commentaire($comment) {
	afficher_form_commentaire($comment['bt_article_id'], 'admin', '', $comment);
	echo '<div class="commentbloc'.(!$comment['bt_statut'] ? ' privatebloc' : '').'" id="'.article_anchor($comment['bt_id']).'">'."\n";
	echo '<div class="comm-side-icon">'."\n";
		echo "\t".'<div class="comm-title">'."\n";
		echo "\t\t".'<img class="author-icon" src="'.URL_ROOT.'favatar.php?w=gravatar&amp;q='.md5((!empty($comment['bt_email']) ? $comment['bt_email'] : $comment['bt_author'] )).'&amp;s=48&amp;d=monsterid"/>'."\n";
		echo "\t\t".'<span class="date">'.date_formate($comment['bt_id']).'<span>'.heure_formate($comment['bt_id']).'</span></span>'."\n" ;

		echo "\t\t".'<span class="reply" onclick="reply(\'[b]@['.str_replace('\'', '\\\'', $comment['bt_author']).'|#'.article_anchor($comment['bt_id']).'] :[/b] \'); ">Reply</span> ';
		echo (!empty($comment['bt_webpage'])) ? "\t\t".'<span class="webpage"><a href="'.$comment['bt_webpage'].'" title="'.$comment['bt_webpage'].'">'.$comment['bt_webpage'].'</a></span>'."\n" : '';
		echo (!empty($comment['bt_email'])) ? "\t\t".'<span class="email"><a href="mailto:'.$comment['bt_email'].'" title="'.$comment['bt_email'].'">'.$comment['bt_email'].'</a></span>'."\n" : '';
		echo "\t".'</div>'."\n";
	echo '</div>'."\n";
	
	echo '<div class="comm-main-frame">'."\n";
	echo "\t".'<div class="comm-header">'."\n";
	echo "\t\t".'<div class="comm-title">'."\n";
	echo "\t\t\t".'<span class="author"><a href="?filtre=auteur.'.$comment['bt_author'].'" title="'.$GLOBALS['lang']['label_all_comm_by_author'].'">'.$comment['bt_author'].'</a> :</span>'."\n";
	echo "\t\t".'</div>'."\n";

	echo "\t\t".'<span class="link-article"> '.$GLOBALS['lang']['sur'].' <a href="'.basename($_SERVER['SCRIPT_NAME']).'?post_id='.$comment['bt_article_id'].'">'.$comment['bt_title'].'</a></span>'."\n";

	echo "\t\t".'<div class="item-menu-options">'."\n";
	echo "\t\t\t".'<ul>'."\n";
	echo "\t\t\t\t".'<li><a href="#" onclick="return unfold(this);" data-com-dom-anchor="'.article_anchor($comment['bt_id']).'">'.$GLOBALS['lang']['editer'].'</a></li>'."\n";
	echo "\t\t\t\t".'<li><a href="#" onclick="return activate_comm(this);" data-com-dom-anchor="'.article_anchor($comment['bt_id']).'" data-comm-id="'.$comment['ID'].'" data-comm-btid="'.$comment['bt_id'].'" data-comm-art-id="'.$comment['bt_article_id'].'">'.$GLOBALS['lang'][(!$comment['bt_statut'] ? '' : 'des').'activer'].'</a></li>'."\n";
	echo "\t\t\t\t".'<li><a href="#" onclick="return suppr_comm(this);" data-comm-id="'.$comment['ID'].'" data-com-dom-anchor="'.article_anchor($comment['bt_id']).'" data-comm-art-id="'.$comment['bt_article_id'].'">'.$GLOBALS['lang']['supprimer'].'</a></li>'."\n";
	echo "\t\t\t".'</ul>'."\n";
	echo "\t\t".'</div>'."\n";

	echo "\t".'</div>'."\n";

	echo "\t".'<div class="comm-content">'."\n";
	echo $comment['bt_content'];
	echo "\t".'</div>'."\n";
	echo $GLOBALS['form_commentaire'];

	echo "\t".'</div>'."\n\n";
	echo '</div>'."\n\n";
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
		$comment = init_post_comment($_POST['comment_article_id'], 'admin');
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
	$commentaires = liste_elements($query, array($article_id), 'commentaires');

	if (!empty($commentaires)) {
		$article_title = $commentaires[0]['bt_title'];
	} else {
		$article_title = get_entry('articles', 'bt_title', $article_id, 'return');
	}

}

// else, no ID
else {
	if ( !empty($_GET['filtre']) ) {
		// for "authors" the requests is "auteur.$search" : here we split the type of search and what we search.
		$type = substr($_GET['filtre'], 0, -strlen(strstr($_GET['filtre'], '.')));
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		// filter for date
		if (preg_match('#^\d{6}(\d{1,8})?$#', ($_GET['filtre'])) ) {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id = c.bt_article_id WHERE c.bt_id LIKE ? ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array($_GET['filtre'].'%'), 'commentaires');
		}
		// filter for statut
		elseif ($_GET['filtre'] == 'draft') {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_statut=0 ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
		elseif ($_GET['filtre'] == 'pub') {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_statut=1 ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
		// filter for author
		elseif ($type == 'auteur' and $search != '') {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE c.bt_author=? ORDER BY c.bt_id DESC";
			$commentaires = liste_elements($query, array($search), 'commentaires');
		}
		// no filter
		else {
			$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id ORDER BY c.bt_id DESC LIMIT ".$GLOBALS['max_comm_admin'];
			$commentaires = liste_elements($query, array(), 'commentaires');
		}
	}
	elseif (!empty($_GET['q'])) {
		$arr = parse_search($_GET['q']);
		$sql_where = implode(array_fill(0, count($arr), 'c.bt_content LIKE ? '), 'AND ');
		$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id WHERE ".$sql_where."ORDER BY c.bt_id DESC";
		$commentaires = liste_elements($query, $arr, 'commentaires');
	}
	else { // no filter, so list'em all
		$query = "SELECT c.*, a.bt_title FROM commentaires c LEFT JOIN articles a ON a.bt_id=c.bt_article_id ORDER BY c.bt_id DESC LIMIT ".$GLOBALS['max_comm_admin'];
		$commentaires = liste_elements($query, array(), 'commentaires');
	}
	$nb_total_comms = liste_elements_count("SELECT count(*) AS nbr FROM commentaires", array());
}

// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['titre_commentaires']. ((!empty($article_title)) ?' | '.$article_title : ''), "comments");
afficher_topnav($GLOBALS['lang']['titre_commentaires'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	afficher_form_filtre('commentaires', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo '<div class="nombre-elem">'."\n";
	if (!empty($article_id)) {
		$article_link = get_blogpath($article_id, $article_title);
		echo '<ul>'."\n";
		echo "\t".'<li><a href="ecrire.php?post_id='.$article_id.'">'.$GLOBALS['lang']['ecrire'].$article_title.'</a></li>'."\n";
		echo "\t".'<li><a href="'.$article_link.'">'.$GLOBALS['lang']['lien_article'].'</a></li>'."\n";
		echo '</ul>'."\n";
		echo '– &nbsp; '.ucfirst(nombre_objets(count($commentaires), 'commentaire'));
	} else {
		echo ucfirst(nombre_objets(count($commentaires), 'commentaire')).' '.$GLOBALS['lang']['sur'].' '.$nb_total_comms;
	}
	echo '</div>'."\n";
echo '</div>'."\n";

echo '<div id="page">'."\n";


// COMMENTAIRES
echo '<div id="liste-commentaires">'."\n";
foreach ($commentaires as $content) {
	afficher_commentaire($content);
}
echo '</div>'."\n";


if (!empty($article_id)) {
	echo '<div id="post-nv-commentaire">'."\n";
	afficher_form_commentaire($article_id, 'admin', $erreurs_form, '');
	echo '<h2 class="poster-comment">'.$GLOBALS['lang']['comment_ajout'].'</h2>'."\n";
	echo $GLOBALS['form_commentaire'];
	echo '</div>'."\n";
}

echo "\n".'<script src="style/javascript.js" type="text/javascript"></script>'."\n";
echo '<script type="text/javascript">';
echo php_lang_to_js(0);
echo 'var csrf_token = \''.new_token().'\'';
echo '</script>';

footer($begin);

