<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();


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
	$list .= ($link['bt_statut'] == '1') ? "\t\t\t".'<li><a href="'.$GLOBALS['racine'].'?mode=links&amp;id='.$link['bt_id'].'">'.$GLOBALS['lang']['voir_sur_le_blog'].'</a></li>'."\n" : "";
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
			$tableau = liste_elements($query, array($_GET['filtre'].'%'), 'links');
		// visibles & brouillons
		} elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') {
			$query = "SELECT * FROM links WHERE bt_statut=? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)), 'links');
		// tags
		} elseif (strpos($_GET['filtre'], 'tag.') === 0) {
			$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
			$query = "SELECT * FROM links WHERE bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? ORDER BY bt_id DESC";
			$tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'), 'links');
		} else {
			$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT ".$GLOBALS['max_linx_admin'];
			$tableau = liste_elements($query, array(), 'links');
		}
	// keyword
	} elseif (!empty($_GET['q'])) {
		$arr = parse_search($_GET['q']);
		$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title || bt_link ) LIKE ? '), 'AND ');
		$query = "SELECT * FROM links WHERE ".$sql_where."ORDER BY bt_id DESC";
		$tableau = liste_elements($query, $arr, 'links');
	// editing a specific link
	} elseif (!empty($_GET['id']) and is_numeric($_GET['id'])) {
		$query = "SELECT * FROM links WHERE bt_id=?";
		$tableau = liste_elements($query, array($_GET['id']), 'links');
	// no filter, show em all
	} else {
		$query = "SELECT * FROM links ORDER BY bt_id DESC LIMIT 0, ".$GLOBALS['max_linx_admin'];
		$tableau = liste_elements($query, array(), 'links');
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
		echo "\t\t".ucfirst(nombre_objets(count($tableau), 'link')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM links", array(), 'links')."\n";
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

echo "\n".'<script src="style/javascript.js" type="text/javascript"></script>'."\n";
echo '<script type="text/javascript">'."\n";
echo php_lang_to_js(0)."\n";

if ($step == 1) {
	echo 'document.getElementById(\'url\').addEventListener(\'focus\', function(){ document.getElementById(\'post-new-lien\').classList.add(\'focusedField\'); }, false);'."\n";
	echo 'document.getElementById(\'post-new-lien\').addEventListener(\'click\', function(){ document.getElementById(\'url\').focus(); }, false);'."\n";
	echo 'document.getElementById(\'url\').addEventListener(\'blur\', function(){ document.getElementById(\'post-new-lien\').classList.remove(\'focusedField\'); }, false);'."\n";

}
echo '</script>';

footer($begin);

