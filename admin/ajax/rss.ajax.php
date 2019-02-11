<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once '../inc/boot.php';

// Update all RSS feeds using GET (for cron jobs).
// only test here is on install UID.
if (isset($_GET['refresh_all'], $_GET['guid'])) {
	if ($_GET['guid'] == BLOG_UID) {
		$GLOBALS['db_handle'] = open_base();
		$GLOBALS['liste_flux'] = open_serialzd_file(FEEDS_DB);
		refresh_rss($GLOBALS['liste_flux']);
		die('Success');
	} else {
		die('Error');
	}
}


operate_session();
$GLOBALS['liste_flux'] = open_serialzd_file(FEEDS_DB);

/*
	This file is called by the other files. It is an underground working script,
	It is not intended to be called directly in your browser.
*/

// retreive all RSS feeds from the sources, and save them in DB.
// echoes the new feeds in JSON format to browser
if (isset($_POST['refresh_all'])) {
	$erreurs = valider_form_rss();
	if (!empty($erreurs)) {
		die(erreurs($erreurs));
	}
	$new_entries = refresh_rss($GLOBALS['liste_flux']);
	echo 'Success';
	$new_entries = tri_selon_sous_cle($new_entries, 'bt_date');

	echo send_rss_json($new_entries, false);
	die;
}


// delete old entries
if (isset($_POST['delete_old'])) {
	$erreurs = valider_form_rss();
	if (!empty($erreurs)) {
		die(erreurs($erreurs));
	}

	$query = 'DELETE FROM rss WHERE bt_statut=0 AND bt_bookmarked=0';
	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute(array());
		die('Success');
	} catch (Exception $e) {
		die('Error : Rss RM old entries AJAX: '.$e->getMessage());
	}
}


// add new RSS link to serialized-DB
if (isset($_POST['add-feed'])) {
	$erreurs = valider_form_rss();
	if (!empty($erreurs)) {
		die(erreurs($erreurs));
	}

	$new_feed = trim($_POST['add-feed']);
	$new_feed_folder = htmlspecialchars(trim($_POST['add-feed-folder']));

	// try request on new feed
	if (!$feeds = request_external_files(array($new_feed => hash('md5', $new_feed)), 25, true)) {
		die('Error : External request failed.');
	}

	// try parsing content
	$items = feed2array($feeds[hash('md5', $new_feed)]['body']);
	if ($items === FALSE or !isset($items['infos'])) {
		die('Error : invalid ressourse (XML parsing error).');
	}
	if (!in_array($items['infos']['type'], array('ATOM', 'RSS'))) {
		die('Error: not an RSS/ATOM feed.');
	}


	// adding to serialized-db
	$GLOBALS['liste_flux'][hash('md5', $new_feed)] = array(
		'link' => $new_feed,
		'title' => ucfirst($items['infos']['title']),
		'checksum' => '42',
		'time' => '1970101000000',
		'folder' => $new_feed_folder,
		'nbrun' => '0'
	);

	// sort list with title
	$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));

	// recount unread elements (they are put in that array for caching ans performance purpose).
	$feeds_nb = rss_count_feed();
	foreach ($GLOBALS['liste_flux'] as $hash => $item) {
		$GLOBALS['liste_flux'][$hash]['nbrun'] = 0;
		if (isset($feeds_nb[$hash])) {
			$GLOBALS['liste_flux'][$hash]['nbrun'] = $feeds_nb[$hash];
		}
	}

	// save to file
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');

	// Update DB
	refresh_rss(array($new_feed => $GLOBALS['liste_flux'][hash('md5', $new_feed)]));
	die('Success');
}

