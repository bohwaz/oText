<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

// Notes are send to browser in JSON format, rendering is done client side.
function send_notes_json($notes, $enclose_in_script_tag) {
	// Notes data
	$out = "\n".'['."\n";
	$count = count($notes)-1;
	foreach ($notes as $i => $n) {
		$out .= '{'.
			'"id":'.json_encode($n['bt_id']).', '.
			'"color":'.json_encode($n['bt_color']).', '.
			'"title":'.json_encode($n['bt_title']).', '.
			'"content":'.json_encode($n['bt_content']).', '.
			'"isstatut":'.json_encode($n['bt_statut']).', '.
			'"ispinned":'.json_encode($n['bt_pinned']).', '.
			'"action":'.json_encode('').
		'}'.(($count==$i) ? '' :',')."\n";
	}
	$out .= ']'."\n";
	if ($enclose_in_script_tag) {
		$out = '<script id="json_notes" type="application/json">'.$out.'</script>'."\n";
	}
	return $out;
}


// TRAITEMENT
$tableau = array();
// on affiche les notes
if ( !empty($_GET['filtre'])) {
	// par date
	if (preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre'])) {
		$query = "SELECT * FROM notes WHERE bt_id LIKE ? ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array($_GET['filtre'].'%'));
	}
	// statut (archive / pas archive)
	elseif ($_GET['filtre'] == 'archived' or $_GET['filtre'] == 'pub') {
		$query = "SELECT * FROM notes WHERE bt_statut=? ORDER BY bt_id DESC";
		$tableau = liste_elements($query, array((($_GET['filtre'] == 'archived') ? 0 : 1)));
	}
} elseif (!empty($_GET['q'])) { // mot clé
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM notes WHERE ".$sql_where."ORDER BY bt_id DESC";
	$tableau = liste_elements($query, $arr);
} else { // aucun filtre
	$query = "SELECT * FROM notes WHERE bt_statut = 1 ORDER BY bt_id";
	$tableau = liste_elements($query, array());
}

// count total nb of notes
$nb_notes_displayed = count($tableau);
$html_sub_menu = '<div id="sub-menu" class="sm-notes">'."\n";
$html_sub_menu .= "\t".'<span id="count-posts"><span id="counter"></span></span>'."\n";
$html_sub_menu .= "\t".'<span id="message-return"></span>'."\n";
$html_sub_menu .= "\t".'<ul class="notes-menu-buttons sub-menu-buttons">'."\n";
$html_sub_menu .= "\t\t".'<li><button class="submit button-submit" type="submit" name="enregistrer" id="enregistrer" disabled>'.$GLOBALS['lang']['enregistrer'].'</button></li>'."\n";
$html_sub_menu .= "\t".'</ul>'."\n";
$html_sub_menu .= '</div>'."\n";


// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['mesnotes'], "notes");
afficher_topnav($GLOBALS['lang']['mesnotes'], $html_sub_menu); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	afficher_form_filtre('notes', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo "\t".'<div class="nombre-elem">'.ucfirst(nombre_objets($nb_notes_displayed, 'note')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM notes", array()).'</div>'."\n";
echo '</div>'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";

$out_html .= "\t".'<div id="popup-wrapper" hidden>'."\n";
$out_html .= "\t\t".'<div id="popup" class="popup-note" style="" data-ispinned="" data-isarchived="">'."\n";
$out_html .= "\t\t\t".'<div class="popup-title">'."\n";
$out_html .= "\t\t\t\t".'<h2 contenteditable="true">Title</h2>'."\n";
$out_html .= "\t\t\t\t".'<button type="button" class="archiveIcon" title="'.$GLOBALS['lang']['archiver'].'"></button>'."\n";
$out_html .= "\t\t\t\t".'<button type="button" class="pinnedIcon" title="'.$GLOBALS['lang']['epingler'].'"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<textarea class="popup-content" cols="30" rows="8" placeholder="Content"></textarea>'."\n";
$out_html .= "\t\t\t".'<div class="popup-footer">'."\n";
$out_html .= "\t\t\t\t".'<div class="date"></div>'."\n";
$out_html .= "\t\t\t\t".'<button type="button" class="colorIcon"></button>'."\n";
$out_html .= "\t\t\t\t".'<ul class="colors">'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(255, 255, 255);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(255, 138, 128);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(255, 209, 128);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(255, 255, 141);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(204, 255, 144);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(167, 255, 235);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(128, 216, 255);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(130, 177, 255);"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li style="background-color: rgb(248, 187, 208);"></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t\t".'<button type="button" class="supprIcon"></button>'."\n";
$out_html .= "\t\t\t\t".'<span class="submit-bttns">'."\n";
$out_html .= "\t\t\t\t\t".'<button class="submit button-cancel" type="button">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
$out_html .= "\t\t\t\t\t".'<button class="submit button-submit" type="button" name="editer">'.$GLOBALS['lang']['enregistrer'].'</button>'."\n";
$out_html .= "\t\t\t\t".'</span>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</div>'."\n";
$out_html .= "\t".'</div>'."\n";

$out_html .= "\t".'<div id="post-new-note">'."\n";
$out_html .= "\t\t".'<div class="contain">'.$GLOBALS['lang']['label_note_ajout'].'</div>'."\n";
$out_html .= "\t".'</div>'."\n\n";

$out_html .= "\t".'<div id="list-notes">'."\n";
// note template
$out_html .= "\t\t".'<div id="n_" data-update-action="" class="notebloc" style="background-color: rgb(255, 255, 255);" data-index-id="" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="title">'."\n";
$out_html .= "\t\t\t\t".'<h2>Title</h2>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="content" data-id=""></div>'."\n";
$out_html .= "\t\t".'</div>'."\n";

$out_html .= "\t".'<h2 id="are-pinned" hidden>Notes épinglées</h2>'."\n";
$out_html .= "\t".'<h2 id="are-unpinned">Autres</h2>'."\n";

$out_html .= "\t".'</div>'."\n";

$out_html .= send_notes_json($tableau, true);
$out_html .= php_lang_to_js()."\n";
$out_html .= "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
$out_html .= '<script>'."\n";
$out_html .= 'var token = \''.new_token().'\';'."\n";
$out_html .= 'new NoteBlock();'."\n";
$out_html .= '</script>'."\n";

echo $out_html;

footer($begin);