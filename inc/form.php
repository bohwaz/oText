<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

/// formulaires GENERIQUES //////////

function form_select($id, $choix, $defaut, $label) {
	$form = '';
	if (!empty($label)) {
		$form .= '<label for="'.$id.'">'.$label.'</label>'."\n";
	}
	$form .= "\t".'<select id="'.$id.'" name="'.$id.'">'."\n";
	foreach ($choix as $valeur => $mot) {
		$form .= "\t\t".'<option value="'.$valeur.'"'.(($defaut == $valeur) ? ' selected="selected" ' : '').'>'.$mot.'</option>'."\n";
	}
	$form .= "\t".'</select>'."\n";
	$form .= "\n";
	return $form;
}

function hidden_input($nom, $valeur, $id=0) {
	$id = ($id === 0) ? '' : ' id="'.$nom.'"';
	$form = '<input type="hidden" name="'.$nom.'"'.$id.' value="'.$valeur.'" />'."\n";
	return $form;
}

/// formulaires PREFERENCES //////////

function select_yes_no($name, $defaut, $label) {
	$choix = array(
		'1' => $GLOBALS['lang']['oui'],
		'0' => $GLOBALS['lang']['non']
	);
	$form = '<label for="'.$name.'" >'.$label.'</label>'."\n";
	$form .= '<select id="'.$name.'" name="'.$name.'">'."\n" ;
	foreach ($choix as $option => $label) {
		$form .= "\t".'<option value="'.htmlentities($option).'"'.(($option == $defaut) ? ' selected="selected" ' : '').'>'.htmlentities($label).'</option>'."\n";
	}
	$form .= '</select>'."\n";
	return $form;
}

function form_checkbox($name, $checked, $label) {
	$checked = ($checked) ? "checked " : '';
	$form = '<input type="checkbox" id="'.$name.'" name="'.$name.'" '.$checked.' class="checkbox-toggle" />'."\n" ;
	$form .= '<label for="'.$name.'" >'.$label.'</label>'."\n";
	return $form;
}

function form_fuseau_horaire($defaut) {
	$all_timezones = timezone_identifiers_list();
	$liste_fuseau = array();
	$cities = array();
	foreach($all_timezones as $tz) {
		$spos = strpos($tz, '/');
		if ($spos !== FALSE) {
			$liste_fuseau[substr($tz, 0, $spos)][$tz] = substr($tz, $spos+1);
		} elseif ($tz == 'UTC') {
			$liste_fuseau['UTC'][$tz] = $tz;
		}
	}
	$form = '<label>'.$GLOBALS['lang']['pref_fuseau_horaire'].'</label>'."\n";
	$form .= '<select name="fuseau_horaire">'."\n";
	foreach ($liste_fuseau as $continent => $tzs) {
		$form .= "\t".'<optgroup label="'.ucfirst(strtolower($continent)).'">'."\n";
		foreach ($tzs as $tz => $city) {
			$form .= "\t\t".'<option value="'.htmlentities($tz).'"';
			$form .= ($defaut == $tz) ? ' selected="selected"' : '';
				$timeoffset = date_offset_get(date_create('now', timezone_open($tz)) );
				$formated_toffset = '(UTC'.(($timeoffset < 0) ? '–' : '+').str2(floor((abs($timeoffset)/3600))) .':'.str2(floor((abs($timeoffset)%3600)/60)) .')';
			$form .= '>'.$formated_toffset.' '.htmlentities($city).'</option>'."\n";
		}
		$form .= "\t".'</optgroup>'."\n";
	}
	$form .= '</select>'."\n";
	return $form;
}

// formulaires ARTICLES //////////

function afficher_form_filtre($type, $filtre) {
	$ret = '<form method="get" action="'.basename($_SERVER['SCRIPT_NAME']).'" onchange="this.submit();">'."\n";
	$ret .= '<div id="form-filtre">'."\n";
	$ret .= filtre($type, $filtre);
	$ret .= '</div>'."\n";
	$ret .= '</form>'."\n";
	echo $ret;
}

