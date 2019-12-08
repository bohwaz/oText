# oText

---

This is **oText**, a lightweight blog engine with several Cloud-related tools.

With oText you can hold a blog, share links, upload and share photos or files, follow RSS feeds, save notes, manage an agenda and contacts.

oText  is provided by Timo van Neerden (a.k.a. _le hollandais volant_), based upon the work of [Frederic Nassar](https://twitter.com/frednassar) and [Timo Van Neerden](https://lehollandaisvolant.net/).

![alt tag](screenshot.png?raw=true&v2)

---

## Features

 * Blog :
    * Comments
    * RSS / ATOM Feeds
    * Easy custom-theming
 * Links sharing (like Diigo, Delicious or Shaarli)
    * dedicated RSS for Links
 * RSS Reader
    * with Cron-updating capabilities (either local or distant)
 * Images/Files uploading and sharing
    * With drag & drop
 * Note saving tools (Google Keep style)
 * Agenda tool (Google Calendar style)
    * With ICS feed-link
 * Contacts manager
 * Import/Export (ICS (agenda), JSON (blog data), OPML (RSS), ZIP (all), HTML (links), VCard (contacts), XML (notes))
 * (new!) **Dark-Theme** : uses the browser setting (in Firefox: go to `about:config` and set/create `ui.systemUsesDarkTheme` set to `1`).


* PWA capable _(soon, for RSS, Notes, Agenda, Contacts)_

---

## Installation
 1. Unzip the downloaded Zip file into a folder
 2. Upload that folder to your site
 3. Use your browser to go to your site and tht folder
 4. Follow the few onscreen steps

---

## Minimal system requirements
### Server-side
Software & disk:
 * PHP 5.7+
 * min 1.5 Mb disk space (more userdata = more space needed)

 PHP-libraries:
 * PHP-PDO (with `php-sqlite` or/and `php-mysql`);
 * `php-curl` (for RSS reader, links sharing, comments icons)
 * `php-gd` (for comments icons / favicons);
 * `php-xml` (for RSS reader)
 * `php-zip` (for zip exporting function)
 * `php-mbstring` (for blog)

### Client-side requirements
 * A modern web-browser (HTML5, CSS3, ES6), either desktop or mobile
 * JavaScript & Cookies must be allowed.

---

## Legal Notice.

oText is based on a fork of [BlogoText](https://github.com/BlogoText/blogotext) and several other resources, listed in the LICENSE file.

## Important note

This is a **personnal** project I made for myself.

You are free to use it if you want to, but I won’t maintain features I do not use. As such, some features might be removed, added or altered (including the database) after an update.

Also, I am open to ideas, bugreports or requests, but keep in mind that I’ll probably say no to suggestions. Your idea might be good, but if I don’t need some feature for myself, I won’t implement it.
