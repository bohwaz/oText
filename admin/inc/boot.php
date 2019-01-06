<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

define('BT_ROOT_ADMIN', dirname(dirname(dirname(__file__))).'/');

define('IS_IN_ADMIN', true);

if ( !file_exists('../config/user.ini') || !file_exists('../config/prefs.php') ) {
	header('Location: install.php');
	exit;
}

require_once '../inc/boot.php';

require_once 'inc/lang.php';

$GLOBALS['db_handle'] = open_base();