function filtre($type, $filtre) {
	$liste_des_types = array();
	$ret = '';
	$ret .= "\n".'<select name="filtre">'."\n" ;
	// Articles
	if ($type == 'articles') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_article_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_date, 1, 6) AS date FROM articles ORDER BY date DESC";
	// Commentaires
	} elseif ($type == 'commentaires') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_comment_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM commentaires ORDER BY bt_id DESC";
	// Liens
	} elseif ($type == 'links') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_link_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM links ORDER BY bt_id DESC";
	// Notes
	} elseif ($type == 'notes') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_note_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM notes ORDER BY bt_id DESC";
	// Contacts
	} elseif ($type == 'contacts') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_contacts_all'].'</option>'."\n";
	// Fichiers
	} elseif ($type == 'images') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_fichier_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM images ORDER BY bt_id DESC";
	}


	/// PUBLISHED vs DRAFTS (or private)
	if (in_array($type, array('articles', 'commentaires', 'links', 'images') )) {
		$ret .= '<option value="draft"'.(($filtre == 'draft') ? ' selected="selected"' : '').'>'.$GLOBALS['lang']['label_invisibles'].'</option>'."\n";
		$ret .= '<option value="pub"'.(($filtre == 'pub') ? ' selected="selected"' : '').'>'.$GLOBALS['lang']['label_publies'].'</option>'."\n";
	}

	/// RECENT vs ARCHIVED (for notes)
	if (in_array($type, array('notes') )) {
		$ret .= '<option value="archived"'.(($filtre == 'archived') ? ' selected="selected"' : '').'>'.$GLOBALS['lang']['label_note_archived'].'</option>'."\n";
	}

	/// BY DATE
	if (in_array($type, array('articles', 'commentaires', 'links', 'images', 'notes')) ) {
		try {
			$req = $GLOBALS['db_handle']->prepare($query);
			$req->execute(array());
			while ($row = $req->fetch()) {
				$tableau_mois[$row['date']] = mois_en_lettres(substr($row['date'], 4, 2)).' '.substr($row['date'], 0, 4);
			}
		} catch (Exception $x) {
			die('Erreur affichage form_filtre() : '.$x->getMessage());
		}
		if (!empty($tableau_mois)) {
			$ret .= '<optgroup label="'.$GLOBALS['lang']['label_date'].'">'."\n";
			foreach ($tableau_mois as $mois => $label) {
				$ret .= "\t".'<option value="'.htmlentities($mois).'"'.((substr($filtre, 0, 6) == $mois) ? ' selected="selected"' : '').'>'.$label.'</option>'."\n";
			}
			$ret .= '</optgroup>'."\n";
		}
	}

	/// BY AUTHOR, FOR COMMENTS
	if (in_array($type, array('commentaires') )) {
		$tab_auteur = nb_entries_as($type, 'bt_author');
		if (!empty($tab_auteur)) {
			$ret .= '<optgroup label="'.$GLOBALS['lang']['pref_auteur'].'">'."\n";
			foreach ($tab_auteur as $nom) {
				if (!empty($nom['nb']) ) {
					$ret .= "\t".'<option value="auteur.'.$nom['bt_author'].'"'.(($filtre == 'auteur.'.$nom['bt_author']) ? ' selected="selected"' : '').'>'.$nom['bt_author'].' ('.$nom['nb'].')'.'</option>'."\n";
				}
			}
			$ret .= '</optgroup>'."\n";
		}
	}

	/// BY FILETYPE, FOR IMAGES/FILES
	if (in_array($type, array('images') )) {
		$tab_type = nb_entries_as('images', 'bt_type');
		if (!empty($tab_type)) {
			$ret .= '<optgroup label="'.'Type'.'">'."\n";
			foreach ($tab_type as $type) {
				if (!empty($type) ) {
					$ret .= "\t".'<option value="type.'.$type['bt_type'].'"'.(($filtre == 'type.'.$type['bt_type']) ? ' selected="selected"' : '').'>'.$type['bt_type'].' ('.$type['nb'].')'.'</option>'."\n";
				}
			}
			$ret .= '</optgroup>'."\n";
		}
	}

	/// BY TAGES, FOR ARTICLES AND LINKS
	if (in_array($type, array('links', 'articles') )) {
		$tab_tags = list_all_tags($type, FALSE);
		if (!empty($tab_tags)) {
			$ret .= '<optgroup label="'.'Tags'.'">'."\n";
			foreach ($tab_tags as $tag => $nb) {
				$ret .= "\t".'<option value="tag.'.$tag.'"'.(($filtre == 'tag.'.$tag) ? ' selected="selected"' : '').'>'.$tag.' ('.$nb.')</option>'."\n";
			}
			$ret .= '</optgroup>'."\n";
		}
	}

	/// BY LABEL FOR CONTACTS
	if (in_array($type, array('contacts') )) {
		$tab_label = nb_entries_as('contacts', 'bt_label');
		if (!empty($tab_label)) {
			$ret .= '<optgroup label="'.$GLOBALS['lang']['label-ctc-label'].'">'."\n";
			foreach ($tab_label as $label) {
				if (!empty($label['bt_label']) ) {
					$ret .= "\t".'<option value="label.'.$label['bt_label'].'"'.(($filtre == 'label.'.$label['bt_label']) ? ' selected="selected"' : '').'>'.$label['bt_label'].' ('.$label['nb'].')'.'</option>'."\n";
				}
			}
			$ret .= '</optgroup>'."\n";
		}
	}


	$ret .= '</select> '."\n\n";


	return $ret;
}



