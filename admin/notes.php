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
	$out = '['."\n";
	$count = count($notes)-1;
	foreach ($notes as $i => $n) {
		$out .= '{'.
			'"id":'.json_encode($n['bt_id']).', '.
			'"color":'.json_encode($n['bt_color']).', '.
			'"title":'.json_encode($n['bt_title']).', '.
			'"content":'.json_encode($n['bt_content']).', '.
			'"action":'.json_encode('').
		'}'.(($count==$i) ? '' :',')."\n";
	}
	$out .= ']';
	if ($enclose_in_script_tag) {
		$out = '<script type="text/javascript">'.'var Notes = {"list": '.$out."\n".'}'.'</script>'."\n";
	}
	return $out;
}


// TRAITEMENT
$tableau = array();
// on affiche les notes
if ( !empty($_GET['filtre']) and preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) { // date
	$query = "SELECT * FROM notes WHERE bt_id LIKE ? ORDER BY bt_id DESC";
	$tableau = liste_elements($query, array($_GET['filtre'].'%'), 'links');
} elseif (!empty($_GET['q'])) { // mot clé
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM notes WHERE ".$sql_where."ORDER BY bt_id DESC";
	$tableau = liste_elements($query, $arr, 'notes');
} else { // aucun filtre : affiche TOUT
	$query = "SELECT * FROM notes ORDER BY bt_id";
	$tableau = liste_elements($query, array(), 'notes');
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
	echo "\t".'<div class="nombre-elem">';
	echo "\t\t".ucfirst(nombre_objets($nb_notes_displayed, 'note')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM notes", array(), 'notes')."\n";
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";

$out_html .= '<div id="post-new-note">'."\n";
$out_html .= "\t".'<div class="contain">'.$GLOBALS['lang']['label_note_ajout'].'</div>'."\n";
$out_html .= '</div>'."\n\n";

$out_html .= '<div id="list-notes">'."\n";
$out_html .= send_notes_json($tableau, true);
$out_html .= '</div>'."\n";

$out_html .= "\n".'<script src="style/javascript.js" type="text/javascript"></script>'."\n";
$out_html .= '<script type="text/javascript">'."\n";
$out_html .= 'var token = \''.new_token().'\';'."\n";
$out_html .= 'var NotesWall = new NoteBlock();'."\n";
$out_html .= php_lang_to_js(0)."\n";
$out_html .= '</script>';

echo $out_html;

footer($begin);