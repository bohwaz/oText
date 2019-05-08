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
			'"date_start": '.json_encode($event['bt_date_start']).', '.
			'"date_end": '.json_encode($event['bt_date_end']).', '.
			'"action": "", '.
			'"title": '.json_encode($event['bt_title']).', '.
			'"color": '.json_encode($event['bt_color']).', '.
			'"content": '.json_encode($event['bt_content']).', '.
			'"persons": '.json_encode(json_decode($event['bt_persons'])).', '.
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
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title || bt_event_loc ) LIKE ? '), 'AND '); // AND operator between words
	$query = "SELECT * FROM agenda WHERE ".$sql_where."ORDER BY bt_date_start ASC";
	$GLOBALS['agenda_display'] = 'eventlist';
	$tableau = liste_elements($query, $arr);
// no filter, send everything
} else {
	$query = "SELECT * FROM agenda ORDER BY bt_date_start ASC";
	$tableau = liste_elements($query, array());
}

// count total nb of events
$nb_events_displayed = count($tableau);

// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['monagenda'], "agenda"); // <head></head>
afficher_topnav($GLOBALS['lang']['monagenda'], ''); // #header #top

echo '<div id="axe">'."\n";

$out_html = '';
$out_html .= '<div id="page">'."\n";

// side nav
$out_html .= "\t".'<div id="side-nav">'."\n";
$out_html .= "\t\t\t".'<table id="mini-calendar-table">'."\n";
$out_html .= "\t\t\t\t".'<thead>'."\n";
$out_html .= "\t\t\t\t".'<tr class="monthrow"><td colspan="4"><span></span></td><td colspan="3"><button id="mini-prev-month"></button><button id="mini-next-month"></button></td></tr>'."\n";
$out_html .= "\t\t\t\t".'<tr class="dayAbbr">'; for ($i=0 ; $i<7 ; $i++) { $out_html .= '<th>'.$GLOBALS['lang']['days_abbr_narrow'][$i].'</th>';} $out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t".'<tbody></tbody>'."\n";
$out_html .= "\t\t\t".'</table>'."\n";
$options = array('eventCalendar'=> $GLOBALS['lang']['pref_agenda_taskcalendar'], 'eventlist'=> $GLOBALS['lang']['pref_agenda_tasklist']);
$out_html .= form_select('cal-size', $options, $GLOBALS['agenda_display'], '');

$a = explode('/', dirname($_SERVER['SCRIPT_NAME']));
$out_html .= '<p class="ical-link"><button type="button" onclick="prompt(\''.$GLOBALS['lang']['pref_agenda_ical_link'].'\', \''.$GLOBALS['racine'].$a[count($a)-1].'/ajax/agenda.ajax.php?guid='.BLOG_UID.'&get_ics'.'\');">'.$GLOBALS['lang']['pref_agenda_show_ical_link'].'</button></p>'."\n";
$out_html .= "\t".'</div>'."\n";

$out_html .= "\t".'<div id="popup-wrapper" hidden>'."\n";

