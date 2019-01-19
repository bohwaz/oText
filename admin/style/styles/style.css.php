<?php
// gzip compression
if (extension_loaded('zlib') and ob_get_length() > 0) {
	ob_end_clean();
	ob_start("ob_gzhandler");
}
else {
	ob_start("ob_gzhandler");
}

header("Content-type: text/css; charset: UTF-8");

echo '@charset "utf-8";'."\n";

/* FOR MAINTENANCE: CSS FILES ARE SPLITED IN MULTIPLE FILES
-------------------------------------------------------------*/

/* General styles (layout, forms, multi-pages elements…) */
readfile('style-style.css');

/* Auth page */
readfile('style-auth.css');

/* Home page, with graphs */
readfile('style-graphs.css');

/* Article lists page */
readfile('style-articles.css');

/* Write page: new article form */
readfile('style-ecrire.css');

/* Comments page: forms+comm list */
readfile('style-commentaires.css');

/* Images and files: form + listing */
readfile('style-miniatures-files.css');

/* Links page: form + listing. */
readfile('style-liens.css');

/* RSS page: listing + forms */
readfile('style-rss.css');

/* Notes page */
readfile('style-notes.css');

/* Agenda page */
readfile('style-agenda.css');

/* Contacts page */
readfile('style-contacts.css');

/* Prefs + maintainance pages */
readfile('style-preferences.css');

/* Media-queries < 1100px */
readfile('style-mobile-lt1100px.css');

/* Media-queries < 850px */
readfile('style-mobile-lt850px.css');

/* Media-queries < 700px */
readfile('style-mobile-lt700px.css');

/* Custon UserCSS */
if (is_file('../../../config/custom-styles.css')) {
	readfile('../../../config/custom-styles.css');
}
