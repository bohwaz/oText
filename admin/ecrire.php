<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();


function afficher_form_billet($article, $erreurs) {
	$html = '';
	if ($erreurs) { $html .= erreurs($erreurs); }

	function form_statut($etat) {
		$choix = array('1' => $GLOBALS['lang']['label_publie'], '0' => $GLOBALS['lang']['label_invisible']);
		return form_select('statut', $choix, $etat, $GLOBALS['lang']['label_dp_etat']);
	}
	function form_allow_comment($etat) {
		$choix= array('1' => $GLOBALS['lang']['ouverts'], '0' => $GLOBALS['lang']['fermes']);
		return form_select('allowcomment', $choix, $etat, $GLOBALS['lang']['label_dp_commentaires']);
	}

	if (!empty($article)) {
		$date_dec = decode_id($article['bt_date']);
		$defaut_ymd = $date_dec['y'].'-'.$date_dec['m'].'-'.$date_dec['d'];
		$defaut_his = $date_dec['h'].':'.$date_dec['i'].':'.$date_dec['s'];
		$titredefaut = $article['bt_title'];
		$chapodefaut = get_entry('articles', 'bt_abstract', $article['bt_id'], 'return');
		$notesdefaut = $article['bt_notes'];
		$tagsdefaut = $article['bt_tags'];
		$contenudefaut = htmlspecialchars($article['bt_wiki_content']);
		$motsclesdefaut = $article['bt_keywords'];
		$statutdefaut = $article['bt_statut'];
		$allowcommentdefaut = $article['bt_allow_comments'];

		$html .= '<form id="form-ecrire" method="post" onsubmit="return moveTag();" action="'.basename($_SERVER['SCRIPT_NAME']).'?post_id='.$article['bt_id'].'" >'."\n";
	} else {
		$defaut_ymd = date('Y-m-d');
		$defaut_his = date('H:i:s');
		$chapodefaut = '';
		$contenudefaut = '';
		$motsclesdefaut = '';
		$tagsdefaut = '';
		$titredefaut = '';
		$notesdefaut = '';
		$statutdefaut = '1';
		$allowcommentdefaut = '1';

		$html .= '<form id="form-ecrire" method="post" onsubmit="return moveTag();" action="'.basename($_SERVER['SCRIPT_NAME']).'" >'."\n";
	}

	$html .= '<div class="main-form">'."\n";
	$html .= '<input id="titre" name="titre" type="text" size="50" value="'.$titredefaut.'" required="" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" tabindex="30" class="text" spellcheck="true" />'."\n" ;
	$html .= '<div id="chapo_note">'."\n";
	$html .= '<textarea id="chapo" name="chapo" rows="5" cols="20" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_chapo']).'" tabindex="35" class="text" >'.$chapodefaut.'</textarea>'."\n" ;
	$html .= '<textarea id="notes" name="notes" rows="5" cols="20" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_notes']).'" tabindex="40" class="text" >'.$notesdefaut.'</textarea>'."\n" ;
	$html .= '</div>'."\n";

	$html .= '<div id="content_format">'."\n";
	$html .= form_formatting_toolbar(TRUE);
	$html .= '<textarea id="contenu" name="contenu" rows="35" cols="60" required="" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_contenu']).'" tabindex="55" class="text">'.$contenudefaut.'</textarea>'."\n" ;
	$html .= '</div>'."\n";

	if ($GLOBALS['activer_categories'] == '1') {
		$html .= "\t".'<div id="tag_bloc">'."\n";
		$html .= form_categories_links('articles', $tagsdefaut);
		$html .= "\t\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'" tabindex="65"/>'."\n";
		$html .= "\t\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$html .= "\t".'</div>'."\n";
	}

	if ($GLOBALS['automatic_keywords'] == '0') {
		$html .= '<input id="mots_cles" name="mots_cles" type="text" size="50" value="'.$motsclesdefaut.'" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_motscle']).'" tabindex="67" class="text" />'."\n";
	}
	$html .= '</div>';

	$html .= '<div id="date-and-opts">'."\n";
		$html .= '<div id="datetime">'."\n";
			$html .= '<span id="formdate"><input class="text" required="" step="1" name="ymd" id="ymd" type="date" value='.$defaut_ymd.' /></span>'."\n";
			$html .= '<span id="formheure"><input class="text" required="" step="1" name="his" id="his" type="time" value='.$defaut_his.' /></span>'."\n";
		$html .= '</div>'."\n";
		$html .= '<div id="opts">'."\n";
			$html .= '<span id="formstatut">'."\n".form_statut($statutdefaut).'</span>'."\n";
			$html .= '<span id="formallowcomment">'."\n".form_allow_comment($allowcommentdefaut).'</span>'."\n";
		$html .= '</div>'."\n";

		$html .= '<p class="submit-bttns">'."\n";
		$html .= "\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'articles.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$html .= "\t".'<button class="submit button-submit" type="submit" name="enregistrer" tabindex="70">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$html .= '</p>'."\n";

		$html .= '<p class="submit-bttns">'."\n";
		if (!empty($article)) {
			$html .= hidden_input('article_id', $article['bt_id']);
			$html .= hidden_input('article_date', $article['bt_date']);
			$html .= hidden_input('ID', $article['ID']);
			$html .= "\t".'<button class="submit button-delete" type="button" name="supprimer" onclick="rmArticle(this)" />'.$GLOBALS['lang']['supprimer'].'</button>'."\n";
		}
		$html .= '</p>'."\n";


    $html .= '</div>'."\n";

	$html .= hidden_input('_verif_envoi', '1');
	$html .= hidden_input('token', new_token());

	$html .= '</form>'."\n";
	echo $html;
}

