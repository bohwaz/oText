<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

// TEMPLATE VARS
/*
 * Vars used in them files, aimed to get
 * replaced with some specific data
 *
 */
$GLOBALS['boucles'] = array(
	'posts' => 'BOUCLE_posts',
	'commentaires' => 'BOUCLE_commentaires',
);

$GLOBALS['balises'] = array(
	'version' => '{version}',
	'app_name' => '{app_name}',
	'style' => '{style}',
	'racine_du_site' => '{racine_du_site}',
	'rss' => '{rss}',
	'rss_comments' => '{rss_comments}',
	// Navigation
	'pagination' => '{pagination}',
	// Blog
	'blog_nom' => '{blog_nom}',
	'blog_description' => '{blog_description}',
	'blog_auteur' => '{blog_auteur}',
	'blog_email' => '{blog_email}',
	'blog_motscles' => '{keywords}',
	// Formulaires
	'form_recherche' => '{recherche}',
	'form_commentaire' => '{formulaire_commentaire}',
	// Encarts
	'comm_encart' => '{commentaires_encart}',
	'cat_encart' => '{categories_encart}',
	'calendrier' => '{calendrier}',
	'more_articles' => '{readmore}',

	// Article
	'article_titre' => '{article_titre}',
	'article_titre_page' => '{article_titre_page}',
	'article_titre_echape' => '{article_titre_echape}',
	'article_chapo' => '{article_chapo}',
	'article_contenu' => '{article_contenu}',
	'article_heure' => '{article_heure}',
	'article_date' => '{article_date}',
	'article_date_iso' => '{article_date_iso}',
	'article_lien' => '{article_lien}',
	'article_tags' => '{article_tags}',
	'article_tags_plain' => '{article_tags_plain}',
	'article_illustration' => '{article_illustration}',
	'article_illustration_url' => '{article_illustration_url}',
	'nb_commentaires' => '{nombre_commentaires}',

	// Commentaire
	'commentaire_auteur' => '{commentaire_auteur}',
	'commentaire_auteur_lien' => '{commentaire_auteur_lien}',
	'commentaire_contenu' => '{commentaire_contenu}',
	'commentaire_heure' => '{commentaire_heure}',
	'commentaire_date' => '{commentaire_date}',
	'commentaire_date_iso' => '{commentaire_date_iso}',
	'commentaire_email' => '{commentaire_email}',
	'commentaire_webpage' => '{commentaire_webpage}',
	'commentaire_anchor' => '{commentaire_ancre}', // the id="" content
	'commentaire_lien' => '{commentaire_lien}',
	'commentaire_md5email' => '{commentaire_md5email}',

	// Liens
	'lien_titre' => '{lien_titre}',
	'lien_titre_echape' => '{lien_titre_echape}',
	'lien_url' => '{lien_url}',
	'lien_date' => '{lien_date}',
	'lien_date_iso' => '{lien_date_iso}',
	'lien_heure' => '{lien_heure}',
	'lien_description' => '{lien_description}',
	'lien_permalink' => '{lien_permalink}',
	'lien_id' => '{lien_id}',
	'lien_tags' => '{lien_tags}',
);