$out_html .= "\t\t".'<form class="popup-edit-event" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="popup-title event-title">'."\n";
$out_html .= "\t\t\t\t".'<button type="button" class="event-color"></button>'."\n";
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
$out_html .= "\t\t\t\t".'<input type="text" class="text" name="itemTitle" required="" placeholder="'.$GLOBALS['lang']['label_add_title'].'">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="popup-content event-content">'."\n";
$out_html .= "\t\t\t".'<div class="event-content-date">'."\n";
$out_html .= "\t\t\t\t".'<p><input type="checkbox" name="allDay" id="allDay" class="checkbox-toggle"><label for="allDay">'.$GLOBALS['lang']['question_entire_day'].'</label></p>'."\n";
$out_html .= "\t\t\t\t".'<p class="date-time-input">'."\n";
$out_html .= "\t\t\t\t\t".'<label for="date"><input class="text" type="date" required="" name="date" id="date"></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="time-start"><input class="text" type="time" required="" name="time-start" id="time-start"></label>'."\n";
$out_html .= "\t\t\t\t\t".'<label for="time-end"><input class="text" type="time" required="" name="time-end" id="time-end"></label>'."\n";
$out_html .= "\t\t\t\t".'</p>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content-loc">'."\n";
$out_html .= "\t\t\t\t".'<label for="event-loc"><input type="text" class="text" name="event-loc" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label_add_location'].'</span></label>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<datalist id="htmlListContacts">'."\n";
$tab_contacts = nb_entries_as('contacts', 'bt_label');
foreach ($tab_contacts as $contact) {
	if (isset($contact['bt_label']) and !empty($contact['bt_label']) ) $out_html .= "\t\t\t\t".'<option value="'.htmlspecialchars($contact['bt_label']).'">(groupe) '.htmlspecialchars($contact['bt_label']).'</option>'."\n";
}
$tab_contacts = nb_entries_as('contacts', 'bt_fullname');
foreach ($tab_contacts as $contact) {
	if (isset($contact['bt_fullname']) and !empty($contact['bt_fullname']) ) $out_html .= "\t\t\t\t".'<option value="'.htmlspecialchars($contact['bt_fullname']).'">'."\n";
}
$out_html .= "\t\t\t".'</datalist>'."\n";
$out_html .= "\t\t\t".'<div class="event-content-persons">'."\n";
$out_html .= "\t\t\t\t".'<ul id="event-content-persons-selected"><li class="tag"><span></span><a href="#">×</a></li></ul>'."\n";
$out_html .= "\t\t\t\t".'<label for="event-persons"><input list="htmlListContacts" type="text" class="text" name="event-persons" value="" placeholder=" " /><span>'.$GLOBALS['lang']['label_add_persons'].'</span></label>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="event-content-descr">'."\n";
$out_html .= "\t\t\t\t".'<label for="event-descr"><textarea type="text" class="text" name="event-descr" placeholder=" "></textarea><span>'.$GLOBALS['lang']['label_add_description'].'</span></label>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="popup-footer event-footer">'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-submit" type="submit" name="editer">'.$GLOBALS['lang']['enregistrer'].'</button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</form>'."\n";

$out_html .= "\t\t".'<div class="popup-event" hidden>'."\n";
$out_html .= "\t\t\t".'<div class="popup-title event-title">'."\n";
$out_html .= "\t\t\t\t".'<span class="event-color"></span>'."\n";
$out_html .= "\t\t\t\t".'<span class="event-name"></span>'."\n";
$out_html .= "\t\t\t\t".'<div class="item-menu-options">'."\n";
$out_html .= "\t\t\t\t\t".'<ul>'."\n";
$out_html .= "\t\t\t\t\t\t".'<li><a class="button-edit">'.$GLOBALS['lang']['editer'].'</a></li>'."\n";
$out_html .= "\t\t\t\t\t\t".'<li><a class="button-suppr">'.$GLOBALS['lang']['supprimer'].'</a></li>'."\n";
$out_html .= "\t\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'<button class="submit button-cancel" type="button"></button>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'<div class="popup-content event-content">'."\n";
$out_html .= "\t\t\t\t".'<ul>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-time"><span></span><span></span></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-loc"></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-persons"><span></span></li>'."\n";
$out_html .= "\t\t\t\t\t".'<li class="event-description"></li>'."\n";
$out_html .= "\t\t\t\t".'</ul>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";
$out_html .= "\t\t".'</div>'."\n";

$out_html .= "\t".'</div>'."\n";
$out_html .= "\t".'<div id="cal-sizer" class="'.($GLOBALS['agenda_display']).'">';

