<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

/// formulaires GENERIQUES //////////

function form_select($id, $choix, $defaut, $label) {
	$form = '<label for="'.$id.'">'.$label.'</label>'."\n";
	$form .= "\t".'<select id="'.$id.'" name="'.$id.'">'."\n";
	foreach ($choix as $valeur => $mot) {
		$form .= "\t\t".'<option value="'.$valeur.'"'.(($defaut == $valeur) ? ' selected="selected" ' : '').'>'.$mot.'</option>'."\n";
	}
	$form .= "\t".'</select>'."\n";
	$form .= "\n";
	return $form;
}

function form_select_no_label($id, $choix, $defaut) {
	$form = '<select id="'.$id.'" name="'.$id.'">'."\n";
	foreach ($choix as $valeur => $mot) {
		$form .= "\t".'<option value="'.$valeur.'"'.(($defaut == $valeur) ? ' selected="selected" ' : '').'>'.$mot.'</option>'."\n";
	}
	$form .= '</select>'."\n";
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


function form_format_date($defaut) {
	$jour_l = jour_en_lettres(date('d'), date('m'), date('Y'));
	$mois_l = mois_en_lettres(date('m'));
	$formats = array (
		'0' => date('d').'/'.date('m').'/'.date('Y'),             // 05/07/2011
		'1' => date('m').'/'.date('d').'/'.date('Y'),             // 07/05/2011
		'2' => date('d').' '.$mois_l.' '.date('Y'),               // 05 juillet 2011
		'3' => $jour_l.' '.date('d').' '.$mois_l.' '.date('Y'),   // mardi 05 juillet 2011
		'4' => $jour_l.' '.date('d').' '.$mois_l,                 // mardi 05 juillet
		'5' => $mois_l.' '.date('d').', '.date('Y'),              // juillet 05, 2011
		'6' => $jour_l.', '.$mois_l.' '.date('d').', '.date('Y'), // mardi, juillet 05, 2011
		'7' => date('Y').'-'.date('m').'-'.date('d'),             // 2011-07-05
		'8' => substr($jour_l,0,3).'. '.date('d').' '.$mois_l,    // ven. 14 janvier
	);
	$form = "\t".'<label>'.$GLOBALS['lang']['pref_format_date'].'</label>'."\n";
	$form .= "\t".'<select name="format_date">'."\n";
	foreach ($formats as $option => $label) {
		$form .= "\t\t".'<option value="'.htmlentities($option).'"'.(($defaut == $option) ? ' selected="selected" ' : '').'>'.$label.'</option>'."\n";
	}
	$form .= "\t".'</select>'."\n";
	return $form;
}

function form_fuseau_horaire($defaut) {
	$all_timezones = timezone_identifiers_list();
	$liste_fuseau = array();
	$cities = array();
	foreach($all_timezones as $tz) {
		$spos = strpos($tz, '/');
		if ($spos !== FALSE) {
			$continent = substr($tz, 0, $spos);
			$city = substr($tz, $spos+1);
			$liste_fuseau[$continent][] = array('tz_name' => $tz, 'city' => $city);
		}
		if ($tz == 'UTC') {
			$liste_fuseau['UTC'][] = array('tz_name' => 'UTC', 'city' => 'UTC');
		}
	}
	$form = '<label>'.$GLOBALS['lang']['pref_fuseau_horaire'].'</label>'."\n";
	$form .= '<select name="fuseau_horaire">'."\n";
	foreach ($liste_fuseau as $continent => $zone) {
		$form .= "\t".'<optgroup label="'.ucfirst(strtolower($continent)).'">'."\n";
		foreach ($zone as $fuseau) {
			$form .= "\t\t".'<option value="'.htmlentities($fuseau['tz_name']).'"';
			$form .= ($defaut == $fuseau['tz_name']) ? ' selected="selected"' : '';
				$timeoffset = date_offset_get(date_create('now', timezone_open($fuseau['tz_name'])) );
				$formated_toffset = '(UTC'.(($timeoffset < 0) ? '–' : '+').str2(floor((abs($timeoffset)/3600))) .':'.str2(floor((abs($timeoffset)%3600)/60)) .') ';
			$form .= '>'.$formated_toffset.' '.htmlentities($fuseau['city']).'</option>'."\n";
		}
		$form .= "\t".'</optgroup>'."\n";
	}
	$form .= '</select>'."\n";
	return $form;
}

function form_format_heure($defaut) {
	$formats = array (
		'0' => date('H\:i\:s'),		// 23:56:04
		'1' => date('H\:i'),			// 23:56
		'2' => date('h\:i\:s A'),	// 11:56:04 PM
		'3' => date('h\:i A'),		// 11:56 PM
	);
	$form = '<label>'.$GLOBALS['lang']['pref_format_heure'].'</label>'."\n";
	$form .= '<select name="format_heure">'."\n";
	foreach ($formats as $option => $label) {
		$form .= "\t".'<option value="'.htmlentities($option).'"'.(($defaut == $option) ? ' selected="selected" ' : '').'>'.htmlentities($label).'</option>'."\n";
	}
	$form .= "\t".'</select>'."\n";
	return $form;
}

function form_langue($defaut) {
	$form = '<label>'.$GLOBALS['lang']['pref_langue'].'</label>'."\n";
	$form .= '<select name="langue">'."\n";
	foreach ($GLOBALS['langs'] as $option => $label) {
		$form .= "\t".'<option value="'.htmlentities($option).'"'.(($defaut == $option) ? ' selected="selected" ' : '').'>'.$label.'</option>'."\n";
	}
	$form .= '</select>'."\n";
	return $form;
}

function form_langue_install($label) {
	$ret = '<label for="langue">'.$label;
	$ret .= '<select id="langue" name="langue">'."\n";
	foreach ($GLOBALS['langs'] as $option => $label) {
		$ret .= "\t".'<option value="'.htmlentities($option).'">'.$label.'</option>'."\n";
	}
	$ret .= '</select></label>'."\n";
	echo $ret;
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

function filtre($type, $filtre) { // cette fonction est très gourmande en ressources.
	$liste_des_types = array();
	$ret = '';
	$ret .= "\n".'<select name="filtre">'."\n" ;
	// Articles
	if ($type == 'articles') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_article_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_date, 1, 6) AS date FROM articles ORDER BY date DESC";
		$tab_tags = list_all_tags('articles', FALSE);
	// Commentaires
	} elseif ($type == 'commentaires') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_comment_derniers'].'</option>'."\n";
		$tab_auteur = nb_entries_as('commentaires', 'bt_author');
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM commentaires ORDER BY bt_id DESC";
	// Liens
	} elseif ($type == 'links') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_link_derniers'].'</option>'."\n";
		$tab_tags = list_all_tags('links', FALSE);
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM links ORDER BY bt_id DESC";
	// Notes
	} elseif ($type == 'notes') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_note_derniers'].'</option>'."\n";
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM notes ORDER BY bt_id DESC";
	// Fichiers
	} elseif ($type == 'fichiers') {
		$ret .= '<option value="">'.$GLOBALS['lang']['label_fichier_derniers'].'</option>'."\n";
		$tab_type = nb_entries_as('images', 'bt_type');
		$query = "SELECT DISTINCT substr(bt_id, 1, 6) AS date FROM images ORDER BY bt_id DESC";
	}

	try {
		$req = $GLOBALS['db_handle']->prepare($query);
		$req->execute(array());
		while ($row = $req->fetch()) {
			$tableau_mois[$row['date']] = mois_en_lettres(substr($row['date'], 4, 2)).' '.substr($row['date'], 0, 4);
		}
	} catch (Exception $x) {
		die('Erreur affichage form_filtre() : '.$x->getMessage());
	}

	/// BROUILLONS vs PUBLIES
	if ($type !== 'notes') {
		$ret .= '<option value="draft"'.(($filtre == 'draft') ? ' selected="selected"' : '').'>'.$GLOBALS['lang']['label_invisibles'].'</option>'."\n";
		$ret .= '<option value="pub"'.(($filtre == 'pub') ? ' selected="selected"' : '').'>'.$GLOBALS['lang']['label_publies'].'</option>'."\n";
	}

	/// PAR DATE
	if (!empty($tableau_mois)) {
		$ret .= '<optgroup label="'.$GLOBALS['lang']['label_date'].'">'."\n";
		foreach ($tableau_mois as $mois => $label) {
			$ret .= "\t".'<option value="' . htmlentities($mois) . '"'.((substr($filtre, 0, 6) == $mois) ? ' selected="selected"' : '').'>'.$label.'</option>'."\n";
		}
		$ret .= '</optgroup>'."\n";
	}

	/// PAR AUTEUR S'IL S'AGIT DES COMMENTAIRES
	if (!empty($tab_auteur)) {
		$ret .= '<optgroup label="'.$GLOBALS['lang']['pref_auteur'].'">'."\n";
		foreach ($tab_auteur as $nom) {
			if (!empty($nom['nb']) ) {
				$ret .= "\t".'<option value="auteur.'.$nom['bt_author'].'"'.(($filtre == 'auteur.'.$nom['bt_author']) ? ' selected="selected"' : '').'>'.$nom['bt_author'].' ('.$nom['nb'].')'.'</option>'."\n";
			}
		}
		$ret .= '</optgroup>'."\n";
	}

	/// PAR TYPE S'IL S'AGIT DES FICHIERS
	if (!empty($tab_type)) {
		$ret .= '<optgroup label="'.'Type'.'">'."\n";
		foreach ($tab_type as $type) {
			if (!empty($type) ) {
				$ret .= "\t".'<option value="type.'.$type['bt_type'].'"'.(($filtre == 'type.'.$type['bt_type']) ? ' selected="selected"' : '').'>'.$type['bt_type'].' ('.$type['nb'].')'.'</option>'."\n";
			}
		}
		$ret .= '</optgroup>'."\n";
	}

	///PAR TAGS POUR LES LIENS & ARTICLES
	if (!empty($tab_tags)) {
		$ret .= '<optgroup label="'.'Tags'.'">'."\n";
		foreach ($tab_tags as $tag => $nb) {
			$ret .= "\t".'<option value="tag.'.$tag.'"'.(($filtre == 'tag.'.$tag) ? ' selected="selected"' : '').'>'.$tag.' ('.$nb.')</option>'."\n";
		}
		$ret .= '</optgroup>'."\n";
	}
	$ret .= '</select> '."\n\n";

	return $ret;
}




