<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

setcookie('lastAccessAgenda', time(), time()+365*24*60*60, null, null, true, true);


// Notes are send to browser in JSON format, rendering is done client side.
function send_agenda_json($events, $enclose_in_script_tag) {
	// events data
	$out = '['."\n";
	$count = count($events)-1;
	foreach ($events as $i => $event) {
		$out .= '{'.
			'"id": '.json_encode($event['bt_id']).', '.
			'"date": '.json_encode(date_format(date_create_from_format('YmdHis', $event['bt_date']), 'c')).', '.
			'"action": "", '.
			'"title": '.json_encode($event['bt_title']).', '.
			'"content": '.json_encode($event['bt_content']).', '.
			'"loc": '.json_encode($event['bt_event_loc']).

		'}'.(($count==$i) ? '' :',')."\n";
	}
	$out .= ']';
	if ($enclose_in_script_tag) {
		$out = '<script type="text/javascript">'."\n".'var Events = {"list": '.$out.'}'."\n".'</script>'."\n";
	}
	return $out;
}

// TRAITEMENT
$tableau = array();

// listing the events
if (!empty($_GET['q'])) {
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM agenda WHERE ".$sql_where."ORDER BY bt_date DESC";
	$tableau = liste_elements($query, $arr, 'agenda');
// no filter, send everything
} else {
	$query = "SELECT * FROM agenda ORDER BY bt_date DESC";
	$tableau = liste_elements($query, array(), 'agenda');
}

// count total nb of events
$nb_events_displayed = count($tableau);
$html_sub_menu = "\t".'<div id="sub-menu" class="sm-agenda">'."\n";
$html_sub_menu .= "\t\t".'<span id="count-posts"><span id="counter"></span></span>'."\n";
$html_sub_menu .= "\t\t".'<span id="message-return"></span>'."\n";
$html_sub_menu .= "\t\t".'<ul class="sub-menu-buttons agenda-menu-buttons">'."\n";
$html_sub_menu .= "\t\t\t".'<li><button class="submit button-submit" type="submit" name="enregistrer" id="enregistrer" disabled>'.$GLOBALS['lang']['enregistrer'].'</button></li>'."\n";
$html_sub_menu .= "\t\t".'</ul>'."\n";
$html_sub_menu .= "\t\t".'<button type="button" id="fab" class="add-event" title="'.$GLOBALS['lang']['label_event_ajout'].'">'.$GLOBALS['lang']['label_event_ajout'].'</button>'."\n";
$html_sub_menu .= "\t".'</div>'."\n";	


// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['monagenda'], "agenda"); // <head></head>
afficher_topnav($GLOBALS['lang']['monagenda'], $html_sub_menu); // #header #top

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	echo "\t".'<div class="nombre-elem">';
	echo "\t\t".ucfirst(nombre_objets($nb_events_displayed, 'event')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM agenda", array(), 'events')."\n";
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";

$out_html .= "\t".'<div id="cal-row">';
$out_html .= "\t\t".'<div id="calendar">';
$out_html .= "\t\t\t".'<div id="calendar_aside">';
$out_html .= "\t\t\t\t".'<div class="side_y">'.date('Y').'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="side_d">'.jour_en_lettres(date('d'), date('m'), date('Y'), true).'</div>'."\n";
$out_html .= "\t\t\t\t".'<div class="side_m">'.date('d').' '.mois_en_lettres(date('m'), true).'</div>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div id="calendar-wrapper"></div>'."\n"; // herein comes the calendar <table>
$out_html .= "\t\t".'</div><!-- end calendar-->'."\n";
$out_html .= "\t\t".'<div id="daily-events-wrapper"></div>'."\n";
$out_html .= "\t".'</div><!-- end cal-row-->'."\n";

$out_html .= "\t".'<div id="events-section">'."\n";
$out_html .= "\t\t".'<table id="event-list">'."\n";
$out_html .= "\t\t\t".'<thead>'."\n";
$out_html .= "\t\t\t\t".'<tr><th>'.$GLOBALS['lang']['label_date'].'</th><th>'.$GLOBALS['lang']['label_titre'].'</th><th>'.$GLOBALS['lang']['label_description'].'</th></tr>'."\n";
$out_html .= "\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t".'<tbody></tbody>'."\n";
$out_html .= "\t\t".'</table>'."\n";
$out_html .= "\t".'</div>'."\n";

$out_html .= send_agenda_json($tableau, true);

$out_html .= '<script src="style/javascript.js" type="text/javascript"></script>'."\n";
$out_html .= '<script type="text/javascript">'."\n";
$out_html .= php_lang_to_js(0)."\n";
$out_html .= 'var token = \''.new_token().'\';'."\n";

$out_html .= 'var initDate = new Date("'.date('Y').'", "'.(date('m')-1).'", "'.date('d').'");'."\n";
$out_html .= 'var Agenda = new EventAgenda();'."\n";

$out_html .= '</script>'."\n";

echo $out_html;

footer($begin);

