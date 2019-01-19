<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

define('BT_ROOT', dirname(dirname(dirname(__file__))).'/');

define('IS_IN_ADMIN', true);

if ( !file_exists(BT_ROOT.'/config/user.ini') || !file_exists(BT_ROOT.'/config/prefs.php') ) {
	header('Location: install.php');
	exit;
}

require_once BT_ROOT.'/inc/boot.php';

require_once DIR_ADMIN.'/inc/lang.php';

$GLOBALS['db_handle'] = open_base();

