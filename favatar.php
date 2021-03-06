<?php
// *** LICENSE ***
// oText is free software.
//
// By Fred Nassar (2006) and Timo Van Neerden (since 2010)
// See "LICENSE" file for info.
// *** LICENSE ***

if (!isset($_GET['w'], $_GET['q'])) {
	header("HTTP/1.0 400 Bad Request"); exit;
}


function creer_dossier($dossier) {
	if ( !is_dir($dossier) ) {
		if (mkdir($dossier, 0777, true) === TRUE) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	return TRUE; // si le dossier existe déjà.
}

$expire = time() -60*60*24*7*365 ;  // default: 1 year

if ($_GET['w'] == 'favicon') {
	// target dir
	creer_dossier('var/cache/favicons/');
	$target_dir = 'var/cache/favicons';

	// source file
	$domain = parse_url($_GET['q'], PHP_URL_HOST); // full URL given?
	if ($domain === NULL) { $domain = parse_url($_GET['q'], PHP_URL_PATH); } // or only domain name?
	if ($domain === NULL) { header("HTTP/1.0 400 Bad Request"); exit; } // or some unusable crap?
	$source_file = 'http://www.google.com/s2/favicons?domain='.$domain;
	// dest file
	$target_file = $target_dir.'/'.md5($domain).'.png';
	// expiration delay
	$expire = time() -60*60*24*7*365 ;  // default: 1 year
}

elseif ($_GET['w'] == 'gravatar') {
	// target dir
	creer_dossier('var/cache/gravatar/');

	$target_dir = 'var/cache/gravatar/';
	// source file
	if (strlen($_GET['q']) !== 32) { header("HTTP/1.0 400 Bad Request"); exit; }  // g is 32 character long ? if no, die.
	$hash = preg_replace("[^a-f0-9]", "", $_GET['q'] );  // strip out anything that doesn't belong in a md5 hash
	if (strlen($hash) != 32) { header("HTTP/1.0 400 Bad Request"); exit; }  // still 32 characters ? if no, given hash wasn't genuine. die.
	$target_file = $hash.'.png';
	$s = (isset($_GET['s']) and is_numeric($_GET['s'])) ? htmlspecialchars($_GET['s']) : 48; // try to get size
	$d = (isset($_GET['d'])) ? htmlspecialchars($_GET['d']) : 'monsterid'; // try to get substitute image
	$source_file = 'http://www.gravatar.com/avatar/'.$hash.'?s='.$s.'&d='.$d;
	// dest file
	$target_file = $target_dir.'/'.md5($hash).'.png';
	// expiration delay
	$expire = time() -60*60*24*30 ;  // default: 30 days
}

else {
	// wrong request: returning error 400.
	header("HTTP/1.0 400 Bad Request"); exit;
}

/* processing :
	- testing cache file
	- gathering source file
	- converting to PNG and saving
	- sending image to browser
*/

// cached file existing & expired : mark to remove it
$force_new = FALSE;
if (file_exists($target_file) and filemtime($target_file) < $expire) {
	$force_new = TRUE;
}

// no cached file or expired
if (!file_exists($target_file) or $force_new === TRUE) {
	// request
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $source_file);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	$file_content = curl_exec($curl_handle);
	curl_close($curl_handle);
	if ($file_content == NULL) { // impossible request
		header("HTTP/1.0 404 Not Found"); exit;
	}
	// new request is a succes, delete old and save new file
	if ($force_new === TRUE) {
		unlink($target_file);
	}
	if (!is_dir($target_dir)) { mkdir($target_dir); }
	file_put_contents($target_file, $file_content);

	// testing format
	$imagecheck = getimagesize($target_file);
	if ($imagecheck['mime'] !== 'image/png') {
		imagepng(imagecreatefromjpeg($target_file), $target_file);  // if not, creating PNG and replacing
	}

	// resizing to 32x32px
	
	$width = 32;
	$height = 32;

	// image actual ratio
	$orig_ratio = $imagecheck[0] / $imagecheck[1];

	// destinatino image sizes
	if ($width/$height > $orig_ratio) {
		$width = $height*$orig_ratio;
	} else {
		$height = $width/$orig_ratio;
	}

	// Resizing (keeping alpha channel)
	$image = imagecreatefrompng($target_file);

	$newImg = imagecreatetruecolor($width, $height);
	imagealphablending($newImg, false);
	imagesavealpha($newImg, true);
	imagefilledrectangle($newImg, 0, 0, $width, $height, imagecolorallocate($newImg, 255, 255, 255));
	imagecopyresampled($newImg, $image, 0, 0, 0, 0, $width, $height, $imagecheck[0], $imagecheck[1]);

	imagepng($newImg, $target_file, 9);

}

// send file to browser
header('Content-Type: image/png');
header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($target_file)).' GMT');
header('Content-Length: ' . filesize($target_file));
header('Cache-Control: public, max-age=2628000');
readfile($target_file);
exit;
