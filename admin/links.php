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
function afficher_form_link($erreurs, $editlink='') {
	if ($erreurs) {
		echo erreurs($erreurs);
	}
	$form = '';

	// Affichage du formulaire simple-champ pour poster un nouveau lien (et les anciels liens en dessous)
	if (!isset($_GET['url']) and !isset($_GET['id'])) {
		$form .= '<form method="get" id="post-new-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'">'."\n";
		$form .= '<fieldset>'."\n";
		$form .= "\t".'<div class="contain-input">'."\n";
		$form .= "\t\t".'<label for="url">'.$GLOBALS['lang']['label_nouv_lien'].'</label>'."\n";
		$form .= "\t\t".'<input type="text" inputmode="url" name="url" id="url" value="" size="70" placeholder="'.$GLOBALS['lang']['label_nouv_lien'].'" class="text" autocomplete="off" tabindex="10" />'."\n";
		$form .= "\t\t".'<button type="submit" class="submit">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</div>'."\n";
		$form .= '</fieldset>'."\n";
		$form .= '</form>'."\n\n";
	}


	// affichage du formulaire complet, soit d’ajout, soit d’édition.
	else {
		$get_id = '';
		$url = '';
		$url_hidden = '';
		$type = 'link';
		$title = '';
		$charset = "UTF-8";
		$new_id = '';
		$content = '';
		$HTML = '';
		$tags = '';
		$checked = '';
		$HTML_submit = '';
		$HTML_suppr = '';

		// ajout d’un lien : on récupère le titre du lien, etc.
		if (isset($_GET['url'])) {
			$new_id = date('YmdHis');
			$HTML_submit = "\t\t".'<button class="submit button-submit" type="submit" name="enregistrer" tabindex="4">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
			$url = htmlspecialchars($_GET['url']);

			// URL is empty or no URI. It’s a note: we hide the URI field.

			if (empty($url) or (strpos($url, 'http') !== 0) ) {
				$type = 'note';
				$title = 'Note'.(!empty($url) ? ' : '.html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '');
				$url = $GLOBALS['racine'].'?mode=links&amp;id='.$new_id;
				$url_hidden = 'hidden';

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
						$title .= ' - '.$width.'x'.$height.'px ';
					}
				}

				// a HTML document: parse it for any <title> ; fallback : $url
				elseif (!empty($ext_file['body'])) {
					libxml_use_internal_errors(true);
					$dom = new DOMDocument();
					$dom->strictErrorChecking = FALSE;
					$dom->loadHTML(mb_convert_encoding($ext_file['body'], 'HTML-ENTITIES',  $charset));
					//$dom->loadHTML($ext_file['body']);
					$elements = $dom->getElementsByTagName('title');
					if ($elements->length > 0) {
						$title = trim($elements->item(0)->textContent);
					}

					libxml_use_internal_errors(false);
				}

				$title = htmlspecialchars($title);
			}

		}


		// édition d’un lien, dont l’ID est dans l’URL
		elseif (isset($_GET['id']) and preg_match('#\d{14}#', $_GET['id'])) {
			$get_id = '?id='.$editlink['bt_id'];
			$new_id = $editlink['bt_id'];
			$HTML .= hidden_input('is_it_edit', 'yes');
			$HTML .= hidden_input('ID', $editlink['ID']);
			$type = $editlink['bt_type'];
			$title = $editlink['bt_title'];
			$url = $editlink['bt_link'];
			$content = $editlink['bt_wiki_content'];
			$tags = $editlink['bt_tags'];
			$checked = (($editlink['bt_statut'] == 0) ? 'checked ' : '');
			$HTML_submit = "\t\t".'<button class="submit button-submit" type="submit" name="editer" id="valid-link" tabindex="4">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
			$HTML_suppr = "\t\t".'<button class="submit button-delete" type="button" name="supprimer" onclick="rmArticle(this)">'.$GLOBALS['lang']['supprimer'].'</button>'."\n";
		}



		$form .= '<form method="post" id="post-lien" action="'.basename($_SERVER['SCRIPT_NAME']).$get_id.'">'."\n";

		$form .= "\t".'<input type="text" name="url" value="'.$url.'" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_url']).'" size="50" class="text readonly-like" required="" inputmode="url" '.$url_hidden.' />'."\n";
		$form .= "\t".'<input type="text" name="title" value="'.$title.'" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" size="50" class="text" required="" autofocus tabindex="1" />'."\n";

		$form .= "\t".'<fieldset class="field">'."\n";
		$form .= form_formatting_toolbar(FALSE);
		$form .= "\t".'<textarea class="text description" name="description" cols="40" rows="7" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_description']).'" tabindex="2">'.$content.'</textarea>'."\n";
		$form .= "\t".'</fieldset>'."\n";


		$form .= "\t".'<div id="tag_bloc">'."\n";
		$form .= "\t\t".form_categories_links('links', $tags);
		$form .= "\t\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'" tabindex="3" />'."\n";
		$form .= "\t\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$form .= "\t".'</div>'."\n";
		$form .= "\t".'<p>'."\n";
		$form .= "\t\t".'<input type="checkbox" name="statut" id="statut" class="checkbox" '.$checked.' />'.'<label class="forcheckbox" for="statut">'.$GLOBALS['lang']['label_lien_priv'].'</label>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= $HTML_suppr;
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'links.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= $HTML_submit;
		$form .= "\t".'</p>'."\n";

		$form .= $HTML;
		$form .= hidden_input('bt_id', $new_id);
		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('type', $type);

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

	return $list;
}


// TRAITEMENT
$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$link = init_post_link2();
	$erreurs_form = valider_form_link($link);
	if (empty($erreurs_form)) {
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
afficher_topnav($GLOBALS['lang']['mesliens']); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	// Affichage formulaire filtrage liens
	afficher_form_filtre('links', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo "\t".'<div class="nombre-elem">';
	echo "\t\t".ucfirst(nombre_objets(count($tableau), 'link')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM links", array())."\n";
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

echo '<div id="page">'."\n";

// edit un lien : affiche le formulaire d’édition
if (isset($_GET['id']) and preg_match('#\d{14}#', $_GET['id']) and !empty($tableau[0]) ) {
	echo afficher_form_link($erreurs_form, $tableau[0]);
}
// ajout d’un lien : affiche le formulaire ajout.
elseif (isset($_GET['url'])) {
	echo afficher_form_link($erreurs_form);
}
// aucun lien à ajouter ou éditer : champ nouveau lien + listage des liens en dessus.
else {
	echo afficher_form_link($erreurs_form);
	echo '<div id="list-link">';
	foreach ($tableau as $link) {
		echo "\n".afficher_lien($link);
	}
	echo '</div>'."\n";
}

echo php_lang_to_js();
echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
echo '<script>'."\n";
if (!isset($_GET['url']) and !isset($_GET['id']) ) {
	echo 'document.getElementById(\'url\').addEventListener(\'focus\', function(){ document.getElementById(\'post-new-lien\').classList.add(\'focusedField\'); }, false);'."\n";
	echo 'document.getElementById(\'post-new-lien\').addEventListener(\'click\', function(){ document.getElementById(\'url\').focus(); }, false);'."\n";
	echo 'document.getElementById(\'url\').addEventListener(\'blur\', function(){ document.getElementById(\'post-new-lien\').classList.remove(\'focusedField\'); }, false);'."\n";
} else {
	echo 'handleTags(\'post-lien\', \'type_tags\', \'categories\', \'selected\');'."\n";
	echo 'new writeForm();'."\n";
}
echo '</script>';


footer($begin);

