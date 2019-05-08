
var DATA_CACHE = 'otext-data-cache-v2';
var SHELL_CACHE = 'otext-shell-cache-v2';

// when the PWA is installed (on the first time)
// we cache all the elements we need to cache
self.addEventListener('install', function(e) {
	console.log('[ServiceWorker] Install');
	e.waitUntil(
		caches.open(SHELL_CACHE).then(function(cache) {
			console.log('[ServiceWorker] Caching app shell');
			return cache.addAll(
				[
					'',
					'feed.php',
					'style/scripts/javascript.js',
					'style/styles/style.css.php',
					'style/webfonts/icons.woff2',
					'style/webfonts/roboto-300.woff2',
					'style/webfonts/roboto-400.woff2',
					'style/webfonts/roboto-500.woff2',
					'style/webfonts/roboto-700.woff2',
				]
			);
		})
	);
});

// after install, and each time the PWA is accessed,
// we remove old cached elements
self.addEventListener('activate', function(e) {
	console.log('[ServiceWorker] Activate');
	e.waitUntil(
		caches.keys().then(function(keyList) {
			return Promise.all(keyList.map(function(key) {
				if (key !== SHELL_CACHE && key !== DATA_CACHE) {
					console.log('[ServiceWorker] Removing old cache', key);
					return caches.delete(key);
				}
			}));
		})
	);
	return self.clients.claim();
});


// when the app is loaded, it is asking for files to load
// If a file is NOT in cache (for example a "data update" request),
// a network request is made.

self.addEventListener('fetch', function(e) {
	console.log('[Service Worker] Fetch', e.request.url);

	// some request must not be cached: json/ajax request, and external files (like img/media in rss feeds)
	if (e.request.url.indexOf('ajax.php') > -1 || ((new URL(e.request.url)).hostname) !== (self.location.host) ) {
		console.log('... [Service Worker] data-request or external URL > doesn’t need cache POST', e.request.url);

			// When the request URL contains dataUrl, the app is asking for fresh
			// data. In this case, the service worker always goes to the network
			// (no caches for this).
			fetch(e.request.url, {method: e.request.method}).then(function(response) {
				if (response.ok) {
					console.log('... [Service Worker] fetched from network', e.request.url)
					return response;
				} else {
					console.log('... [Service Worker] Can’t fetch (network error)', e.request.url)
			        throw new Error("HTTP error, status = " + response.status);
				}
			}).catch(function() {
				console.log('... [Service Worker] Network Error (1)', e.request.url)
				return new Response("Network error", {"ok" : false, "status" : 408, "headers" : {"Content-Type" : "text/plain"}});
			});

	}		
	
	// The app is asking for non-data related files (like app-shell files). In this scenario the app uses the "Cache, falling back
	// to the network" offline strategy: https://jakearchibald.com/2014/offline-cookbook/#cache-falling-back-to-network
	// if a network request is to be made, it is put in cache afterwards
	 else {
		console.log('... [Service Worker] Shell file : needs to be in cache', e.request.url);
		e.respondWith(
			caches.open(SHELL_CACHE).then(function(cache) {
				return (
					caches.match(e.request).then(function(response) {
						console.log('... ... [Service Worker] File is in cache', e.request.url);

						if (response.ok) {
							console.log('... ... ... [Service Worker] File fetched from cache', e.request.url);
							return response;
						} else {
							console.log('... ... ... [Service Worker] Error in cache', e.request.url);
							throw new Error("Not in cache " + response.status);
						}

					// file is not in cache : try to get from network, then cache it for next time
					}).catch(function() {
						return(
							fetch(e.request).then(function(response) {
								// done and ok : cache it
								console.log('... ... [Service Worker] File not in cache, fetch from Newtwork', e.request.url);
								if (response.ok) {
									console.log('... ... ... [Service Worker] Fetch from network received', e.request.url);
									cache.put(e.request, response.clone());
									console.log('... ... ... [Service Worker] File is now cached', e.request.url);
									return response;
								// not ok > throw error
								} else {
									console.log('... ... ... [Service Worker] Can’t fetch file from network (is offline?)', e.request.url)
									throw new Error("HTTP error, status = " + response.status);
								}

							// can’t fetch > return a network error
							}).catch(function() {
								console.log('... ... ... [Service Worker] Network Error (2)', e.request.url)
								return new Response("Network error", {"ok" : false, "status" : 404, "headers" : {"Content-Type" : "text/plain"}});
							})
						);
					})
				)
			})
		);
	}
});

