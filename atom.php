<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

header('Content-Type: application/atom+xml; charset=UTF-8');

// second level caching file.
$lv2_cache_file = 'var/cache/static/c_atom_'.substr(md5(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : ''), 0, 8).'.dat';
/*
// if cache file exists
if (file_exists($lv2_cache_file)) {
	// if cache not too old
	if (@filemtime($lv2_cache_file) > time()-(3600) ) {
		readfile($lv2_cache_file);
		die;
	}
	// file too old: delete it and go on (and create new file)
	@unlink($lv2_cache_file);
}
*/
require_once 'inc/boot.php';

$xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
$xml .= '<feed xmlns="http://www.w3.org/2005/Atom">'."\n";
$xml .= '<author><name>'.$GLOBALS['auteur'].'</name></author>'."\n";
$xml .= '<link rel="self" href="'.$GLOBALS['racine'].'atom.php'.((!empty($_SERVER['QUERY_STRING'])) ? '?'.(htmlspecialchars($_SERVER['QUERY_STRING'])) : '').'" />'."\n";

// ATOM DU BLOG
/* si y'a un ID en paramètre : flux sur fil commentaires de l'article "ID" */
if (isset($_GET['id'])) {
	$GLOBALS['db_handle'] = open_base();
	$article_id = htmlspecialchars($_GET['id']);

	$liste = liste_elements("SELECT c.*, a.bt_title FROM commentaires AS c, articles AS a WHERE c.bt_article_id=? AND c.bt_article_id=a.bt_id AND c.bt_statut=1 ORDER BY c.bt_id DESC", array($article_id));

	if (!empty($liste)) {
		$xml .= '<title>Commentaires sur '.$liste[0]['bt_title'].' - '.$GLOBALS['nom_du_site'].'</title>'."\n";
		$xml .= '<link href="'.$liste[0]['bt_link'].'" />'."\n";
		$xml .= '<id>'.$liste[0]['bt_link'].'</id>';


		foreach ($liste as $comment) {
			$dec = decode_id($comment['bt_id']);
			$tag = 'tag:'.parse_url($GLOBALS['racine'], PHP_URL_HOST).''.$dec['y'].'-'.$dec['m'].'-'.$dec['d'].':'.$comment['bt_id'];
			$xml .= '<entry>'."\n";
				$xml .= '<title>'.$comment['bt_author'].'</title>'."\n";
				$xml .= '<link href="'.URL_ROOT.get_blogpath($comment['bt_article_id'], $comment['bt_title']).$comment['bt_link'].'"/>'."\n";
				$xml .= '<id>'.$tag.'</id>'."\n";
				$xml .= '<updated>'.date('c', mktime($dec['h'], $dec['i'], $dec['s'], $dec['m'], $dec['d'], $dec['y'])).'</updated>'."\n";
				$xml .= '<content type="html">'.htmlspecialchars($comment['bt_content']).'</content>'."\n";
			$xml .= '</entry>'."\n";
		}
	} else {
		$xml .= '<entry>'."\n";
			$xml .= '<title>'.$GLOBALS['lang']['note_no_commentaire'].'</title>'."\n";
			$xml .= '<id>'.$GLOBALS['racine'].'</id>'."\n";
			$xml .= '<link href="'.$GLOBALS['racine'].'" />'."\n";
			$xml .= '<updated>'.date('r').'</updated>'."\n";
			$xml .= '<content type="html">'.$GLOBALS['lang']['no_comments'].'</content>'."\n";
		$xml .= '</entry>'."\n";
	}
}
/* sinon, fil rss sur les articles (par défaut) */
/* Ici, on utilise la petite BDD placée en cache. */
else {

	function rel2abs($article) { // convertit les URL relatives en absolues
		$article = str_replace(' src="/', ' src="http://'.$_SERVER['HTTP_HOST'].'/' , $article);
		$article = str_replace(' href="/', ' href="http://'.$_SERVER['HTTP_HOST'].'/' , $article);
		$base = $GLOBALS['racine'];
		$article = preg_replace('#(src|href)=\"(?!http)#i','$1="'.$base, $article);
		return $article;
	}


	$fcache = 'var/cache/static/cache_rss_array.dat';
	$liste = array();
	if (!file_exists($fcache)) {
		$GLOBALS['db_handle'] = open_base();
		rafraichir_cache_lv1();
	}
	// this function exists in SQLI.PHP. It is replaced here, because including sqli.php and the other files takes 10x more cpu load than this
	if (file_exists($fcache)) {
		$liste = unserialize(base64_decode(substr(file_get_contents($fcache), strlen('<?php /* '), -strlen(' */'))));
		if (!is_array($liste)) {
			$liste = array();
			unlink($fcache);
		}
	}

	$liste_rss = array();
	$modes_url = '';
	if (!empty($_GET['mode'])) {
		$found = 0;
		// 1 = articles
		if ( strpos($_GET['mode'], 'blog') !== FALSE ) {
			$liste_rss = array_merge($liste_rss, $liste['a']);
			$found = 1; $modes_url .= 'blog-';
		}
		// 2 = comments
		if ( strpos($_GET['mode'], 'comments') !== FALSE ) {
			$liste_rss = array_merge($liste_rss, $liste['c']);
			$found = 1; $modes_url .= 'comments-';
		}
		// 4 = links
		if (strpos($_GET['mode'], 'links') !== FALSE) {
			$liste_rss = array_merge($liste_rss, $liste['l']);
			$found = 1; $modes_url .= 'links-';
		}
		// if none matches, take articles
		if ($found == 0) { $liste_rss = $liste['a']; }

	// if none given, take articles
	} else {
		$liste_rss = $liste['a'];
	}

	// sort by tags, if any
	if (isset($_GET['tag'])) {
		foreach ($liste_rss as $i => $entry) {
			if (isset($entry['bt_tags'])) {
				if ( (strpos($entry['bt_tags'], htmlspecialchars($_GET['tag'].',')) === FALSE) and
				 	 (strpos($entry['bt_tags'], htmlspecialchars(', '.$_GET['tag'])) === FALSE) and
					 ($entry['bt_tags'] != htmlspecialchars($_GET['tag']))) {
					unset($liste_rss[$i]);
				}
			}
		}
	}

	// sort by date (which is a sub key, hence this loop)
	foreach ($liste_rss as $key => $item) {
		 $bt_id[$key] = (isset($item['bt_date'])) ? $item['bt_date'] : $item['bt_id'];
	}
	array_multisort($bt_id, SORT_DESC, $liste_rss);
	$liste_rss = array_slice($liste_rss, 0, 20);
	$invert = (isset($_GET['invertlinks'])) ? TRUE : FALSE;
	$xml .= '<title>'.$GLOBALS['nom_du_site'].'</title>'."\n";
	$xml .= '<link href="'.$GLOBALS['racine'].'?mode='.(trim($modes_url, '-')).'"/>'."\n";
	$xml .= '<id>'.$GLOBALS['racine'].'?mode='.$modes_url.'</id>'."\n";
	$main_updated = 0;
	$xml_post = '';
	foreach ($liste_rss as $elem) {
		$time = (isset($elem['bt_date'])) ? $elem['bt_date'] : $elem['bt_id'];
		$main_updated = max($main_updated, $time);
		if ($time > date('YmdHis')) { continue; }
		$title = (in_array($elem['bt_type'], array('article', 'link', 'note'))) ? $elem['bt_title'] : $elem['bt_author'];
		$tag = 'tag:'.parse_url($GLOBALS['racine'], PHP_URL_HOST).','.date_create_from_format('YmdHis', $time)->format('Y-m-d').':'.$elem['bt_type'].'-'.$elem['bt_id'];

		// normal entry
		$xml_post .= '<entry>'."\n";
		$xml_post .= '<title>'.$title.'</title>'."\n";
		$xml_post .= '<id>'.$tag.'</id>'."\n";
		$xml_post .= '<updated>'.date_create_from_format('YmdHis', $time)->format('c').'</updated>'."\n";

		if ($elem['bt_type'] == 'link' or $elem['bt_type'] == 'note') {
			if ($invert) {
				$xml_post .= '<link href="'.$GLOBALS['racine'].'?id='.$elem['bt_id'].'"/>'."\n";
				$xml_post .= '<content type="html">'.htmlspecialchars(rel2abs($elem['bt_content']).'<br/> — (<a href="'.$elem['bt_link'].'">link</a>)').'</content>'."\n";
			} else {
				$xml_post .= '<link href="'.$elem['bt_link'].'"/>'."\n";
				$xml_post .= '<content type="html">'.htmlspecialchars(rel2abs($elem['bt_content']).'<br/> — (<a href="'.$GLOBALS['racine'].'?id='.$elem['bt_id'].'">permalink</a>)').'</content>'."\n";
			}
		} else {
			$xml_post .= '<link href="'.URL_ROOT.$elem['bt_link'].'"/>'."\n";
			$xml_post .= '<content type="html">'.htmlspecialchars(rel2abs($elem['bt_content'])).'</content>'."\n";
		}
		if (isset($elem['bt_tags']) and !empty($elem['bt_tags'])) {
			$xml_post .= '<category term="'.implode('" />'."\n".'<category term="', explode(', ', $elem['bt_tags'])).'" />'."\n";
		}

		$xml_post .= '</entry>'."\n";
	}
	$xml .= '<updated>'.date_create_from_format('YmdHis', $main_updated)->format('c').'</updated>'."\n";
	$xml .= $xml_post;

}


$end = microtime(TRUE);
$xml .= '<!-- cached file generated in '.round(($end - $begin),6).' seconds, on '.date("r").' -->'."\n";
$xml .= '</feed>';

file_put_contents($lv2_cache_file, $xml);
echo $xml;

?>