function form_formatting_toolbar($extended=FALSE) {
	$html = '';

	$html .= '<p class="formatbut">'."\n";
	$html .= "\t".'<button id="button01" class="but" type="button" title="'.$GLOBALS['lang']['bouton-gras'].'" data-tag="[b]|[/b]"></button>'."\n";
	$html .= "\t".'<button id="button02" class="but" type="button" title="'.$GLOBALS['lang']['bouton-ital'].'" data-tag="[i]|[/i]"></button>'."\n";
	$html .= "\t".'<button id="button03" class="but" type="button" title="'.$GLOBALS['lang']['bouton-soul'].'" data-tag="[u]|[/u]"></button>'."\n";
	$html .= "\t".'<button id="button04" class="but" type="button" title="'.$GLOBALS['lang']['bouton-barr'].'" data-tag="[s]|[/s]"></button>'."\n";
	$html .= "\t".'<span class="spacer"></span>'."\n";
	$html .= "\t".'<button id="button09" class="but" type="button" title="'.$GLOBALS['lang']['bouton-lien'].'" data-tag="[||http://]"></button>'."\n";
	$html .= "\t".'<button id="button10" class="but" type="button" title="'.$GLOBALS['lang']['bouton-cita'].'" data-tag="[quote]|[/quote]"></button>'."\n";
	$html .= "\t".'<button id="button12" class="but" type="button" title="'.$GLOBALS['lang']['bouton-code'].'" data-tag="[code]|[/code]"></button>'."\n";

	if ($extended) {
		$html .= "\t".'<span class="spacer"></span>'."\n";
		// bouton des couleurs
		$html .= "\t".'<span id="button13" class="but but-dropdown" title=""><span class="list list-color">';
		foreach (array('black', 'gray', 'silver', 'white', 'blue', 'green', 'red', 'yellow', 'fuchsia', 'lime', 'aqua', 'maroon', 'purple', 'navy', 'teal', 'olive', '#ff7000', '#ff9aff', '#a0f7ff', '#ffd700' ) as $value) {
			$html .= '<button type="button" data-tag="[color='.$value.']|[/color]" style="background:'.$value.';"></button>';
		}
		$html .= '</span></span>'."\n";

		// boutons de la taille de caractère
		$html .= "\t".'<span id="button14" class="but but-dropdown" title=""><span class="list list-size">';
		foreach (array('9', '12', '16', '20') as $value) {
			$html .= '<button type="button" data-tag="[size='.$value.']|[/size]" style="font-size:'.$value.'pt;">'.$value.'. Ipsum</button>';
		}
		$html .= '</span></span>'."\n";

		// quelques caractères unicode
		$html .= "\t".'<span id="button15" class="but but-dropdown" title=""><span class="list list-spechr">';
		foreach (preg_split('//u', 'æÆœŒéÉèÈçÇùÙàÀöÖ…«»±≠×÷ß®©↓↑←→øØ☠☣☢☮★☯☑☒☐♫♬♪♣♠♦❤♂♀☹☺☻♲⚐⚠☂√∑λπΩ№∞', null, PREG_SPLIT_NO_EMPTY) as $value) {
			$html .= '<button type="button" data-tag="'.$value.'">'.$value.'</button>';
		}
		$html .= '</span></span>'."\n";

		$html .= "\t".'<span class="spacer"></span>'."\n";
		$html .= "\t".'<button id="button05" class="but" type="button" title="'.$GLOBALS['lang']['bouton-left'].'" data-tag="[left]|[/left]" ></button>'."\n";
		$html .= "\t".'<button id="button06" class="but" type="button" title="'.$GLOBALS['lang']['bouton-center'].'" data-tag="[center]|[/center]" ></button>'."\n";
		$html .= "\t".'<button id="button07" class="but" type="button" title="'.$GLOBALS['lang']['bouton-right'].'" data-tag="[right]|[/right]"></button>'."\n";
		$html .= "\t".'<button id="button08" class="but" type="button" title="'.$GLOBALS['lang']['bouton-justify'].'" data-tag="[justify]|[/justify]"></button>'."\n";
		$html .= "\t".'<span class="spacer"></span>'."\n";
		$html .= "\t".'<button id="button11" class="but" type="button" title="'.$GLOBALS['lang']['bouton-imag'].'" data-tag="[img]||alt[/img]"></button>'."\n";
		$html .= "\t".'<button id="button16" class="but" type="button" title="'.$GLOBALS['lang']['bouton-liul'].'" data-tag="\n\n** element 1\n** element 2\n"></button>'."\n";
		$html .= "\t".'<button id="button17" class="but" type="button" title="'.$GLOBALS['lang']['bouton-liol'].'" data-tag="\n\n## element 1\n## element 2\n"></button>'."\n";
		$html .= "\t".'<span class="spacer"></span>'."\n";
		$html .= "\t".'<button id="button18" class="but js-action toggleAutoCorrect" type="button" title="'.$GLOBALS['lang']['bouton-spellcheck'].'"></button>'."\n";
		$html .= "\t".'<button id="button19" class="but js-action toggleFullScreen" type="button" title="'.$GLOBALS['lang']['bouton-fullscreen'].'"></button>'."\n";

	}

	$html .= '</p>'."\n";

	return $html;
}

function form_categories_links($where, $tags_post) {
	$tags = list_all_tags($where, FALSE);
	$html = '';
	if (!empty($tags)) {
		$html = '<datalist id="htmlListTags">'."\n";
		foreach ($tags as $tag => $i) $html .= "\t".'<option value="'.addslashes($tag).'">'."\n";
		$html .= '</datalist>'."\n";
	}
	$html .= '<ul id="selected">'."\n";
	$list_tags = explode(',', $tags_post);


	// remove diacritics and reindexes so that "ééé" does not passe after "zzz"
	foreach ($list_tags as $i => $tag) {
		$list_tags[$i] = array('t' => trim($tag), 'tt' => diacritique(trim($tag)));
	}
	$list_tags = array_reverse(tri_selon_sous_cle($list_tags, 'tt'));

	foreach ($list_tags as $i => $tag) {
		if (!empty($tag['t'])) {
			$html .= "\t".'<li><span>'.trim($tag['t']).'</span><a href="javascript:void(0)" onclick="this.parentNode.parentNode.removeChild(this.parentNode); return false;">×</a></li>'."\n";
		}
	}
	$html .= '</ul>'."\n";
	return $html;
}

