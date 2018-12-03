<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

/* generates the comment form, with params from the admin-side and the visiter-side */
function afficher_form_commentaire($article_id, $mode, $erreurs, $edit_comm) {
	// TODO : why this still GLOBAL ?
	$form_html = '';
	// init default form fields contents
	$form_cont = array('author' => '', 'e_mail' => '', 'webpage' => '', 'comment' => '', 'statut' => '', 'bt_id' => '', 'is_edit' => '0');

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
			$form_cont['is_edit'] = protect('1');

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


	// WORK ON REQUEST
	// preview ? submission ? validation ?

	// parses the comment, but does not save it
	if (isset($_POST['previsualiser'])) {
		$p_comm = (isset($_POST['commentaire'])) ? protect($_POST['commentaire']) : '';
		$comm['bt_content'] = markup($p_comm);
		$comm['bt_id'] = date('YmdHis');
		$comm['bt_author'] = $form_cont['author'];
		$comm['bt_email'] = $form_cont['e_mail'];
		$comm['bt_webpage'] = $form_cont['webpage'];
		$comm['anchor'] = article_anchor($comm['bt_id']);
		$comm['bt_link'] = '';
		$comm['auteur_lien'] = ($comm['bt_webpage'] != '') ? '<a href="'.$comm['bt_webpage'].'" class="webpage">'.$comm['bt_author'].'</a>' : $comm['bt_author'];
		$form_html .= '<div id="erreurs"><ul><li>Prévisualisation&nbsp;:</li></ul></div>'."\n";
		$form_html .= '<div id="previsualisation">'."\n";
		$form_html .= conversions_theme_commentaire(file_get_contents($GLOBALS['theme_post_comm']), $comm);
		$form_html .= '</div>'."\n";
	}

	// comm sent ; with errors
	elseif (isset($_POST['_verif_envoi']) and !empty($erreurs)) {
		$form_html .= '<div id="erreurs"><strong>'.$GLOBALS['lang']['erreurs'].'</strong> :'."\n" ;
		$form_html .= '<ul><li>'."\n";
		$form_html .=  implode('</li><li>', $erreurs);
		$form_html .=  '</li></ul></div>'."\n";
	}

	// prelim vars for Generation of comment Form
	$required = ($GLOBALS['require_email'] == 1) ? 'required=""' : '';
	$cookie_checked = (isset($_COOKIE['cookie_c']) and $_COOKIE['cookie_c'] == 1) ? ' checked="checked"' : '';
	$subscribe_checked = (isset($_COOKIE['subscribe_c']) and $_COOKIE['subscribe_c'] == 1) ? ' checked="checked"' : '';

	$form = "\n";

	// COMMENT FORM ON ADMIN SIDE : +always_open –captcha –previsualisation –verif
	if ($mode == 'admin') {
		$rand = '-'.substr(md5(rand(100,999)),0,5);
		$form .= '<form id="form-commentaire'.$form_cont['bt_id'].'" class="form-commentaire" method="post" action="'.basename($_SERVER['SCRIPT_NAME']).'?'.$_SERVER['QUERY_STRING'].'#erreurs">'."\n";
		// main comm field
		$form .= "\t".'<fieldset class="field">'."\n";
		$form .= form_formatting_toolbar(FALSE);
		$form .= "\t\t".'<textarea class="commentaire text" name="commentaire" required="" placeholder="Lorem Ipsum" id="commentaire'.$rand.'" cols="50" rows="10">'.$form_cont['comment'].'</textarea>'."\n";
		$form .= "\t".'</fieldset>'."\n";
		// info (name, url, email) field
		$form .= "\t".'<fieldset class="infos">'."\n";
		$form .= "\t\t".'<span><label for="auteur'.$rand.'">'.$GLOBALS['lang']['label_dp_pseudo'].'</label>';
		$form .= '<input type="text" name="auteur" id="auteur'.$rand.'" placeholder="John Doe" required value="'.$form_cont['author'].'" size="25" class="text" /></span>'."\n";
		$form .= "\t\t".'<span><label for="email'.$rand.'">'.(($GLOBALS['require_email'] == 1) ? $GLOBALS['lang']['label_dp_email_required'] : $GLOBALS['lang']['label_dp_email']).'</label>';
		$form .= '<input type="email" name="email" id="email'.$rand.'" placeholder="mail@example.com" '.$required.' value="'.$form_cont['e_mail'].'" size="25" class="text" /></span>'."\n";
		$form .= "\t\t".'<span><label for="webpage'.$rand.'">'.$GLOBALS['lang']['label_dp_webpage'].'</label>';
		$form .= '<input type="url" name="webpage" id="webpage'.$rand.'" placeholder="http://www.example.com" value="'.$form_cont['webpage'].'" size="25" class="text" /></span>'."\n";
		$form .= "\t".'</fieldset><!--end info-->'."\n";
		// misc system POST info
		$form .= "\t".'<fieldset class="syst">'."\n";
		$form .= "\t\t".hidden_input('comment_id', $form_cont['bt_id']);
		$form .= "\t\t".hidden_input('_verif_envoi', '1');
		$form .= "\t\t".hidden_input('token', new_token());
		$form .= "\t".'</fieldset><!--end syst-->'."\n";
			// submit buttons
		$form .= "\t".'<fieldset class="buttons">'."\n";
		$form .= "\t\t".'<p class="submit-bttns">'."\n";
		$form .= "\t\t\t".'<button class="submit button-cancel" type="button" onclick="unfold(this);">'.$GLOBALS['lang']['annuler'].'</button>'."\n";
		$form .= "\t\t\t".'<button class="submit button-submit" type="submit" name="enregistrer">'.$GLOBALS['lang']['envoyer'].'</button>'."\n";
		$form .= "\t\t".'</p>'."\n";
		$form .= "\t".'</fieldset><!--end buttons-->'."\n";
		$form .= '</form>'."\n";
		$form_html .= $form;

	// COMMENT ON PUBLIC SIDE
	} else {
		// ALLOW COMMENTS : OFF
		if ($GLOBALS['global_com_rule'] == '1' or get_entry('articles', 'bt_allow_comments', $article_id, 'return') == 0) {
			$form_html .= '<p>'.$GLOBALS['lang']['comment_not_allowed'].'</p>'."\n";
		}
		// ALLOW COMMENTS : ON
		else {
			// Formulaire commun
			$form .= '<form id="form-commentaire" class="form-commentaire" method="post" action="'.'?'.$_SERVER['QUERY_STRING'].'" >'."\n";
			$form .= "\t".'<fieldset class="field">'."\n";
			$form .= form_formatting_toolbar(FALSE);
			$form .= "\t\t".'<textarea class="commentaire" name="commentaire" required="" placeholder="'.$GLOBALS['lang']['label_commentaire'].'" id="commentaire" cols="50" rows="10">'.$form_cont['comment'].'</textarea>'."\n";
			$form .= "\t".'</fieldset>'."\n";
			$form .= "\t".'<fieldset class="infos">'."\n";
			$form .= "\t\t".'<label>'.$GLOBALS['lang']['label_dp_pseudo'].'<input type="text" name="auteur" placeholder="John Doe" required="" value="'.$form_cont['author'].'" size="25" class="text" /></label>'."\n";
			$form .= "\t\t".'<label>'.(($GLOBALS['require_email'] == 1) ? $GLOBALS['lang']['label_dp_email_required'] : $GLOBALS['lang']['label_dp_email']).'<input type="email" name="email" placeholder="mail@example.com" '.$required.' value="'.$form_cont['e_mail'].'" size="25" /></label>'."\n";
			$form .= "\t\t".'<label>'.$GLOBALS['lang']['label_dp_webpage'].'<input type="url" name="webpage" placeholder="http://www.example.com" value="'.$form_cont['webpage'].'" size="25" /></label>'."\n";
			$form .= "\t\t".'<label>'.$GLOBALS['lang']['label_dp_captcha'].'<b>'.en_lettres($GLOBALS['captcha']['x']).'</b> &#x0002B; <b>'.en_lettres($GLOBALS['captcha']['y']).'</b> '.'<input type="number" name="captcha" autocomplete="off" value="" class="text" /></label>'."\n";
			$form .= "\t\t".hidden_input('_token', $GLOBALS['captcha']['hash']);
			$form .= "\t\t".hidden_input('_verif_envoi', '1');
			$form .= "\t".'</fieldset><!--end info-->'."\n";
			$form .= "\t".'<fieldset class="subsc"><!--begin cookie asking -->'."\n";
			$form .= "\t\t".'<input class="check" type="checkbox" id="allowcuki" name="allowcuki"'.$cookie_checked.' /><label for="allowcuki">'.$GLOBALS['lang']['comment_cookie'].'</label>'.'<br/>'."\n";
			$form .= "\t\t".'<input class="check" type="checkbox" id="subscribe" name="subscribe"'.$subscribe_checked.' /><label for="subscribe">'.$GLOBALS['lang']['comment_subscribe'].'</label>'."\n";
			$form .= "\t".'</fieldset><!--end cookie asking-->'."\n";
			$form .= "\t".'<fieldset class="buttons">'."\n";
			$form .= "\t\t".'<input class="submit" type="submit" name="enregistrer" value="'.$GLOBALS['lang']['envoyer'].'" />'."\n";
			$form .= "\t\t".'<input class="submit" type="submit" name="previsualiser" value="'.$GLOBALS['lang']['preview'].'" />'."\n";
			$form .= "\t".'</fieldset><!--end buttons-->'."\n";
			$form_html .= $form;
			if ($GLOBALS['comm_defaut_status'] == '0') { // petit message en cas de moderation a-priori
				$form_html .= "\t\t".'<div class="need-validation">'.$GLOBALS['lang']['comment_need_validation'].'</div>'."\n";
			}
			$form_html .= '</form>'."\n";
		}
	}

	return $form_html;
}