/// Formulaire pour ajouter un lien dans Links côté Admin
function afficher_form_link($step, $erreurs, $editlink='') {
	if ($erreurs) {
		echo erreurs($erreurs);
	}
	$form = '';
	if ($step == 1) { // postage de l'URL : un champ affiché en GET
		$form .= '<form method="get" id="post-new-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'">'."\n";
		$form .= '<fieldset>'."\n";
		$form .= "\t".'<div class="contain-input">'."\n";
		$form .= "\t\t".'<label for="url">'.$GLOBALS['lang']['label_nouv_lien'].'</label>'."\n";
		$form .= "\t\t".'<input type="text" name="url" id="url" value="" size="70" placeholder="'.$GLOBALS['lang']['label_nouv_lien'].'" class="text" autocomplete="off" tabindex="10" />'."\n";
		$form .= "\t".'</div>'."\n";
		//$form .= "\t".'<p class="submit-bttns"><button type="submit" class="submit button-submit">'.$GLOBALS['lang']['envoyer'].'</button></p>'."\n";
		$form .= '</fieldset>'."\n";
		$form .= '</form>'."\n\n";

	} elseif ($step == 2) { // Form de l'URL, avec titre, description, en POST cette fois, et qu'il faut vérifier avant de stoquer dans la BDD.
		$form .= '<form method="post" onsubmit="return moveTag();" id="post-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'">'."\n";

		$url = $_GET['url'];
		$type = 'url';
		$title = $url;
		$charset = "UTF-8";
		$new_id = date('YmdHis');

		// URL is empty or no URI. It’s a note: we hide the URI field.
		if (empty($url) or (strpos($url, 'http') !== 0) ) {
			$type = 'note';
			$title = 'Note'.(!empty($url) ? ' : '.html_entity_decode( $url, ENT_QUOTES | ENT_HTML5, 'UTF-8') : '');
			$url = $GLOBALS['racine'].'?mode=links&amp;id='.$new_id;
			$form .= hidden_input('url', $url);
			$form .= hidden_input('type', 'note');
		// URL is not empty
		} else {
			// Find out type of file
			$response = request_external_files(array($url), 15, false);
			$ext_file = $response[$url];
			$rep_hdr = $ext_file['headers'];
			$cnt_type = (isset($rep_hdr['content-type'])) ? (is_array($rep_hdr['content-type']) ? $rep_hdr['content-type'][count($rep_hdr['content-type'])-1] : $rep_hdr['content-type']) : 'text/';
			$cnt_type = (is_array($cnt_type)) ? $cnt_type[0] : $cnt_type;

			// Image
			if (strpos($cnt_type, 'image/') === 0) {
				$title = $GLOBALS['lang']['label_image'];
				if (list($width, $height) = @getimagesize($url)) {
					$fdata = $url;
					$type = 'image';
					$title .= ' - '.$width.'x'.$height.'px ';
				}
			}

			// Non-image NON-textual file (pdf…)
			elseif (strpos($cnt_type, 'text/') !== 0 and strpos($cnt_type, 'xml') === FALSE) {
				if ($GLOBALS['dl_link_to_files'] == 2) {
					$type = 'file';
				}
			}

			// a HTML document: parse it for any <title> ; fallback : $url
			elseif (!empty($ext_file['body'])) {
				libxml_use_internal_errors(true);
				$dom = new DOMDocument();
				$dom->strictErrorChecking = FALSE;
//				$dom->loadHTML(mb_convert_encoding($ext_file['body'], 'HTML-ENTITIES',  'UTF-8'));
				$dom->loadHTML($ext_file['body']);
				$elements = $dom->getElementsByTagName('title');
				if ($elements->length > 0) {
					$title = $elements->item(0)->textContent;
				}
				libxml_use_internal_errors(false);
			}

			$form .= "\t".'<input type="text" name="url" value="'.htmlspecialchars($url).'" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_url']).'" size="50" class="text readonly-like" />'."\n";
			$form .= hidden_input('type', 'link');
		}

		$link = array('title' => htmlspecialchars($title), 'url' => htmlspecialchars($url));
		$form .= "\t".'<input type="text" name="title" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" required="" value="'.$link['title'].'" size="50" class="text" autofocus />'."\n";
		$form .= "\t".'<span id="description-box">'."\n";
		$form .= ($type == 'image') ? "\t\t".'<span id="img-container"><img src="'.$fdata.'" alt="img" class="preview-img" height="'.$height.'" width="'.$width.'"/></span>' : '';
		$form .= "\t\t".'<textarea class="text description" name="description" cols="40" rows="7" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_description']).'"></textarea>'."\n";
		$form .= "\t".'</span>'."\n";

		$form .= "\t".'<div id="tag_bloc">'."\n";
		$form .= form_categories_links('links', '');
		$form .= "\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'"/>'."\n";
		$form .= "\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$form .= "\t".'</div>'."\n";

		$form .= "\t".'<input type="checkbox" name="statut" id="statut" class="checkbox" />'.'<label class="forcheckbox" for="statut">'.$GLOBALS['lang']['label_lien_priv'].'</label>'."\n";
		if ($type == 'image' or $type == 'file') {
			// download of file is asked
			$form .= ($GLOBALS['dl_link_to_files'] == 2) ? "\t".'<input type="checkbox" name="add_to_files" id="add_to_files" class="checkbox" />'.'<label class="forcheckbox" for="add_to_files">'.$GLOBALS['lang']['label_dl_fichier'].'</label>'."\n" : '';
			// download of file is systematic
			$form .= ($GLOBALS['dl_link_to_files'] == 1) ? hidden_input('add_to_files', 'on') : '';
		}
		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'links.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-submit" type="submit" name="enregistrer" id="valid-link">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('bt_id', $new_id);
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('dossier', '');
		$form .= '</form>'."\n\n";

	} elseif ($step == 'edit') { // Form pour l'édition d'un lien : les champs sont remplis avec le "wiki_content" et il y a les boutons suppr/activer en plus.
		$form = '<form method="post" onsubmit="return moveTag();" id="post-lien" action="'.basename($_SERVER['SCRIPT_NAME']).'?id='.$editlink['bt_id'].'">'."\n";
		$form .= "\t".'<input type="text" name="url" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_url']).'" required="" value="'.$editlink['bt_link'].'" size="70" class="text readonly-like" /></label>'."\n";
		$form .= "\t".'<input type="text" name="title" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_titre']).'" required="" value="'.$editlink['bt_title'].'" size="70" class="text" autofocus /></label>'."\n";
		$form .= "\t".'<div id="description-box">'."\n";
		$form .= "\t\t".'<textarea class="description text" name="description" cols="70" rows="7" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_description']).'" >'.$editlink['bt_wiki_content'].'</textarea>'."\n";
		$form .= "\t".'</div>'."\n";
		$form .= "\t".'<div id="tag_bloc">'."\n";
		$form .= form_categories_links('links', $editlink['bt_tags']);
		$form .= "\t\t".'<input list="htmlListTags" type="text" class="text" id="type_tags" name="tags" placeholder="'.ucfirst($GLOBALS['lang']['placeholder_tags']).'"/>'."\n";
		$form .= "\t\t".'<input type="hidden" id="categories" name="categories" value="" />'."\n";
		$form .= "\t".'</div>'."\n";
		$form .= "\t".'<input type="checkbox" name="statut" id="statut" class="checkbox" '.(($editlink['bt_statut'] == 0) ? 'checked ' : '').'/>'.'<label class="forcheckbox" for="statut">'.$GLOBALS['lang']['label_lien_priv'].'</label>'."\n";

		$form .= "\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t".'<button class="submit button-delete" type="button" name="supprimer" onclick="rmArticle(this)">'.$GLOBALS['lang']['supprimer'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-cancel" type="button" onclick="goToUrl(\'links.php\');">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t".'<button class="submit button-submit" type="submit" name="editer">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t".'</p>'."\n";
		$form .= hidden_input('ID', $editlink['ID']);
		$form .= hidden_input('bt_id', $editlink['bt_id']);
		$form .= hidden_input('_verif_envoi', '1');
		$form .= hidden_input('is_it_edit', 'yes');
		$form .= hidden_input('token', new_token());
		$form .= hidden_input('type', $editlink['bt_type']);
		$form .= '</form>'."\n\n";
	}
	return $form;
}

