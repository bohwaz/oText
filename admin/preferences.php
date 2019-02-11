<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

require_once 'inc/boot.php';
operate_session();

/* FORMULAIRE NORMAL DES PRÉFÉRENCES */
function afficher_form_prefs($erreurs = '') {
	$submit_box = '<div class="submit-bttns">'."\n";
	$submit_box .= hidden_input('_verif_envoi', '1');
	$submit_box .= hidden_input('token', new_token());
	$submit_box .= '<button class="submit button-cancel" type="button" onclick="goToUrl(\'preferences.php\');" >'.$GLOBALS['lang']['annuler'].'</button>'."\n";
	$submit_box .= '<button class="submit button-submit" type="submit" name="enregistrer">'.$GLOBALS['lang']['enregistrer'].'</button>'."\n";
	$submit_box .= '</div>'."\n";


	echo '<form id="preferences" method="post" action="'.basename($_SERVER['SCRIPT_NAME']).'" >' ;
		echo erreurs($erreurs);
		$fld_user = '<div role="group" class="pref">'; /* no fieldset because browset can’t style them correctly */
		$fld_user .= '<div class="form-legend"><legend class="legend-user">'.$GLOBALS['lang']['prefs_legend_utilisateur'].'</legend></div>'."\n";
		$fld_user .= '<div class="form-lines">'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="auteur">'.$GLOBALS['lang']['pref_auteur'].'</label>'."\n";
		$fld_user .= "\t".'<input type="text" id="auteur" name="auteur" size="30" value="'.(empty($GLOBALS['auteur']) ? htmlspecialchars(USER_LOGIN) : $GLOBALS['auteur']).'" class="text" />'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="email">'.$GLOBALS['lang']['pref_email'].'</label>'."\n";
		$fld_user .= "\t".'<input type="text" id="email" name="email" size="30" value="'.$GLOBALS['email'].'" class="text" />'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="nomsite">'.$GLOBALS['lang']['pref_nom_site'].'</label>'."\n";
		$fld_user .= "\t".'<input type="text" id="nomsite" name="nomsite" size="30" value="'.$GLOBALS['nom_du_site'].'" class="text" />'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="racine">'.$GLOBALS['lang']['pref_racine'].'</label>'."\n";
		$fld_user .= "\t".'<input type="text" id="racine" name="racine" size="30" value="'.$GLOBALS['racine'].'" class="text" />'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="description">'.$GLOBALS['lang']['label_dp_description'].'</label>'."\n";
		$fld_user .= "\t".'<textarea id="description" name="description" cols="35" rows="2" class="text" >'.$GLOBALS['description'].'</textarea>'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '<p>'."\n";
		$fld_user .= "\t".'<label for="keywords">'.$GLOBALS['lang']['pref_keywords'].'</label>';
		$fld_user .= "\t".'<textarea id="keywords" name="keywords" cols="35" rows="2" class="text" >'.$GLOBALS['keywords'].'</textarea>'."\n";
		$fld_user .= '</p>'."\n";
		$fld_user .= '</div>'."\n";
		$fld_user .= $submit_box;
		$fld_user .= '</div>';
	echo $fld_user;

		$fld_securite = '<div role="group" class="pref">';
		$fld_securite .= '<div class="form-legend"><legend class="legend-securite">'.$GLOBALS['lang']['prefs_legend_securite'].'</legend></div>'."\n";
		$fld_securite .= '<div class="form-lines">'."\n";
		$fld_securite .= '<p>'."\n";
		$fld_securite .= "\t".'<label for="identifiant">'.$GLOBALS['lang']['pref_identifiant'].'</label>'."\n";
		$fld_securite .= "\t".'<input type="text" id="identifiant" name="identifiant" size="30" value="'.htmlspecialchars(USER_LOGIN).'" class="text" />'."\n";
		$fld_securite .= '</p>'."\n";
		$fld_securite .= '<p>'."\n";
		$fld_securite .= "\t".'<label for="mdp">'.$GLOBALS['lang']['pref_mdp'].'</label>';
		$fld_securite .= "\t".'<input type="password" id="mdp" name="mdp" size="30" value="" class="text" autocomplete="off" />'."\n";
		$fld_securite .= '</p>'."\n";
		$fld_securite .= '<p>'."\n";
		$fld_securite .= "\t".'<label for="mdp_rep">'.$GLOBALS['lang']['pref_mdp_nouv'].'</label>';
		$fld_securite .= "\t".'<input type="password" id="mdp_rep" name="mdp_rep" size="30" value="" class="text" autocomplete="off" />'."\n";
		$fld_securite .= '</p>'."\n";
		$fld_securite .= '</div>';
		$fld_securite .= $submit_box;
		$fld_securite .= '</div>';
	echo $fld_securite;

		$fld_apparence = '<div role="group" class="pref">';
		$fld_apparence .= '<div class="form-legend"><legend class="legend-apparence">'.$GLOBALS['lang']['prefs_legend_apparence'].'</legend></div>'."\n";
		$fld_apparence .= '<div class="form-lines">'."\n";
		$fld_apparence .= '<p>'."\n";
		$fld_apparence .= form_select('theme', liste_themes(), $GLOBALS['theme_choisi'],$GLOBALS['lang']['pref_theme']);
		$fld_apparence .= '</p>'."\n";
		$fld_apparence .= '<p>'."\n";
		$fld_apparence .= form_select('nb_maxi', array('5'=>'5', '10'=>'10', '15'=>'15', '20'=>'20', '25'=>'25', '50'=>'50'), $GLOBALS['max_bill_acceuil'],$GLOBALS['lang']['pref_nb_maxi']);
		$fld_apparence .= '</p>'."\n";
		$nbs = array('10'=>'10', '25'=>'25', '50'=>'50', '100'=>'100', '300'=>'300', '-1' => $GLOBALS['lang']['pref_all']);
		$fld_apparence .= '<p>'."\n";
		$fld_apparence .= form_select('nb_list', $nbs, $GLOBALS['max_bill_admin'],$GLOBALS['lang']['pref_nb_list']);
		$fld_apparence .= '</p>'."\n";
		$fld_apparence .= '<p>'."\n";
		$fld_apparence .= form_select('nb_list_com', $nbs, $GLOBALS['max_comm_admin'],$GLOBALS['lang']['pref_nb_list_com']);
		$fld_apparence .= '</p>'."\n";
		$fld_apparence .= '</div>'."\n";
		$fld_apparence .= $submit_box;
		$fld_apparence .= '</div>';
	echo $fld_apparence;

		$fld_dateheure = '<div role="group" class="pref">';
		$fld_dateheure .= '<div class="form-legend"><legend class="legend-dateheure">'.$GLOBALS['lang']['prefs_legend_langdateheure'].'</legend></div>'."\n";
		$fld_dateheure .= '<div class="form-lines">'."\n";
		$fld_dateheure .= '<p>'."\n";
		$fld_dateheure .= form_select('langue', $GLOBALS['langs'], $GLOBALS['lang']['id'], $GLOBALS['lang']['pref_langue']);
		$fld_dateheure .= '</p>'."\n";
		$fld_dateheure .= '<p>'."\n";
		$jour_l = jour_en_lettres(date('d'), date('m'), date('Y'));
		$mois_l = mois_en_lettres(date('m'));
		$opts = array (
			'0' => date('d/m/Y'),                                     // 05/07/2011
			'1' => date('m/d/Y'),                                     // 07/05/2011
			'2' => date('d').' '.$mois_l.' '.date('Y'),               // 05 juillet 2011
			'3' => $jour_l.' '.date('d').' '.$mois_l.' '.date('Y'),   // mardi 05 juillet 2011
			'4' => $jour_l.' '.date('d').' '.$mois_l,                 // mardi 05 juillet
			'5' => $mois_l.' '.date('d').', '.date('Y'),              // juillet 05, 2011
			'6' => $jour_l.', '.$mois_l.' '.date('d').', '.date('Y'), // mardi, juillet 05, 2011
			'7' => date('Y-m-d'),                                     // 2011-07-05
			'8' => substr($jour_l,0,3).'. '.date('d').' '.$mois_l,    // ven. 14 janvier
		);
		$fld_dateheure .= form_select('format_date', $opts, $GLOBALS['format_date'], $GLOBALS['lang']['pref_format_date']);
		$fld_dateheure .= '</p>'."\n";
		$fld_dateheure .= '<p>'."\n";
		$opts = array (
			'0' => date('H\:i\:s'),   // 23:56:04
			'1' => date('H\:i'),      // 23:56
			'2' => date('h\:i\:s A'), // 11:56:04 PM
			'3' => date('h\:i A'),    // 11:56 PM
		);
		$fld_dateheure .= form_select('format_heure', $opts, $GLOBALS['format_heure'], $GLOBALS['lang']['pref_format_heure']);
		$fld_dateheure .= '</p>'."\n";
		$fld_dateheure .= '<p>'."\n";
		$fld_dateheure .= form_fuseau_horaire($GLOBALS['fuseau_horaire']);
		$fld_dateheure .= '</p>'."\n";
		$fld_dateheure .= '</div>'."\n";
		$fld_dateheure .= $submit_box;
		$fld_dateheure .= '</div>';
	echo $fld_dateheure;

		$fld_cfg_blog = '<div role="group" class="pref">';
		$fld_cfg_blog .= '<div class="form-legend"><legend class="legend-blogcomm">'.$GLOBALS['lang']['prefs_legend_configblog'].'</legend></div>'."\n";
		$fld_cfg_blog .= '<div class="form-lines">'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_checkbox('activer_categories', $GLOBALS['activer_categories'], $GLOBALS['lang']['pref_categories'] );
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_checkbox('auto_keywords', $GLOBALS['automatic_keywords'], $GLOBALS['lang']['pref_automatic_keywords'] );
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_select('arts_id_format', array('1' => $GLOBALS['lang']['pref_id_format_ymdhis'], '0' => $GLOBALS['lang']['pref_id_format_rand6']), $GLOBALS['format_id_blog'],$GLOBALS['lang']['pref_id_format']);
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_checkbox('global_comments', $GLOBALS['global_com_rule'], $GLOBALS['lang']['pref_allow_global_coms']);
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_checkbox('require_email', $GLOBALS['require_email'], $GLOBALS['lang']['pref_force_email']);
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '<p>'."\n";
		$fld_cfg_blog .= form_select('comm_defaut_status', array('1' => $GLOBALS['lang']['pref_comm_black_list'], '0' => $GLOBALS['lang']['pref_comm_white_list']), $GLOBALS['comm_defaut_status'],$GLOBALS['lang']['pref_comm_BoW_list']);
		$fld_cfg_blog .= '</p>'."\n";
		$fld_cfg_blog .= '</div>'."\n";
		$fld_cfg_blog .= $submit_box;
		$fld_cfg_blog .= '</div>';
	echo $fld_cfg_blog;

		$fld_cfg_linx = '<div role="group" class="pref">';
		$fld_cfg_linx .= '<div class="form-legend"><legend class="legend-links">'.$GLOBALS['lang']['prefs_legend_configlinx'].'</legend></div>'."\n";
		$fld_cfg_linx .= '<div class="form-lines">'."\n";
		// nb liens côté admin
		$nbs = array('50'=>'50', '100'=>'100', '200'=>'200', '300'=>'300', '500'=>'500', '-1' => $GLOBALS['lang']['pref_all']);
		$fld_cfg_linx .= '<p>'."\n";
		$fld_cfg_linx .= form_select('nb_list_linx', $nbs, $GLOBALS['max_linx_admin'], $GLOBALS['lang']['pref_nb_list_linx']);
		$fld_cfg_linx .= '</p>'."\n";
		// partage de fichiers !pages : télécharger dans fichiers automatiquement ?
		$nbs = array('0'=> $GLOBALS['lang']['non'], '1'=> $GLOBALS['lang']['oui'], '2' => $GLOBALS['lang']['pref_ask_everytime']);
		$fld_cfg_linx .= '<p>'."\n";
		$fld_cfg_linx .= form_select('dl_link_to_files', $nbs, $GLOBALS['dl_link_to_files'], $GLOBALS['lang']['pref_linx_dl_auto']);
		$fld_cfg_linx .= '</p>'."\n";
		// lien à glisser sur la barre des favoris
		$a = explode('/', dirname($_SERVER['SCRIPT_NAME']));
		$fld_cfg_linx .= '<p>';
		$fld_cfg_linx .= '<label>'.$GLOBALS['lang']['pref_label_bookmark_lien'].'</label>'."\n";
		$fld_cfg_linx .= '<a class="dnd-to-favs" onclick="alert(\''.$GLOBALS['lang']['pref_label_bookmark_lien'].'\');return false;" href="javascript:javascript:(function(){window.open(\''.$GLOBALS['racine'].$a[count($a)-1].'/links.php?url=\'+encodeURIComponent(location.href));})();">Save link</a>';
		$fld_cfg_linx .= '</p>'."\n";
		$fld_cfg_linx .= '</div>'."\n";
		$fld_cfg_linx .= $submit_box;
		$fld_cfg_linx .= '</div>';
	echo $fld_cfg_linx;

		/* TODO
		- Open=read ? + button to mark as read in HTML
		- Export OPML
		*/
		$fld_cfg_rss = '<div role="group" class="pref">';
		$fld_cfg_rss .= '<div class="form-legend"><legend class="legend-rss">'.$GLOBALS['lang']['prefs_legend_configrss'].'</legend></div>'."\n";
		$fld_cfg_rss .= '<div class="form-lines">'."\n";
		$fld_cfg_rss .= '<p>'."\n";
		$a = explode('/', dirname($_SERVER['SCRIPT_NAME']));
		$fld_cfg_rss .= '<label>'.$GLOBALS['lang']['pref_label_crontab_rss'].'</label>'."\n";
		$fld_cfg_rss .= '<a onclick="prompt(\''.$GLOBALS['lang']['pref_alert_crontab_rss'].'\', \'0 *  *   *   *   wget --spider -qO- '.$GLOBALS['racine'].$a[count($a)-1].'/ajax/rss.ajax.php?guid='.BLOG_UID.'&refresh_all'.'\');return false;" href="#">Afficher ligne Cron</a>';
		$fld_cfg_rss .= '</p>'."\n";
		$fld_cfg_rss .= '<p>'."\n";
		$fld_cfg_rss .= "\t".'<label>'.$GLOBALS['lang']['pref_rss_go_to_imp-export'].'</label>'."\n";
		$fld_cfg_rss .= "\t".'<a href="maintenance.php">'.$GLOBALS['lang']['label_import-export'].'</a>'."\n";
		$fld_cfg_rss .= '</p>'."\n";
		$fld_cfg_rss .= '<p>'."\n";
		$fld_cfg_rss .= '</p>'."\n";
		$fld_cfg_rss .= '</div>'."\n";
		$fld_cfg_rss .= $submit_box;
		$fld_cfg_rss .= '</div>';
	echo $fld_cfg_rss;

		$fld_cfg_agenda = '<div role="group" class="pref">';
		$fld_cfg_agenda .= '<div class="form-legend"><legend class="legend-agenda">'.$GLOBALS['lang']['prefs_legend_configagenda'].'</legend></div>'."\n";
		$fld_cfg_agenda .= '<div class="form-lines">'."\n";
		$fld_cfg_agenda .= '<p>'."\n";
		$nbs = array('eventCalendar'=> $GLOBALS['lang']['pref_agenda_taskcalendar'], 'eventlist'=> $GLOBALS['lang']['pref_agenda_tasklist']);
		$fld_cfg_agenda .= '<p>'."\n";
		$fld_cfg_agenda .= form_select('default_agenda_display', $nbs, $GLOBALS['agenda_display'], $GLOBALS['lang']['pref_agenda_format']);
		$fld_cfg_agenda .= '</p>'."\n";
		$fld_cfg_agenda .= '</p>'."\n";
		$fld_cfg_agenda .= '<p>'."\n";
		$fld_cfg_agenda .= '</p>'."\n";
		$fld_cfg_agenda .= '</div>'."\n";
		$fld_cfg_agenda .= $submit_box;
		$fld_cfg_agenda .= '</div>';
	echo $fld_cfg_agenda;


		$fld_maintenance = '<div role="group" class="pref">';
		$fld_maintenance .= '<div class="form-legend"><legend class="legend-sweep">'.$GLOBALS['lang']['titre_maintenance'].'</legend></div>'."\n";
		$fld_maintenance .= '<div class="form-lines">'."\n";
		$fld_maintenance .= '<p>'."\n";
		$fld_maintenance .= "\t".'<label>'.$GLOBALS['lang']['pref_go_to_maintenance'].'</label>'."\n";
		$fld_maintenance .= "\t".'<a href="maintenance.php">Maintenance</a>'."\n";
		$fld_maintenance .= '</p>'."\n";
		$fld_maintenance .= '</div>'."\n";
		$fld_maintenance .= '</div>';
	echo $fld_maintenance;


	echo '</form>'."\n";
}


$erreurs_form = array();
if (isset($_POST['_verif_envoi'])) {
	$erreurs_form = valider_form_preferences();
	if (empty($erreurs_form)) {
		if ( (fichier_user() === TRUE) and (fichier_prefs() === TRUE) ) {
			redirection(basename($_SERVER['SCRIPT_NAME']).'?msg=confirm_prefs_maj');
			exit();
		}
	}
}

// DEBUT PAGE
afficher_html_head($GLOBALS['lang']['preferences'], "preferences");
afficher_topnav($GLOBALS['lang']['preferences'], ''); #top

echo '<div id="axe">'."\n";
echo '<div id="page">'."\n";

afficher_form_prefs($erreurs_form);

echo "\n".'<script src="style/scripts/javascript.js"></script>'."\n";

footer($begin);