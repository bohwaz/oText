<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();


/// Formulaire pour ajouter un lien dans Links côté Admin
function afficher_form_link($step, $erreurs, $editlink='') {
	if ($erreurs) {
		echo erreurs($erreurs);
	}
	$form = '';
	if ($step == 1) { // postage de l'URL : un champ affiché en GET
		$form .= '<form method="get" id="post-new-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'">'."\n";
		$form .= '<fieldset>'."\n";
		$form .= "\t".'<div class="contain-input">'."\n";
		$form .= "\t\t".'<label for="url">'.$GLOBALS['lang']['label_nouv_lien'].'</label>'."\n";
		$form .= "\t\t".'<input type="text" name="url" id="url" value="" size="70" placeholder="'.$GLOBALS['lang']['label_nouv_lien'].'" class="text" autocomplete="off" tabindex="10" />'."\n";
		$form .= "\t".'</div>'."\n";
		//$form .= "\t".'<p class="submit-bttns"><button type="submit" class="submit button-submit">'.$GLOBALS['lang']['envoyer'].'</button></p>'."\n";
		$form .= '</fieldset>'."\n";
		$form .= '</form>'."\n\n";

	} elseif ($step == 2) { // Form de l'URL, avec titre, description, en POST cette fois, et qu'il faut vérifier avant de stoquer dans la BDD.
		$form .= '<form method="post" onsubmit="return moveTag();" id="post-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'">'."\n";

		$url = $_GET['url'];
		$type = 'url';
		$title = $url;
		$charset = "UTF-8";
		$new_id = date('YmdHis');

		// URL is empty or no URI. It’s a note: we hide the URI field.
		if (empty($url) or (strpos($url, 'http') !== 0) ) {
			$type = 'note';
			$title = 'Note'.(!empty($url) ? ' : '.html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '');
			$url = $GLOBALS['racine'].'?mode=links&amp;id='.$new_id;
			$form .= hidden_input('url', $url);
			$form .= hidden_input('type', 'note');
		// URL is not empty
		} else {
			// Find out type of file
			$response = request_external_files(array($url => '1'), 15, false);
			$ext_file = $response[1];
			$rep_hdr = $ext_file['headers'];
			$cnt_type = (isset($rep_hdr['content-type'])) ? (is_array($rep_hdr['content-type']) ? $rep_hdr['content-type'][count($rep_hdr['content-type'])-1] : $rep_hdr['content-type']) : 'text/';
			$cnt_type = (is_array($cnt_type)) ? $cnt_type[0] : $cnt_type;

			// Image
			if (strpos($cnt_type, 'image/') === 0) {
				$filename = basename(parse_url($url, PHP_URL_PATH));
				$title = $filename.' ('.$GLOBALS['lang']['label_image'].')';
				if (list($width, $height) = @getimagesize($url)) {
					$fdata = $url;
					$type = 'image';
					$title .= ' - '.$width.'x'.$height.'px ';
				}
			}

			// Non-image NON-textual file (pdf…)
			elseif (strpos($cnt_type, 'text/') !== 0 and strpos($cnt_type, 'xml') === FALSE) {
				if ($GLOBALS['dl_link_to_files'] == 2) {
					$type = 'file';
				}
			}

			// a HTML document: parse it for any <title> ; fallback : $url
			elseif (!empty($ext_file['body'])) {
				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
				$dom->strictErrorChecking = FALSE;
				//$dom->loadHTML(mb_convert_encoding($ext_file['body'], 'HTML-ENTITIES',  'UTF-8'));
				$dom->loadHTML($ext_file['body']);
				$elements = $dom->getElementsByTagName('title');
				if ($elements->length > 0) {
					$title = trim($elements->item(0)->textContent);
				}
				libxml_use_internal_errors(false);
			}

			$form .= "\t".'<input type="text" name="url" value="'.htmlspecialchars($url).'" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_url']).'" size="50" class="text readonly-like" />'."\n";
			$form .= hidden_input('type', 'link');
		}

		$link = array('title' => htmlspecialchars($title), 'url' => htmlspecialchars($url));
		$form .= "\t".'<input type="text" name="title" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" required="" value="'.$link['title'].'" size="50" class="text" autofocus />'."\n";

		$form .= "\t".'<textarea class="text description" name="description" cols="40" rows="7" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_description']).'"></textarea>'."\n";

		$form .= "\t".'<div id="tag_bloc">'."\n";
		$form .= form_categories_links('links', '');
		$form .= "\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'"/>'."\n";
		$form .= "\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$form .= "\t".'</div>'."\n";

		$form .= "\t".'<p>'."\n";
		$form .= "\t\t".'<input type="checkbox" name="statut" id="statut" class="checkbox" />'.'<label class="forcheckbox" for="statut">'.$GLOBALS['lang']['label_lien_priv'].'</label>'."\n";
		$form .= "\t".'</p>'."\n";
		if ($type == 'image' or $type == 'file') {
			// download of file is asked
			$form .= "\t".'<p>'."\n";
			if ($GLOBALS['dl_link_to_files'] == 2)
			$form .= "\t\t".'<input type="checkbox" name="add_to_files" id="add_to_files" class="checkbox" />'.'<label class="forcheckbox" for="add_to_files">'.$GLOBALS['lang']['label_dl_fichier'].'</label>'."\n";
			// download of file is systematic
			elseif ($GLOBALS['dl_link_to_files'] == 1)
			$form .= hidden_input('add_to_files', 'on');
			$form .= "\t".'</p>'."\n";
		}
		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'links.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-submit" type="submit" name="enregistrer" id="valid-link">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('bt_id', $new_id);
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('dossier', '');
		$form .= '</form>'."\n\n";

	} elseif ($step == 'edit') { // Form pour l'édition d'un lien : les champs sont remplis avec le "wiki_content" et il y a les boutons suppr/activer en plus.
		$form = '<form method="post" onsubmit="return moveTag();" id="post-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'?id='.$editlink['bt_id'].'">'."\n";
		$form .= "\t".'<input type="text" name="url" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_url']).'" required="" value="'.$editlink['bt_link'].'" size="70" class="text readonly-like" /></label>'."\n";
		$form .= "\t".'<input type="text" name="title" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" required="" value="'.$editlink['bt_title'].'" size="70" class="text" autofocus /></label>'."\n";
		$form .= "\t".'<textarea class="description text" name="description" cols="70" rows="7" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_description']).'" >'.$editlink['bt_wiki_content'].'</textarea>'."\n";
		$form .= "\t".'<div id="tag_bloc">'."\n";
		$form .= form_categories_links('links', $editlink['bt_tags']);
		$form .= "\t\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'"/>'."\n";
		$form .= "\t\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$form .= "\t".'</div>'."\n";
		$form .= "\t".'<p>'."\n";
		$form .= "\t\t".'<input type="checkbox" name="statut" id="statut" class="checkbox" '.(($editlink['bt_statut'] == 0) ? 'checked ' : '').'/>'.'<label class="forcheckbox" for="statut">'.$GLOBALS['lang']['label_lien_priv'].'</label>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t".'<button class="submit button-delete" type="button" name="supprimer" onclick="rmArticle(this)">'.$GLOBALS['lang']['supprimer'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'links.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-submit" type="submit" name="editer">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= hidden_input('ID', $editlink['ID']);
		$form .= hidden_input('bt_id', $editlink['bt_id']);
		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('is_it_edit', 'yes');
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('type', $editlink['bt_type']);
		$form .= '</form>'."\n\n";
	}
	return $form;
}