function apercu($article) {
	if (!empty($article)) {
		$apercu = '<h2>'.$article['bt_title'].'</h2>'."\n";
		if (empty($article['bt_abstract'])) {
			$article['bt_abstract'] = mb_substr(strip_tags($article['bt_content']), 0, 249).'…';
		}
		$apercu .= '<div><strong>'.$article['bt_abstract'].'</strong></div>'."\n";
		$apercu .= '<div>'.rel2abs_admin($article['bt_content']).'</div>'."\n";
		echo '<div id="apercu">'."\n".$apercu.'</div>'."\n\n";
	}
}




// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$billet = init_post_article();
	$erreurs_form = valider_form_billet($billet);
	if (empty($erreurs_form)) {
		traiter_form_billet($billet);
	}
}

// RECUP INFOS ARTICLE SI DONNÉ
$post = array();
if (isset($_GET['post_id'])) {
	$article_id = htmlspecialchars($_GET['post_id']);
	$query = "SELECT * FROM articles WHERE bt_id LIKE ?";
	$posts = liste_elements($query, array($article_id), 'articles');
	if (isset($posts[0])) $post = $posts[0];
}
// recup titre
if ( !empty($post) ) {
	$titre_ecrire_court = $GLOBALS['lang']['titre_maj'];
	$titre_ecrire = $titre_ecrire_court.' : '.$post['bt_title'];
} else {
	$titre_ecrire_court = $GLOBALS['lang']['titre_ecrire'];
	$titre_ecrire = $titre_ecrire_court;
}

// DEBUT PAGE
afficher_html_head($titre_ecrire, 'ecrire');
afficher_topnav($titre_ecrire_court, ''); #top

echo '<div id="axe">'."\n";
if (!empty($post)) {
	echo '<div id="subnav">'."\n";
		echo '<div class="nombre-elem">';
		echo '<a href="'.$post['bt_link'].'">'.$GLOBALS['lang']['lien_article'].'</a> &nbsp; – &nbsp; ';
		echo '<a href="commentaires.php?post_id='.$article_id.'">'.ucfirst(nombre_objets($post['bt_nb_comments'], 'commentaire')).'</a>';
		echo '</div>'."\n";
	echo '</div>'."\n";
}

echo '<div id="page">'."\n";

// WRITING FORM
apercu($post);
afficher_form_billet($post, $erreurs_form);

echo php_lang_to_js();
echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
echo '<script>';
echo '
new writeForm();

var form = document.getElementById(\'form-ecrire\');

function markAsEdited() {
	form.dataset.edited = true;
}
function markStar() {
	document.title = \'[*] \' + document.title;
}

// When edited, place a little « [*] » on the title.
form.addEventListener(\'input\', markStar, {"once": true});
form.addEventListener(\'input\', markAsEdited);

// prevent user from loosing data by closing window without saving
window.addEventListener("beforeunload", function (e) {
	var confirmationMessage = BTlang.questionQuitPage;
	if (!form.dataset.edited) { return true; };
	(e || window.event).returnValue = confirmationMessage || \'\'; //Gecko + IE
	return confirmationMessage;                                   // Webkit : ignore this.
});';
echo '</script>'."\n";


footer($begin);
