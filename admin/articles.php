<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

function afficher_liste_articles($tableau) {
	$out = '';
	$out .= '<div id="blocBillets">';
	if (!empty($tableau)) {
		$i = 0;
		$previous_date = '';
		foreach ($tableau as $article) {
			$article_date = substr($article['bt_date'], 0, 6);
			if ($previous_date !== $article_date) {
				if ($previous_date !== '') {
					$out .= '</ul>'."\n";
				}
				$out .= "\n".'<h2>'.mois_en_lettres(substr($article_date, 4, 2)).' '.substr($article_date, 0, 4).'</h2>'."\n";
				$out .= '<ul class="billets">'."\n";
				$previous_date = $article_date;
			}
			// title and icons by status
			$title = trim(htmlspecialchars(mb_substr(strip_tags( (empty($article['bt_abstract']) ? $article['bt_content'] : $article['bt_abstract']) ), 0, 249), ENT_QUOTES)) . '…';

			$out .= "\t".'<li class="'.( ($article['bt_statut'] == '1') ? 'on' : 'off').'">'."\n";
			$out .= "\t\t".'<a class="titre" href="ecrire.php?post_id='.$article['bt_id'].'" title="'.$title.'">'.$article['bt_title']."\n";
			if ($article['bt_date'] > date('YmdHis')) 
			$out .= "\t\t\t".'<span class="planed">'.$GLOBALS['lang']['label_planned'].'</span>'."\n";
			$out .= "\t\t".'</a>'."\n";
			$out .= "\t\t".'<a class="date" href="'.basename($_SERVER['SCRIPT_NAME']).'?filtre='.substr($article['bt_date'],0,8).'">'.date_formate($article['bt_date']).', '.heure_formate($article['bt_date']).'</a>'."\n";
			$out .= "\t\t".'<a class="comments" href="commentaires.php?post_id='.$article['bt_id'].'">'.$article['bt_nb_comments'].'</a>'."\n";
			$out .= "\t\t".'<a class="preview" href="'.URL_ROOT.$article['bt_link'].'" title="'.$GLOBALS['lang'][(( $article['bt_statut'] == '1')?'lien_article':'preview')].'"></a>'."\n";
			$out .= "\t".'</li>'."\n";
			$i++;
		}
		if ($previous_date !== '') {
			$out .= '</ul>'."\n\n";
		}
	}

	$out .= '</div>'."\n";
	$out .= '<a id="fab" class="add-article" href="ecrire.php" title="'.$GLOBALS['lang']['titre_ecrire'].'">'.$GLOBALS['lang']['titre_ecrire'].'</a>'."\n";

	echo $out;
}


$tableau = array();
if ( !empty($_GET['filtre']) ) {
	if ( preg_match('#^\d{6}(\d{1,8})?$#', $_GET['filtre']) ) {
		$query = "SELECT * FROM articles WHERE bt_date LIKE ? ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array($_GET['filtre'].'%'));
	}
	elseif ($_GET['filtre'] == 'draft' or $_GET['filtre'] == 'pub') {
		$query = "SELECT * FROM articles WHERE bt_statut=? ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array((($_GET['filtre'] == 'draft') ? 0 : 1)));
	}
	elseif (strpos($_GET['filtre'], 'tag.') === 0) {
		$search = htmlspecialchars(ltrim(strstr($_GET['filtre'], '.'), '.'));
		$query = "SELECT * FROM articles WHERE bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? OR bt_tags LIKE ? ORDER BY bt_date DESC";
		$tableau = liste_elements($query, array($search, $search.',%', '%, '.$search, '%, '.$search.', %'));
	} else {
		$query = "SELECT * FROM articles ORDER BY bt_date DESC LIMIT 0, ".$GLOBALS['max_bill_admin'];
		$tableau = liste_elements($query, array());
	}
} elseif (!empty($_GET['q'])) {
	$arr = parse_search($_GET['q']);
	$sql_where = implode(array_fill(0, count($arr), '( bt_content || bt_title ) LIKE ? '), 'AND ');
	$query = "SELECT * FROM articles WHERE ".$sql_where."ORDER BY bt_date DESC";
	$tableau = liste_elements($query, $arr);
} else {
	$query = "SELECT * FROM articles ORDER BY bt_date DESC LIMIT 0, ".$GLOBALS['max_bill_admin'];
	$tableau = liste_elements($query, array());
}



// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['mesarticles'], "articles");  // <head></head>
afficher_topnav($GLOBALS['lang']['mesarticles']); #header

echo '<div id="axe">'."\n";
echo '<div id="subnav">'."\n";
	afficher_form_filtre('articles', (isset($_GET['filtre'])) ? htmlspecialchars($_GET['filtre']) : '');
	echo "\t".'<div class="nombre-elem">'."\n";
	echo "\t\t".ucfirst(nombre_objets(count($tableau), 'article')).' '.$GLOBALS['lang']['sur'].' '.liste_elements_count("SELECT count(ID) AS nbr FROM articles", array());
	echo "\t".'</div>'."\n";
echo '</div>'."\n";

echo '<div id="page">'."\n";

afficher_liste_articles($tableau);

echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";
	echo "\n".'<script>'."\n";
	echo 'var scrollPos = 0;'."\n";
	echo 'window.addEventListener(\'scroll\', function(){ scrollingFabHideShow() });'."\n";
	echo "\n".'</script>'."\n";

footer($begin);