// mark some element(s) as read
if (isset($_POST['mark-as-read'])) {
	$erreurs = valider_form_rss();
	if (!empty($erreurs)) {
		die(erreurs($erreurs));
	}

	$what = $_POST['mark-as-read'];
	if ($what == 'all') {
		$query = 'UPDATE rss SET bt_statut=0';
		$array = array();
	}

	elseif ($what == 'site' and !empty($_POST['mark-as-read-data'])) {
		$feedhash = $_POST['mark-as-read-data'];

		$query = 'UPDATE rss SET bt_statut=0 WHERE bt_feed=?';
		$array = array($feedhash);
	}

	elseif ($what == 'post' and !empty($_POST['mark-as-read-data'])) {
		$postid = $_POST['mark-as-read-data'];
		$query = 'UPDATE rss SET bt_statut=0 WHERE bt_id=?';
		$array = array($postid);
	}

	elseif ($what == 'folder' and !empty($_POST['mark-as-read-data'])) {
		$folder = $_POST['mark-as-read-data'];
		$query = 'UPDATE rss SET bt_statut=0 WHERE bt_folder=?';
		$array = array($folder);
	}

	elseif ($what == 'postlist' and !empty($_POST['mark-as-read-data'])) {
		$list = json_decode($_POST['mark-as-read-data']);
		$questionmarks = str_repeat("?,", count($list)-1)."?";
		$query = 'UPDATE rss SET bt_statut=0 WHERE bt_id IN ('.$questionmarks.')';
		$array = $list;
	}

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
	} catch (Exception $e) {
		die('Error : Rss mark as read: '.$e->getMessage());
	}

	// recount unread elements (they are put in that array for caching ans performance purpose).
	$feeds_nb = rss_count_feed();
	foreach ($GLOBALS['liste_flux'] as $hash => $item) {
		$GLOBALS['liste_flux'][$hash]['nbrun'] = 0;
		if (isset($feeds_nb[$hash])) {
			$GLOBALS['liste_flux'][$hash]['nbrun'] = $feeds_nb[$hash];
		}
	}

	// save to file
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' *'.'/');

	die('Success');
}

// mark some elements as fav
if (isset($_POST['mark-as-fav'])) {
	$erreurs = valider_form_rss();
	if (!empty($erreurs)) {
		die(erreurs($erreurs));
	}

	$url = $_POST['url'];
	$query = 'UPDATE rss SET bt_bookmarked= (1-bt_bookmarked) WHERE bt_id= ? ';
	$array = array($url);

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute($array);
		die('Success');
	} catch (Exception $e) {
		die('Error : Rss mark as fav: '.$e->getMessage());
	}

}


if (isset($_POST['edit-feed-list'])) {
	$posted_feeds = json_decode($_POST['edit-feed-list'], TRUE);

	foreach ($posted_feeds as $i => $entry) {
		$posted_feeds[$entry['id']] = $entry;
	}

	try {
		$GLOBALS['db_handle']->beginTransaction();

		foreach($GLOBALS['liste_flux'] as $i => $feed) {

			if (isset($posted_feeds[$feed['checksum']])) {

				switch ($posted_feeds[$feed['checksum']]['action']) {
					case 'delete':
						// rm posts from that feed
						$req = $GLOBALS['db_handle']->prepare('DELETE FROM rss WHERE bt_feed = ?');
						$req->execute(array($i));
						// rm feed from feed list $GLOBALS['liste_flux']
						unset($GLOBALS['liste_flux'][$i]);
						break;

					case 'edited':
						// update feed in $GLOBALS['liste_flux']
						//unset($GLOBALS['liste_flux'][$i]);
						$GLOBALS['liste_flux'][$i]['link'] = $posted_feeds[$feed['checksum']]['link'];
						$GLOBALS['liste_flux'][$i]['title'] = $posted_feeds[$feed['checksum']]['title'];
						$GLOBALS['liste_flux'][$i]['folder'] = $posted_feeds[$feed['checksum']]['folder'];

						break;
				}
			}
		}
		// commit to DB
		$GLOBALS['db_handle']->commit();

		// commit to feed list FILE
		$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));
		file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' *'.'/');


		die('Success');
	} catch (Exception $e) {
		die('SQL Feeds-update Error: '.$e->getMessage());
	}
	die ('Success');
}

exit;