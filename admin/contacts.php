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
function send_contacts_json($contacts, $enclose_in_script_tag) {
	// Notes data
	$out = "\n".'['."\n";
	$count = count($contacts)-1;
	foreach ($contacts as $i => $c) {
		$out .= '{'.
			'"id":'.json_encode($c['bt_id']).', '.
			'"title":'.json_encode($c['bt_title']).', '.
			'"type":'.json_encode($c['bt_type']).', '.
			'"fullname":'.json_encode($c['bt_fullname']).', '.
			'"pseudo":'.json_encode($c['bt_surname']).', '.
			'"tel":'.json_encode(json_decode($c['bt_phone'])).', '.
			'"email":'.json_encode(json_decode($c['bt_email'])).', '.
			'"address":'.json_encode(json_decode($c['bt_address'])).', '.
			'"birthday":'.json_encode($c['bt_birthday']).', '.
			'"websites":'.json_encode(json_decode($c['bt_websites'])).', '.
			'"social":'.json_encode(json_decode($c['bt_social'])).', '.
			'"label":'.json_encode($c['bt_label']).', '.
			'"star":'.json_encode($c['bt_stared']).', '.
			'"notes":'.json_encode($c['bt_notes']).', '.
			'"other":'.json_encode($c['bt_other']).', '.
			'"img":'.json_encode($c['bt_image']).', '.
			'"imgIsNew": "", '.
			'"action": ""'.
		'}'.(($count==$i) ? '' :',')."\n";
	}
	$out .= ']'."\n";
	if ($enclose_in_script_tag) {
		$out = '<script id="json_contacts" type="application/json">'.$out.'</script>'."\n";
	}
	return $out;
}


// TRAITEMENT
$tableau = array();
// on affiche les contacts
if ( !empty($_GET['filtre']) ) {
	if (strpos($_GET['filtre'], 'label.') === 0) {
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		$query = "SELECT * FROM contacts WHERE bt_label=? ORDER BY LOWER(bt_fullname) ASC";
		$tableau = liste_elements($query, array($search), 'contacts');
	}
} elseif (!empty($_GET['q'])) { // mot clé
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_fullname || bt_surname ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM contacts WHERE ".$sql_where."ORDER BY LOWER(bt_fullname) ASC";
	$tableau = liste_elements($query, $arr, 'contacts');
} else { // aucun filtre : affiche TOUT
	$query = "SELECT * FROM contacts ORDER BY LOWER(bt_fullname) ASC";
	$tableau = liste_elements($query, array(), 'contacts');
}










// count total nb of notes
$nb_notes_displayed = count($tableau);
$html_sub_menu = '<div id="sub-menu" class="sm-contacts">'."\n";
$html_sub_menu .= "\t".'<span id="count-posts"><span id="counter"></span></span>'."\n";
$html_sub_menu .= "\t".'<span id="message-return"></span>'."\n";
$html_sub_menu .= "\t".'<ul class="contacts-menu-buttons sub-menu-buttons">'."\n";
$html_sub_menu .= "\t\t".'<li><button class="submit button-submit" type="submit" name="enregistrer" id="enregistrer" disabled>'.$GLOBALS['lang']['enregistrer'].'</button></li>'."\n";
$html_sub_menu .= "\t".'</ul>'."\n";
$html_sub_menu .= '</div>'."\n";


// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['mescontacts'], "contacts");
afficher_topnav($GLOBALS['lang']['mescontacts'], $html_sub_menu); #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	afficher_form_filtre('contacts', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo "\t".'<div class="nombre-elem">';
	echo "\t\t".ucfirst(nombre_objets($nb_notes_displayed, 'contact')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM contacts", array())."\n";
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";

$out_html .= "\t".'<div id="popup-wrapper" hidden>'."\n";

$out_html .= "\t\t".'<form class="popup-edit-contact" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="contact-title">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";

$out_html .= "\t\t\t".'<div class="contact-content">'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-img-fullname">'."\n";
$out_html .= "\t\t\t\t\t".'<span class="contact-img"></span>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-title"><input type="text" class="text" name="contact-title" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-title'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-fullname"><input type="text" class="text" name="contact-fullname" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-fullname'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-label">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-label"><input type="text" class="text" name="contact-label" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-label'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<p>'.$GLOBALS['lang']['label_coordonnees'].'</p>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-emails">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-email"><input type="email" class="text" name="contact-email" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-email'].'</span><button type="button" class="rem"></button><button type="button" class="add"></button></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-phones">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-phone"><input type="tel" class="text" name="contact-phone" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-phone'].'</span><button type="button" class="rem"></button><button type="button" class="add"></button></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-address">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-nb"><input type="text" class="text" name="contact-nb" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-nr'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-st"><input type="text" class="text" name="contact-st" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-street'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-co"><input type="text" class="text" name="contact-co" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-complement'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-cp"><input type="text" class="text" name="contact-cp" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-cpzip'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-ci"><input type="text" class="text" name="contact-ci" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-city'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-sa"><input type="text" class="text" name="contact-sa" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-state'].'</span></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-cn"><input type="text" class="text" name="contact-cn" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-country'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<p class="onshowmore">'.$GLOBALS['lang']['label_profil'].'</p>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-surname onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-surname"><input type="text" class="text" name="contact-surname" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-surname'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-birthday onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-birthday"><input type="date" class="text" name="contact-birthday" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-birthday'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-links onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-links"><input type="url" class="text" name="contact-links" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-link'].'</span><button type="button" class="rem"></button><button type="button" class="add"></button></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-social onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-social"><input type="url" class="text" name="contact-social" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-socialmedia'].'</span><button type="button" class="rem"></button><button type="button" class="add"></button></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<p class="onshowmore">'.$GLOBALS['lang']['label-ctc-other'].'</p>'."\n";