function conversions_theme($texte, $solo_art, $cnt_mode) {
	$texte = str_replace($GLOBALS['balises']['version'], BLOGOTEXT_VERSION, $texte);
	$texte = str_replace($GLOBALS['balises']['app_name'], BLOGOTEXT_NAME, $texte);
	$texte = str_replace($GLOBALS['balises']['style'], $GLOBALS['theme_style'], $texte);
	$texte = str_replace($GLOBALS['balises']['racine_du_site'], $GLOBALS['racine'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_auteur'], $GLOBALS['auteur'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_email'], $GLOBALS['email'], $texte);
	$texte = str_replace($GLOBALS['balises']['blog_nom'], $GLOBALS['nom_du_site'], $texte);

	if ($cnt_mode == 'post' and !empty($solo_art)) {
		$texte = str_replace($GLOBALS['balises']['article_titre_page'], $solo_art['bt_title'].' - ', $texte);
		$texte = str_replace($GLOBALS['balises']['article_titre'], $solo_art['bt_title'], $texte);
		$texte = str_replace($GLOBALS['balises']['article_titre_echape'], urlencode($solo_art['bt_title']), $texte);
		$texte = str_replace($GLOBALS['balises']['article_lien'], URL_ROOT.$solo_art['bt_link'], $texte);
		if ($solo_art['bt_type'] == 'article') {
			$texte = str_replace($GLOBALS['balises']['article_chapo'], str_replace(array("\r", "\n"), ' ', ((empty($solo_art['bt_abstract'])) ? mb_substr(strip_tags($solo_art['bt_content']), 0, 249).'…' : $solo_art['bt_abstract'])), $texte);
			$texte = str_replace($GLOBALS['balises']['blog_motscles'], $solo_art['bt_keywords'], $texte);
			$texte = str_replace($GLOBALS['balises']['article_illustration_url'], $solo_art['illustration_image'][1], $texte);

		}
		if ($solo_art['bt_type'] == 'link' or $solo_art['bt_type'] == 'note') {
			$texte = str_replace($GLOBALS['balises']['article_chapo'], trim(str_replace(array("\r", "\n"), ' ', mb_substr(strip_tags($solo_art['bt_content']), 0, 149))).'…', $texte);
			$texte = str_replace($GLOBALS['balises']['article_titre_page'], $solo_art['bt_title'].' - ', $texte);
		}
	}

	// si remplacé, ceci n'a pas d'effet.
	$texte = str_replace($GLOBALS['balises']['article_illustration_url'], 'favicon.png', $texte);
	$texte = str_replace($GLOBALS['balises']['blog_description'], $GLOBALS['description'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_titre_page'], '', $texte);
	$texte = str_replace($GLOBALS['balises']['blog_motscles'], $GLOBALS['keywords'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_titre_echape'], '', $texte);
	$texte = str_replace($GLOBALS['balises']['article_lien'], $GLOBALS['racine'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_chapo'], $GLOBALS['description'], $texte);

	$texte = str_replace($GLOBALS['balises']['pagination'], lien_pagination(), $texte);

	if (strpos($texte, $GLOBALS['balises']['form_recherche']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['form_recherche'], moteur_recherche(''), $texte) ;
	}
	if (strpos($texte, $GLOBALS['balises']['more_articles']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['more_articles'], html_readmore(), $texte) ;
	}
	if (strpos($texte, $GLOBALS['balises']['calendrier']) !== FALSE) {
		$texte = str_replace($GLOBALS['balises']['calendrier'], html_calendrier(), $texte) ;
	}


	// Formulaires
	$texte = str_replace($GLOBALS['balises']['rss'], $GLOBALS['rss'], $texte);
	$texte = str_replace($GLOBALS['balises']['comm_encart'], encart_commentaires(), $texte);
	$texte = str_replace($GLOBALS['balises']['cat_encart'], encart_categories((isset($_GET['mode']))?$_GET['mode']:''), $texte);
	if (isset($GLOBALS['rss_comments'])) { $texte = str_replace($GLOBALS['balises']['rss_comments'], $GLOBALS['rss_comments'], $texte);}

	return $texte;
}


// Commentaire
function conversions_theme_commentaire($texte, $commentaire) {
	$texte = str_replace($GLOBALS['balises']['commentaire_contenu'], $commentaire['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_date'], date_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_date_iso'], date_formate_iso($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_heure'], heure_formate($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_email'], $commentaire['bt_email'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_md5email'], md5($commentaire['bt_email']), $texte);

	$auteur_lien = (!empty($commentaire['bt_webpage'])) ? '<a href="'.$commentaire['bt_webpage'].'" class="webpage">'.$commentaire['bt_author'].'</a>' : $commentaire['bt_author'];
	$texte = str_replace($GLOBALS['balises']['commentaire_auteur_lien'], $auteur_lien, $texte);

	$texte = str_replace($GLOBALS['balises']['commentaire_auteur'], str_replace("'", "\\'", $commentaire['bt_author']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_webpage'], $commentaire['bt_webpage'], $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_anchor'], article_anchor($commentaire['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['commentaire_lien'], $commentaire['bt_link'], $texte);
	return $texte;
}

// Article
function conversions_theme_article($texte, $billet) {
	$texte = str_replace($GLOBALS['balises']['rss_comments'], 'rss.php?id='.$billet['bt_id'], $texte);
	// if Admin : add "edit" link to post-title
	if (!empty($_SESSION['user_id'])) {
		$edit = '<a href="'.(str_replace(BT_ROOT, "", DIR_ADMIN)).'ecrire.php?post_id='.$billet['bt_id'].'">Édit</a>';
	} else {
		$edit = '';
	}

	$texte = str_replace($GLOBALS['balises']['article_titre'], $billet['bt_title'].$edit, $texte);
	$detected_images = html_get_image_from_article($billet['bt_content']);
	$texte = str_replace($GLOBALS['balises']['article_illustration'], $detected_images[0], $texte);

	$texte = str_replace($GLOBALS['balises']['article_titre_echape'], urlencode($billet['bt_title']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_chapo'], ((empty($billet['bt_abstract'])) ? mb_substr(strip_tags($billet['bt_content']), 0, 249).'…' : $billet['bt_abstract']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_contenu'], $billet['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_date'], date_formate($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_date_iso'], date_formate_iso($billet['bt_date']), $texte);
	$texte = str_replace($GLOBALS['balises']['article_heure'], heure_formate($billet['bt_date']), $texte);
	// comments closed (globally or only for this article) and no comments => say « comments closed »
	if ( ($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) and $billet['bt_nb_comments'] == 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], $GLOBALS['lang']['note_comment_closed'], $texte); }
	// comments open OR ( comments closed AND comments exists ) => say « nb comments ».
	if ( !($billet['bt_allow_comments'] == 0 or $GLOBALS['global_com_rule'] == 1 ) or $billet['bt_nb_comments'] != 0 ) { $texte = str_replace($GLOBALS['balises']['nb_commentaires'], nombre_objets($billet['bt_nb_comments'], 'commentaire'), $texte); }
	$texte = str_replace($GLOBALS['balises']['article_lien'], URL_ROOT.$billet['bt_link'], $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags'], liste_tags($billet, '1'), $texte);
	$texte = str_replace($GLOBALS['balises']['article_tags_plain'], liste_tags($billet, '0'), $texte);
	return $texte;
}

// Liens
function conversions_theme_lien($texte, $lien) {
	$texte = str_replace($GLOBALS['balises']['article_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_titre'], $lien['bt_title'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_titre_echape'], urlencode($lien['bt_title']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_url'], $lien['bt_link'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_date'], date_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_date_iso'], date_formate_iso($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_heure'], heure_formate($lien['bt_id']), $texte);
	$texte = str_replace($GLOBALS['balises']['lien_permalink'], $lien['bt_id'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_description'], $lien['bt_content'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_id'], $lien['ID'], $texte);
	$texte = str_replace($GLOBALS['balises']['lien_tags'], liste_tags($lien, '1'), $texte);
	return $texte;
}


// récupère le bout du fichier thème contenant une boucle comme {BOUCLE_commentaires}
//  soit le morceau de HTML retourné est parsé à son tour pour crée le HTML de chaque commentaire ou chaque article.
//  soit le morceau de HTML retourné sert à se faire remplacer par l’ensemble des commentaires constitués
function extract_boucles($texte, $balise, $incl) {
	$len_balise_d = 0 ; $len_balise_f = 0;
	if ($incl == 'excl') { // la $balise est exclue : bli{p}blabla{/p}blo => blabla
		$len_balise_d = strlen('{'.$balise.'}');
	}
	else {// la $balise est inclue : bli{p}blabla{/p}blo => {p}blabla{/p}
		$len_balise_f = strlen('{/'.$balise.'}');
	}

	$debut = strpos($texte, '{'.$balise.'}');
	$fin = strpos($texte, '{/'.$balise.'}');

	if ($debut !== FALSE and $fin !== FALSE) {
		$debut += $len_balise_d;
		$fin += $len_balise_f;

		$length = $fin - $debut;
		$return = substr($texte, $debut, $length);
		return $return;
	} else { // $balises n’est pas dans le texte : retourne le texte sans changements.
		return $texte;
	}
}

// only used by the main page of the blog (not on admin) : shows main blog page.
function afficher_index($tableau, $type, $erreurs_form) {
	$HTML = '';
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
	if (!($theme_post = file_get_contents($GLOBALS['theme_post_post']))) die($GLOBALS['lang']['err_theme_introuvable']);

	if ($type == 'list') {
		$HTML_elmts = '';
		if (!empty($tableau)) {
			$firstArticleArray = array();
			// get template file based on article type.
			switch ($tableau[0]['bt_type']) {
				case 'article':
					if (!($theme_article = file_get_contents($GLOBALS['theme_post_artc']))) die($GLOBALS['lang']['err_theme_introuvable']);
					$conversion_theme_fonction = 'conversions_theme_article';
					break;
				case 'comment':
					if (!($theme_article = file_get_contents($GLOBALS['theme_post_comm']))) die($GLOBALS['lang']['err_theme_introuvable']);
					$conversion_theme_fonction = 'conversions_theme_commentaire';
					break;
				case 'link':
				case 'note':
					// if single link, put link title in <title>
					if (1 === count($tableau)) {
						$firstArticleArray = $tableau[0];
					}
					if (!($theme_article = file_get_contents($GLOBALS['theme_post_link']))) die($GLOBALS['lang']['err_theme_introuvable']);
					$conversion_theme_fonction = 'conversions_theme_lien';
					break;				
				default:
					die($tableau[0]['bt_type'].': Wrong data type');
					break;
			}
			// convert templates to HTML filled with data
			foreach ($tableau as $element) {
				$HTML_elmts .=  $conversion_theme_fonction($theme_article, $element);
			}
			$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $theme_page);
			$HTML = conversions_theme($HTML, $firstArticleArray, 'post');
		}
		else {
			$HTML_article = conversions_theme($theme_page, array(), 'list');
			$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
		}
	}

	elseif ($type == 'post') {
		$billet = $tableau;
		$billet['illustration_image'] = html_get_image_from_article($billet['bt_content']);
		// parse & apply template article
		$HTML_article = conversions_theme_article($theme_post, $billet);
		// add form commentaire
		$HTML_article = str_replace($GLOBALS['balises']['form_commentaire'], afficher_form_commentaire($billet['bt_id'], 'public', $erreurs_form, ''), $HTML_article);


		// parse & apply templace commentaires
		$HTML_comms = '';
		// get list comments
		if ($billet['bt_nb_comments'] != 0) {
			$query = "SELECT c.*, a.bt_title FROM commentaires AS c, articles AS a WHERE c.bt_article_id=? AND c.bt_article_id=a.bt_id AND c.bt_statut=1 ORDER BY c.bt_id LIMIT ? ";
			$commentaires = liste_elements($query, array($billet['bt_id'], $billet['bt_nb_comments']));
			$template_comments = extract_boucles($theme_post, $GLOBALS['boucles']['commentaires'], 'excl');
			foreach ($commentaires as $element) {
				$HTML_comms .=  conversions_theme_commentaire($template_comments, $element);
			}
		}

		// in $article : pastes comments
		$v = extract_boucles($theme_post, $GLOBALS['boucles']['commentaires'], 'incl');
		$HTML_article = str_replace($v, $HTML_comms, $HTML_article);

		// in global page : pastes article and comms
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_article, $theme_page);

		// in global page : remplace remaining tags
		$HTML = conversions_theme($HTML, $billet, 'post');
	}
	echo $HTML;
}

// Affiche la liste des articles, avec le ?liste dans l’url
function afficher_liste($tableau) {
	$HTML_elmts = '';
	if (!($theme_page = file_get_contents($GLOBALS['theme_liste']))) die($GLOBALS['lang']['err_theme_introuvable']);
	$HTML_article = conversions_theme($theme_page, array(), 'list');
	if (!empty($tableau)) {
		$HTML_elmts .= '<ul id="liste-all-articles">'."\n";
		foreach ($tableau as $e) {
			$short_date = substr($e['bt_date'], 0, 4).'/'.substr($e['bt_date'], 4, 2).'/'.substr($e['bt_date'], 6, 2);
			$HTML_elmts .= "\t".'<li><time datetime="'.date_formate_iso($e['bt_date']).'">'.$short_date.'</time><a href="'.$e['bt_link'].'">'.$e['bt_title'].'</a></li>'."\n";
		}
		$HTML_elmts .= '</ul>'."\n";
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $HTML_elmts, $HTML_article);
	}
	else {
		$HTML = str_replace(extract_boucles($theme_page, $GLOBALS['boucles']['posts'], 'incl'), $GLOBALS['lang']['note_no_article'], $HTML_article);
	}
	echo $HTML;
}