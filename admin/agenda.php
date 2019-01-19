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
	$out = "\n".'['."\n";
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
	$out .= ']'."\n";
	if ($enclose_in_script_tag) {
		$out = '<script id="json_agenda" type="application/json">'.$out.'</script>'."\n";
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
	$tableau = liste_elements($query, $arr);
// no filter, send everything
} else {
	$query = "SELECT * FROM agenda ORDER BY bt_date DESC";
	$tableau = liste_elements($query, array());
}

// count total nb of events
$nb_events_displayed = count($tableau);
$html_sub_menu = "\t".'<div id="sub-menu" class="sm-agenda">'."\n";
$html_sub_menu .= "\t\t".'<span id="count-posts"><span id="counter"></span></span>'."\n";
$html_sub_menu .= "\t\t".'<span id="message-return"></span>'."\n";
$html_sub_menu .= "\t\t\t".'<span id="current_date">'.date_formate(date('YmdHis')).'</span>'."\n";
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
	echo "\t\t".ucfirst(nombre_objets($nb_events_displayed, 'event')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(*) AS nbr FROM agenda", array())."\n";
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";
$out_html .= "\t".'<div id="popup-wrapper" hidden>'."\n";

$out_html .= "\t\t".'<form class="popup-edit-event" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="event-title">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t\t".'<input type="text" class="text" name="itemTitle" required="" placeholder="'.$GLOBALS['lang']['label_add_title'].'">'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content">'."\n";
$out_html .= "\t\t\t".'<div class="event-content-date">'."\n";
$out_html .= "\t\t\t\t".'<p><input type="checkbox" name="allDay" id="allDay" class="checkbox-toggle"><label for="allDay">'.$GLOBALS['lang']['question_entire_day'].'</label></p>'."\n";
$out_html .= "\t\t\t\t".'<p><input class="text" type="date" required="" name="date" id="date"><input class="text" type="time" required="" name="time" id="time"></p>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content-loc">'."\n";
$out_html .= "\t\t\t\t".'<input placeholder="'.$GLOBALS['lang']['label_add_location'].'" type="text" class="text" name="loc">'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content-descr">'."\n";
$out_html .= "\t\t\t\t".'<textarea placeholder="'.$GLOBALS['lang']['label_add_description'].'" cols="30" rows="3" class="text" name="descr"></textarea>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-footer">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-submit" type="submit" name="editer">'.$GLOBALS['lang']['enregistrer'].'</button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</form>'."\n";

$out_html .= "\t\t".'<div class="popup-event" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="event-title">'."\n";
$out_html .= "\t\t\t\t".'<span></span>'."\n";
$out_html .= "\t\t\t\t".'<div class="item-menu-options">'."\n";
$out_html .= "\t\t\t\t\t".'<ul>'."\n";
$out_html .= "\t\t\t\t\t\t".'<li><a>'.$GLOBALS['lang']['supprimer'].'</a></li>'."\n";
$out_html .= "\t\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t\t".'<button class="button-edit"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content">'."\n";
$out_html .= "\t\t\t\t".'<ul>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-time"><span></span><span></span></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-loc"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-description"></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</div>'."\n";

$out_html .= "\t".'</div>'."\n";
$out_html .= "\t".'<div id="cal-row">';
$out_html .= "\t\t".'<div id="calendar">';
$out_html .= "\t\t\t".'<div id="calendar-wrapper">'."\n";
$out_html .= "\t\t\t\t".'<table id="calendar-table" class="table-month-mode">'."\n";
$out_html .= "\t\t\t\t\t".'<thead class="month-mode">'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="monthrow"><td id="changeYear" colspan="4"><button id="show-full-year"></button><span></span></td><td id="month" colspan="3"><button id="prev-month"></button><button id="next-month"></button></td></tr>'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="dayAbbr">'; for ($i=0 ; $i<7 ; $i++) { $out_html .= '<th>'.$GLOBALS['lang']['days_abbr_narrow'][$i].'</th>';} $out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t\t".'<tbody class="month-mode"></tbody>'."\n";
$out_html .= "\t\t\t\t\t".'<thead class="year-mode">'."\n";
$out_html .= "\t\t\t\t\t\t".'<tr class="monthrow">'."\n";
$out_html .= "\t\t\t\t\t\t\t".'<td id="year" colspan="4"><button id="prev-year"></button><span></span><button id="next-year"></button></td>'."\n";
$out_html .= "\t\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t\t".'<tbody class="year-mode"></tbody>'."\n";
$out_html .= "\t\t\t\t".'</table>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</div><!-- end calendar-->'."\n";
$out_html .= "\t\t".'<div id="daily-events-wrapper">'."\n";
$out_html .= "\t\t\t".'<p>'."\n";
$out_html .= "\t\t\t\t".'<select id="filter-events">'."\n";
$out_html .= "\t\t\t\t\t".'<option value="'.date('c').'">'.date_formate(date('Ymdis')).'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="today" selected>'.ucfirst($GLOBALS['lang']['aujourdhui']).'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="tomonth">'.$GLOBALS['lang']['cemois'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="toyear">'.$GLOBALS['lang']['cetteannee'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="all">'.$GLOBALS['lang']['label_all_events'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="past">'.$GLOBALS['lang']['label_past_events'].'</option>'."\n";
$out_html .= "\t\t\t\t".'</select>'."\n";
$out_html .= "\t\t\t".'</p>'."\n";
$out_html .= "\t\t\t".'<div id="daily-events">'."\n";
$out_html .= "\t\t\t\t".'<div data-index-id="" class="" hidden>'."\n";
$out_html .= "\t\t\t\t\t".'<div class="eventDate">'."\n";
$out_html .= "\t\t\t\t\t\t".'<span class="event-dd"></span><span class="event-mmdd"></span><span class="event-hhii"></span>'."\n";
$out_html .= "\t\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t\t".'<div class="eventSummary">'."\n";
$out_html .= "\t\t\t\t\t\t".'<span class="title"></span><span class="content"></span><span class="loc"></span>'."\n";
$out_html .= "\t\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";

$out_html .= "\t\t".'</div>'."\n";
$out_html .= "\t".'</div><!-- end cal-row-->'."\n";

$out_html .= send_agenda_json($tableau, true); // 1
$out_html .= php_lang_to_js(); // 2
$out_html .= '<script src="style/scripts/javascript.js"></script>'."\n"; // 3
$out_html .= '<script>'."\n"; // 4
$out_html .= 'var token = \''.new_token().'\';'."\n";
$out_html .= 'new EventAgenda();'."\n";
$out_html .= '</script>'."\n";

echo $out_html;

footer($begin);

