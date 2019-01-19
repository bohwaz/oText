<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

/* generates the comment form, with params from the admin-side and the visiter-side */
function afficher_form_commentaire($article_id, $mode, $erreurs, $edit_comm) {
	$form_html = '';
	// init default form fields contents
	$form_cont = array('author' => '', 'e_mail' => '', 'webpage' => '', 'comment' => '', 'statut' => '', 'bt_id' => '');

	// init captcha
	$ua = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';
	$captcha_x = rand(4, 9);
	$captcha_y = rand(1, 6);
	$captcha_hash = hash("sha1", $ua.($captcha_x+$captcha_y));


	// FILL DEFAULT FORM DATA
	// admin mode
	if ($mode == 'admin') {
		if (!empty($edit_comm)) {
			// edit mode
			$form_cont['author'] = protect($edit_comm['bt_author']);
			$form_cont['e_mail'] = protect($edit_comm['bt_email']);
			$form_cont['webpage'] = protect($edit_comm['bt_webpage']);
			$form_cont['comment'] = htmlspecialchars($edit_comm['bt_wiki_content']);
			$form_cont['statut'] = protect($edit_comm['bt_statut']);
			$form_cont['bt_id'] = protect($edit_comm['bt_id']);
		} else {
			// non-edit : new comment from admin
			$form_cont['author'] = $GLOBALS['auteur'];
			$form_cont['e_mail'] = $GLOBALS['email'];
			$form_cont['webpage'] = $GLOBALS['racine'];
		}
	}
	// public mode
	else {
		$form_cont['author'] = (isset($_COOKIE['auteur_c'])) ? protect($_COOKIE['auteur_c']) : '';
		$form_cont['e_mail'] = (isset($_COOKIE['email_c'])) ? protect($_COOKIE['email_c']) : '';
		$form_cont['webpage'] = (isset($_COOKIE['webpage_c'])) ? protect($_COOKIE['webpage_c']) : '';
	}

	// comment just submited (for submission OR for preview)
	if (isset($_POST['_verif_envoi'])) {
		$form_cont['author'] = protect($_POST['auteur']);
		$form_cont['e_mail'] = protect($_POST['email']);
		$form_cont['webpage'] = protect($_POST['webpage']);
		$form_cont['comment'] = protect($_POST['commentaire']);
	}

	// Preview : parses the comment, but does not save it
	if (isset($_POST['previsualiser'])) {
		$comm['bt_content'] = markup(protect($_POST['commentaire']));
		$comm['bt_id'] = date('YmdHis');
		$comm['bt_author'] = $form_cont['author'];
		$comm['bt_email'] = $form_cont['e_mail'];
		$comm['bt_webpage'] = $form_cont['webpage'];
		$comm['bt_link'] = '';
		$form_html .= '<div id="erreurs"><ul><li>Prévisualisation :</li></ul></div>'."\n";
		$form_html .= '<div id="previsualisation">'."\n";
		$form_html .= conversions_theme_commentaire(file_get_contents($GLOBALS['theme_post_comm']), $comm);
		$form_html .= '</div>'."\n";
	}

	// Posted but errors
	elseif (isset($_POST['_verif_envoi']) and !empty($erreurs)) {
		$form_html .= '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :'."\n" ;
		$form_html .= '<ul><li>'."\n";
		$form_html .=  implode('</li><li>', $erreurs);
		$form_html .=  '</li></ul></div>'."\n";
	}

	// prelim vars for generation of comment Form
	$cookie_checked = (isset($_COOKIE['cookie_c']) and $_COOKIE['cookie_c'] == 1) ? ' checked="checked"' : '';
	$subscribe_checked = (isset($_COOKIE['subscribe_c']) and $_COOKIE['subscribe_c'] == 1) ? ' checked="checked"' : '';
	$token = ($mode == 'admin') ? new_token() : $captcha_hash;

	$form = "\n";
	// If comments are closed (public only)
	if ($mode != 'admin' and ($GLOBALS['global_com_rule'] == '1' or get_entry('articles', 'bt_allow_comments', $article_id, 'return') == 0) ) {
		$form_html .= '<p>'.$GLOBALS['lang']['comment_not_allowed'].'</p>'."\n";
	}

	// Comments are open
	else {
		$form .= '<form id="form-commentaire" class="form-commentaire" method="post" action="?'.htmlentities($_SERVER['QUERY_STRING']).'">'."\n";
		$form .= "\t".'<fieldset class="field">'."\n";
		$form .= form_formatting_toolbar(FALSE);
		$form .= "\t\t".'<textarea class="commentaire text" name="commentaire" required="" placeholder="'.$GLOBALS['lang']['label_commentaire'].'" id="commentaire" cols="50" rows="10">'.$form_cont['comment'].'</textarea>'."\n";
		$form .= "\t".'</fieldset>'."\n";
		// info (name, url, email) field
		$form .= "\t".'<fieldset class="infos">'."\n";
		$form .= "\t\t".'<span><input type="text" name="auteur" id="auteur" placeholder="John Doe" required value="'.$form_cont['author'].'" size="25" class="text" />'."\n";
		$form .= "\t\t".'<label for="auteur">'.$GLOBALS['lang']['label_dp_pseudo'].'</label></span>'."\n";
		$form .= "\t\t".'<span><input type="email" name="email" id="email" placeholder="mail@example.com" '.(($GLOBALS['require_email'] == 1) ? 'required=""' : '').' value="'.$form_cont['e_mail'].'" size="25" class="text" />'."\n";
		$form .= "\t\t".'<label for="email">'.(($GLOBALS['require_email'] == 1) ? $GLOBALS['lang']['label_dp_email_required'] : $GLOBALS['lang']['label_dp_email']).'</label></span>'."\n";
		$form .= "\t\t".'<span><input type="url" name="webpage" id="webpage" placeholder="http://www.example.com" value="'.$form_cont['webpage'].'" size="25" class="text" />'."\n";
		$form .= "\t\t".'<label for="webpage">'.$GLOBALS['lang']['label_dp_webpage'].'</label></span>'."\n";
		// captcha field
		if ($mode != 'admin') {
		$form .= "\t\t".'<span><input type="number" name="captcha" id="captcha" autocomplete="off" value="" class="text" />'."\n";
		$form .= "\t\t".'<label for="captcha">'.$GLOBALS['lang']['label_dp_captcha'].'<b>'.en_lettres($captcha_x).'</b> &#x0002B; <b>'.en_lettres($captcha_y).'</b> '.'</label></span>'."\n";
		}
		$form .= "\t".'</fieldset><!--end info-->'."\n";
		// misc system POST info
		$form .= "\t".'<fieldset class="syst">'."\n";
		$form .= "\t\t".hidden_input('comment_id', $form_cont['bt_id']);
		$form .= "\t\t".hidden_input('_verif_envoi', '1');
		$form .= "\t\t".hidden_input('token', $token);
		$form .= "\t".'</fieldset><!--end syst-->'."\n";
		// cookie / subscribe checkboxes
		if ($mode != 'admin') {
			$form .= "\t".'<fieldset class="subsc"><!--begin cookie asking -->'."\n";
			$form .= "\t\t".'<input class="check" type="checkbox" id="allowcuki" name="allowcuki"'.$cookie_checked.' /><label for="allowcuki">'.$GLOBALS['lang']['comment_cookie'].'</label>'.'<br/>'."\n";
			$form .= "\t\t".'<input class="check" type="checkbox" id="subscribe" name="subscribe"'.$subscribe_checked.' /><label for="subscribe">'.$GLOBALS['lang']['comment_subscribe'].'</label>'."\n";
			$form .= "\t".'</fieldset><!--end cookie asking-->'."\n";
		}
		// submit buttons
		$form .= "\t".'<fieldset class="buttons">'."\n";
		$form .= "\t\t".'<p class="submit-bttns">'."\n";
		// previsualisation button
		if ($mode != 'admin')
		$form .= "\t\t".'<input class="submit" type="submit" name="previsualiser" value="'.$GLOBALS['lang']['preview'].'" />'."\n";
		$form .= "\t\t\t".'<button class="submit button-cancel" type="button" onclick="unfold(this);">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t\t".'<button class="submit button-submit" type="submit" name="enregistrer">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t\t".'</p>'."\n";
		$form .= "\t".'</fieldset><!--end buttons-->'."\n";
		if ($mode !='admin' and $GLOBALS['comm_defaut_status'] == '0') // petit message en cas de moderation a-priori
		$form .= "\t".'<div class="need-validation">'.$GLOBALS['lang']['comment_need_validation'].'</div>'."\n";
		$form .= '</form>'."\n";

		$form_html .= $form;
	}


	return $form_html;
}