// modèle d'affichage d'un div pour un lien (avec un formaulaire d'édition par lien).
function afficher_lien($link) {
	$list = '';
	$list .= '<div class="linkbloc'.(!$link['bt_statut'] ? ' privatebloc' : '').'">'."\n";

	$list .= '<div class="link-header">'."\n";
	$list .= "\t".'<a class="titre-lien" href="'.$link['bt_link'].'">'.$link['bt_title'].'</a>'."\n";
	$list .= "\t".'<span class="date">'.date_formate($link['bt_id']).', '.heure_formate($link['bt_id']).'</span>'."\n";
	$list .= "\t".'<div class="item-menu-options">';
	$list .= "\t\t".'<ul>'."\n";
	$list .= "\t\t\t".'<li><a href="'.basename($_SERVER['SCRIPT_NAME']).'?id='.$link['bt_id'].'">'.$GLOBALS['lang']['editer'].'</a></li>'."\n";
	if ($link['bt_statut'] == '1')
	$list .= "\t\t\t".'<li><a href="'.$GLOBALS['racine'].'?mode=links&amp;id='.$link['bt_id'].'">'.$GLOBALS['lang']['voir_sur_le_blog'].'</a></li>'."\n";
	$list .= "\t\t".'</ul>'."\n";
	$list .= "\t".'</div>'."\n";
	$list .=  '</div>'."\n";

	$list .= (!empty($link['bt_content'])) ? "\t".'<div class="link-content">'.$link['bt_content'].'</div>'."\n" : '';

	$list .= "\t".'<div class="link-footer">'."\n";
	$list .= "\t\t".'<ul class="link-tags">'."\n";
	if (!empty($link['bt_tags'])) {
		$tags = explode(',', $link['bt_tags']);
		foreach ($tags as $tag) $list .= "\t\t\t".'<li class="tag">'.'<a href="?filtre=tag.'.urlencode(trim($tag)).'">'.trim($tag).'</a>'.'</li>'."\n";
	}
	$list .= "\t\t".'</ul>'."\n";
	$list .= "\t\t".'<span class="hard-link">'.$link['bt_link'].'</span>'."\n";
	$list .= "\t".'</div>'."\n";

	$list .= '</div>'."\n";
	echo $list;
}