function form_formatting_toolbar($extended=FALSE) {
	$html = '';

	$html .= '<p class="formatbut">'."\n";
	$html .= "\t".'<button id="button01" class="but" type="button" title="'.$GLOBALS['lang']['bouton-gras'].'" data-tag="[b]|[/b]"></button>'."\n";
	$html .= "\t".'<button id="button02" class="but" type="button" title="'.$GLOBALS['lang']['bouton-ital'].'" data-tag="[i]|[/i]"></button>'."\n";
	$html .= "\t".'<button id="button03" class="but" type="button" title="'.$GLOBALS['lang']['bouton-soul'].'" data-tag="[u]|[/u]"></button>'."\n";
	$html .= "\t".'<button id="button04" class="but" type="button" title="'.$GLOBALS['lang']['bouton-barr'].'" data-tag="[s]|[/s]"></button>'."\n";

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

	}

	$html .= "\t".'<span class="spacer"></span>'."\n";
	$html .= "\t".'<button id="button09" class="but" type="button" title="'.$GLOBALS['lang']['bouton-lien'].'" data-tag="[||http://]"></button>'."\n";
	$html .= "\t".'<button id="button10" class="but" type="button" title="'.$GLOBALS['lang']['bouton-cita'].'" data-tag="[quote]|[/quote]"></button>'."\n";
	$html .= "\t".'<button id="button12" class="but" type="button" title="'.$GLOBALS['lang']['bouton-code'].'" data-tag="[code]|[/code]"></button>'."\n";

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
			$html .= "\t".'<li><span>'.trim($tag['t']).'</span><a href="javascript:void(0)" onclick="removeTag(this.parentNode)">×</a></li>'."\n";
		}
	}
	$html .= '</ul>'."\n";
	return $html;
}

