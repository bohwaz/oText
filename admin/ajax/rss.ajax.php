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
if (isset($_GET['refresh_all'], $_GET['guid']) and ($_GET['guid'] == BLOG_UID)) {
//	if ($_GET['guid'] == BLOG_UID) {
//		///////////////////////////////////////////////////////////////////////////////////////: THIS NEEDED ? since we have the "require inc/boot.php" hereabove.
//		$GLOBALS['db_handle'] = open_base();
		$GLOBALS['liste_flux'] = open_serialzd_file(FEEDS_DB);

		refresh_rss($GLOBALS['liste_flux']);
		die('Success');
//	} else {
//		die('Error');
//	}
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
	$feed_array = retrieve_new_feeds(array($new_feed));


	if (!($feed_array[$new_feed]['infos']['type'] == 'ATOM' or $feed_array[$new_feed]['infos']['type'] == 'RSS')) {
		die('Error: Invalid ressource (not an RSS/ATOM feed)');
	}

	// adding to serialized-db
	$GLOBALS['liste_flux'][$new_feed] = array(
		'link' => $new_feed,
		'title' => ucfirst($feed_array[$new_feed]['infos']['title']),
		'checksum' => '42',
		'time' => '1',
		'folder' => $new_feed_folder,
		'nbrun' => '0'
	);

	// sort list with title
	$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));

	// recount unread elements (they are put in that array for caching ans performance purpose).
	$feeds_nb = rss_count_feed();
	foreach ($feeds_nb as $i => $feed) {
		$GLOBALS['liste_flux'][$feed['bt_feed']]['nbrun'] = (isset($feed['nbrun'])) ? $feed['nbrun'] : 0;
	}
	// save to file
	file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');

	// Update DB
	refresh_rss(array($new_feed => $GLOBALS['liste_flux'][$new_feed]));
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
		$feedurl = "";
		foreach ($GLOBALS['liste_flux'] as $i => $flux) {
			if ($feedhash == crc32($i)) {
				$feedurl = $i;
				break;
		}	}

		$query = 'UPDATE rss SET bt_statut=0 WHERE bt_feed=?';
		$array = array($feedurl);
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
		// recount unread elements (they are put in that array for caching ans performance purpose).
		$feeds_nb = rss_count_feed();
		foreach ($feeds_nb as $i => $feed) {
			$GLOBALS['liste_flux'][$feed['bt_feed']]['nbrun'] = (isset($feed['nbrun'])) ? $feed['nbrun'] : 0;
		}
		// save to file
		file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');


		die('Success');
	} catch (Exception $e) {
		die('Error : Rss mark as read: '.$e->getMessage());
	}
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
						$req->execute(array($feed['link']));
						// rm feed from feed list $GLOBALS['liste_flux']
						unset($GLOBALS['liste_flux'][$i]);
						break;

					case 'edited':
						// update feed in $GLOBALS['liste_flux']
						unset($GLOBALS['liste_flux'][$i]);
						$feed['link'] = $posted_feeds[$feed['checksum']]['link'];
						$feed['title'] = $posted_feeds[$feed['checksum']]['title'];
						$feed['folder'] = $posted_feeds[$feed['checksum']]['folder'];

						$GLOBALS['liste_flux'][$feed['link']] = $feed;
						break;
				}
			}
		}
		// commit to DB
		$GLOBALS['db_handle']->commit();

		// commit to feed list FILE
		$GLOBALS['liste_flux'] = array_reverse(tri_selon_sous_cle($GLOBALS['liste_flux'], 'title'));
		file_put_contents(FEEDS_DB, '<?php /* '.chunk_split(base64_encode(serialize($GLOBALS['liste_flux']))).' */');


		die('Success');
	} catch (Exception $e) {
		die('SQL Feeds-update Error: '.$e->getMessage());
	}
	die ('Success');
}

exit;