$out_html .= "\t\t".'<div id="calendar-wrapper">'."\n";
$out_html .= "\t\t\t".'<table id="calendar-table" class="table-month-mode">'."\n";
$out_html .= "\t\t\t\t".'<thead class="day-mode">'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="monthrow">'."\n";
$out_html .= "\t\t\t\t\t\t".'<td id="changeMonth" colspan="4"><button id="show-full-month"></button><span></span></td>'."\n";
$out_html .= "\t\t\t\t\t\t".'<td id="day" colspan="3"><button id="prev-day"></button><span></span><button id="next-day"></button></td>'."\n";
$out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t".'<tbody class="day-mode"></tbody>'."\n";
$out_html .= "\t\t\t\t".'<thead class="month-mode">'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="monthrow">'."\n";
$out_html .= "\t\t\t\t\t\t".'<td id="changeYear" colspan="4"><button id="show-full-year"></button><span></span></td>'."\n";
$out_html .= "\t\t\t\t\t\t".'<td id="month" colspan="3"><button id="prev-month"></button><span></span><button id="next-month"></button></td>'."\n";
$out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="dayAbbr">'; for ($i=0 ; $i<7 ; $i++) { $out_html .= '<th>'.$GLOBALS['lang']['days_abbr_narrow'][$i].'</th>';} $out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t".'<tbody class="month-mode"></tbody>'."\n";
$out_html .= "\t\t\t\t".'<thead class="year-mode">'."\n";
$out_html .= "\t\t\t\t\t".'<tr class="monthrow">'."\n";
$out_html .= "\t\t\t\t\t\t".'<td id="year" colspan="4"><button id="prev-year"></button><span></span><button id="next-year"></button></td>'."\n";
$out_html .= "\t\t\t\t\t".'</tr>'."\n";
$out_html .= "\t\t\t\t".'</thead>'."\n";
$out_html .= "\t\t\t\t".'<tbody class="year-mode"></tbody>'."\n";
$out_html .= "\t\t\t".'</table>'."\n";
$out_html .= "\t\t".'</div>'."\n";

$out_html .= "\t\t".'<div id="daily-events-wrapper">'."\n";
$out_html .= "\t\t\t".'<p>'."\n";
$out_html .= "\t\t\t\t".'<select id="filter-events">'."\n";
$out_html .= "\t\t\t\t\t".'<option value="'.date('c').'">'.date_formate(date('Ymdis')).'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="futur" selected>'.ucfirst($GLOBALS['lang']['label_future_events']).'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="today">'.ucfirst($GLOBALS['lang']['aujourdhui']).'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="tomonth">'.$GLOBALS['lang']['cemois'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="toyear">'.$GLOBALS['lang']['cetteannee'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="all">'.$GLOBALS['lang']['label_all_events'].'</option>'."\n";
$out_html .= "\t\t\t\t\t".'<option value="past">'.$GLOBALS['lang']['label_past_events'].'</option>'."\n";
$out_html .= "\t\t\t\t".'</select>'."\n";
$out_html .= "\t\t\t".'</p>'."\n";
$out_html .= "\t\t\t".'<div id="daily-events">'."\n";
$out_html .= "\t\t\t\t".'<div data-id="" data-date="" class="" hidden>'."\n";
$out_html .= "\t\t\t\t\t".'<div class="eventDate">'."\n";
$out_html .= "\t\t\t\t\t\t".'<span class="event-dd"></span><span class="event-mmdd"></span><span class="event-hhii"></span>'."\n";
$out_html .= "\t\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t\t".'<div class="eventSummary">'."\n";
$out_html .= "\t\t\t\t\t\t".'<span class="color"></span><span class="title"></span><span class="content"></span><span class="loc"></span>'."\n";
$out_html .= "\t\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t\t".'</div>'."\n";
$out_html .= "\t\t\t".'</div>'."\n";

$out_html .= "\t\t".'</div>'."\n";
$out_html .= "\t".'</div>'."\n";


// (+) button, fab
$out_html .= "\t\t".'<button type="button" id="fab" class="add-event" title="'.$GLOBALS['lang']['label_event_ajout'].'">'.$GLOBALS['lang']['label_event_ajout'].'</button>'."\n";
// notif popup bubble
$out_html .= "\t".'<span id="popup-notif"><span id="count-posts"><span id="counter"></span></span><span id="message-return"></span></span>'."\n";


$out_html .= send_agenda_json($tableau, true); // 1
$out_html .= php_lang_to_js(); // 2
$out_html .= '<script src="style/scripts/javascript.js"></script>'."\n"; // 3
$out_html .= '<script>'."\n"; // 4
$out_html .= 'var token = \''.new_token().'\';'."\n";
$out_html .= 'new EventAgenda();'."\n";
$out_html .= '</script>'."\n";

echo $out_html;

footer($begin);