$out_html .= "\t\t\t\t".'<div class="contact-notes onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-notes"><input type="text" class="text" name="contact-notes" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-notes'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-other onshowmore">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="contact-other"><input type="text" class="text" name="contact-other" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label-ctc-other'].'</span></label>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";

$out_html .= "\t\t\t".'</div>'."\n";

$out_html .= "\t\t\t".'<div class="contact-footer">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-showmore" type="button" name="showmore">PLUS</button>'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-submit" type="button" name="save">'.$GLOBALS['lang']['enregistrer'].'</button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";


$out_html .= "\t\t".'</form>'."\n";



$out_html .= "\t\t".'<div class="popup-contact" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="contact-title">'."\n";
$out_html .= "\t\t\t\t".'<div class="contact-img-name">'."\n";
$out_html .= "\t\t\t\t\t".'<span class="contact-img"></span>'."\n";
$out_html .= "\t\t\t\t\t".'<span class="contact-name"></span>'."\n";
$out_html .= "\t\t\t\t\t".'<span class="contact-label"></span>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="item-menu-options">'."\n";
$out_html .= "\t\t\t\t\t".'<ul><li><a>'.$GLOBALS['lang']['supprimer'].'</a></li></ul>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t\t".'<button class="button-edit"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="contact-content">'."\n";
$out_html .= "\t\t\t\t".'<div>'.$GLOBALS['lang']['label_coordonnees'].'</div>'."\n";
$out_html .= "\t\t\t\t".'<ul class="contact-content-coord">'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-names"><span></span></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-emails"><a href=""></a></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-phones"><a href=""></a></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-address"><span></span><span></span><span></span><span></span></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t\t".'<div>'.$GLOBALS['lang']['label_profil'].'</div>'."\n";
$out_html .= "\t\t\t\t".'<ul class="contact-content-profile">'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-birthday"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-links"><a href=""></a></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-social"><a href=""></a></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t\t".'<div>'.$GLOBALS['lang']['label-ctc-other'].'</div>'."\n";
$out_html .= "\t\t\t\t".'<ul class="contact-content-misc">'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-notes"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="contact-other"></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</div>'."\n";

$out_html .= "\t".'</div>'."\n";



$out_html .= "\t".'<div id="list-contacts">'."\n";
$out_html .= "\t\t".'<table id="table-contacts">'."\n";
$out_html .= "\t\t".'<thead>'."\n";
$out_html .= "\t\t\t".'<tr>'."\n";
$out_html .= "\t\t\t\t".'<th class="icon"></th>'."\n";
$out_html .= "\t\t\t\t".'<th class="name">'.$GLOBALS['lang']['label-ctc-fullname'].'</th>'."\n";
$out_html .= "\t\t\t\t".'<th class="tel">'.$GLOBALS['lang']['label-ctc-phone'].'</th>'."\n";
$out_html .= "\t\t\t\t".'<th class="email">'.$GLOBALS['lang']['label-ctc-email'].'</th>'."\n";
$out_html .= "\t\t\t\t".'<th class="label">'.$GLOBALS['lang']['label-ctc-label'].'</th>'."\n";
$out_html .= "\t\t\t\t".'<th class="buttons"></th>'."\n";
$out_html .= "\t\t\t".'</tr>'."\n";
$out_html .= "\t\t".'</thead>'."\n";
$out_html .= "\t\t".'<tbody>'."\n";
$out_html .= "\t\t\t".'<tr data-id="">'."\n";
$out_html .= "\t\t\t\t".'<td class="icon"><span></span></td>'."\n";
$out_html .= "\t\t\t\t".'<td class="name"></td>'."\n";
$out_html .= "\t\t\t\t".'<td class="tel"><a href=""></a></td>'."\n";
$out_html .= "\t\t\t\t".'<td class="email"><a href=""></a></td>'."\n";
$out_html .= "\t\t\t\t".'<td class="label"><span></span></td>'."\n";
$out_html .= "\t\t\t\t".'<td class="buttons"><button class="button-edit" type="button" title="'.$GLOBALS['lang']['editer'].'"></button></td>'."\n";
$out_html .= "\t\t\t".'</tr>'."\n";
$out_html .= "\t\t".'</tbody>'."\n";
$out_html .= "\t\t".'</table>'."\n";
$out_html .= "\t".'</div>'."\n";
$out_html .= "\t".'<button type="button" id="fab" class="add-contact" title="'.$GLOBALS['lang']['rss_label_config'].'">'.$GLOBALS['lang']['rss_label_addfeed'].'</button>'."\n";

$out_html .= send_contacts_json($tableau, true);
$out_html .= php_lang_to_js()."\n";
$out_html .= "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
$out_html .= '<script>'."\n";
$out_html .= 'var token = \''.new_token().'\';'."\n";
$out_html .= 'new ContactsList();'."\n";
$out_html .= '</script>'."\n";

echo $out_html;

footer($begin);