// TRAITEMENT
$step = 0;
$erreurs_form = array();
if (!isset($_GET['url'])) { // rien : on affiche le premier FORM
	$step = 1;
} else { // URL donné dans le $_GET
	$step = 2;
}
if (isset($_GET['id']) and preg_match('#\d{14}#', $_GET['id'])) {
	$step = 'edit';
}

if (isset($_POST['_verif_envoi'])) {
	$link = init_post_link2();
	$erreurs_form = valider_form_link($link);
	$step = 'edit';
	if (empty($erreurs_form)) {

		// URL est un fichier !html !js !css !php ![vide] && téléchargement de fichiers activé :
		if (!isset($_POST['is_it_edit']) and $GLOBALS['dl_link_to_files'] >= 1) {

			// dl_link_to_files : 0 = never ; 1 = always ; 2 = ask with checkbox
			if ( isset($_POST['add_to_files']) ) {
				$_POST['fichier'] = $link['bt_link'];
				$fichier = init_post_fichier();
				$erreurs = valider_form_fichier($fichier);
				if (empty($erreurs)) {
					traiter_form_fichier($fichier);
				}
			}
		}
		traiter_form_link($link);
	}
}

// create link list.
$tableau = array();
// on affiche les anciens liens seulement si on ne veut pas en ajouter un
if (!isset($_GET['url']) and !isset($_GET['ajout'])) {
	if ( !empty($_GET['filtre']) ) {
		// date
		if ( preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) {
			$query = "SELECT * FROM links WHERE bt_id LIKE ? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($_GET['filtre'].'%'));
		// visibles & brouillons
		} elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') {
			$query = "SELECT * FROM links WHERE bt_statut=? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)));
		// tags
		} elseif (strpos($_GET['filtre'], 'tag.') === 0) {
			$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
			$query = "SELECT * FROM links WHERE bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'));
		} else {
			$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT ".$GLOBALS['max_linx_admin'];
			$tableau = liste_elements($query, array());
		}
	// keyword
	} elseif (!empty($_GET['q'])) {
		$arr = parse_search($_GET['q']);
		$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title || bt_link ) LIKE ? '), 'AND ');
		$query = "SELECT * FROM links WHERE ".$sql_where."ORDER BY bt_id DESC";
		$tableau = liste_elements($query, $arr);
	// editing a specific link
	} elseif (!empty($_GET['id']) and is_numeric($_GET['id'])) {
		$query = "SELECT * FROM links WHERE bt_id=?";
		$tableau = liste_elements($query, array($_GET['id']));
	// no filter, show em all
	} else {
		$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_linx_admin'];
		$tableau = liste_elements($query, array());
	}
}

// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['mesliens'], "links");
afficher_topnav($GLOBALS['lang']['mesliens'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	// Affichage formulaire filtrage liens
	afficher_form_filtre('links', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	if ($step != 'edit' and $step != 2) {
		echo "\t".'<div class="nombre-elem">';
		echo "\t\t".ucfirst(nombre_objets(count($tableau), 'link')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM links", array())."\n";
		echo "\t".'</div>'."\n";
	}
echo '</div>'."\n";

echo '<div id="page">'."\n";

if ($step == 'edit' and !empty($tableau[0]) ) { // edit un lien : affiche le lien au dessus du champ d’édit
	echo afficher_form_link($step, $erreurs_form, $tableau[0]);
}
elseif ($step == 2) { // lien donné dans l’URL
	echo afficher_form_link($step, $erreurs_form);
}
else { // aucun lien à ajouter ou éditer : champ nouveau lien + listage des liens en dessus.
	echo afficher_form_link(1, $erreurs_form);
	echo '<div id="list-link">'."\n";
	foreach ($tableau as $link) {
		afficher_lien($link);
	}
	echo '</div>'."\n";
}

echo php_lang_to_js();
echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
echo '<script>'."\n";
if ($step == 1) {
	echo 'document.getElementById(\'url\').addEventListener(\'focus\', function(){ document.getElementById(\'post-new-lien\').classList.add(\'focusedField\'); }, false);'."\n";
	echo 'document.getElementById(\'post-new-lien\').addEventListener(\'click\', function(){ document.getElementById(\'url\').focus(); }, false);'."\n";
	echo 'document.getElementById(\'url\').addEventListener(\'blur\', function(){ document.getElementById(\'post-new-lien\').classList.remove(\'focusedField\'); }, false);'."\n";
}
echo '</script>';


footer($begin);

