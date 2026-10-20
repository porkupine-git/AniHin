// Production-Ready Service Worker for Zanora Anime Platform
// Version 3.1.0 - Resilient Offline & Static Asset Cache

const CACHE_VERSION = 'v3.1.0';
const CACHE_NAME = `zanora-${CACHE_VERSION}`;

// Protect against failing 3rd-party ad / push notification worker imports
if (typeof self.importScripts === 'function') {
    const rawImportScripts = self.importScripts.bind(self);
    self.importScripts = function(...urls) {
        for (const url of urls) {
            try {
                rawImportScripts(url);
            } catch (err) {
                console.warn('[Zanora SW] Gracefully skipped inaccessible worker script:', url, err);
            }
        }
    };
}

// Global unhandled error listener for worker
self.addEventListener('error', (event) => {
    console.warn('[Zanora SW] Handled background worker warning:', event.message);
    event.preventDefault();
});

// Pre-cached core static assets
const STATIC_ASSETS = [
    '/css/styles.min.css',
    '/css/home.css',
    '/js/app.min.js',
    '/js/watch.min.js'
];

// Install Event: Cache essential static assets safely
self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return Promise.allSettled(
                STATIC_ASSETS.map(url => 
                    fetch(url).then(response => {
                        if (response.ok) return cache.put(url, response);
                    }).catch(() => {})
                )
            );
        })
    );
});

// Activate Event: Purge outdated caches & claim clients immediately
self.addEventListener('activate', (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then((keys) =>
                Promise.all(
                    keys.map((key) => {
                        if (key.startsWith('zanora-') && key !== CACHE_NAME) {
                            return caches.delete(key);
                        }
                    })
                )
            )
        ])
    );
});

// Fetch Event: Multi-tier caching strategy
self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // 1. Bypass non-GET requests, extension requests, or cross-origin requests
    if (request.method !== 'GET' || !url.protocol.startsWith('http')) return;

    // 2. Bypass API, AJAX, Auth, Admin, and video proxy/range requests
    if (
        url.pathname.startsWith('/ajax/') ||
        url.pathname.startsWith('/auth/') ||
        url.pathname.startsWith('/user/') ||
        url.pathname.startsWith('/admin') ||
        request.headers.has('range')
    ) {
        return;
    }

    // 3. HTML Navigation: Network-First Strategy with Cache Fallback
    if (request.mode === 'navigate' || request.headers.get('accept')?.includes('text/html')) {
        event.respondWith(
            fetch(request)
                .then((networkResponse) => {
                    if (networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // 4. CSS / JS Assets: Stale-While-Revalidate Strategy
    if (request.destination === 'style' || request.destination === 'script' || url.pathname.endsWith('.css') || url.pathname.endsWith('.js')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                const fetchPromise = fetch(request).then((networkResponse) => {
                    if (networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                }).catch(() => cachedResponse);

                return cachedResponse || fetchPromise;
            })
        );
        return;
    }

    // 5. Static Images / Icons: Cache-First Strategy
    if (
        request.destination === 'image' ||
        url.pathname.startsWith('/avatar/') ||
        url.pathname.startsWith('/public/logo/') ||
        url.pathname.startsWith('/public/images/') ||
        url.pathname.startsWith('/images/')
    ) {
        var imgReq = request;
        if (url.hostname.includes('zanora.lol') && url.pathname.startsWith('/avatar/')) {
            imgReq = new Request(url.pathname, {
                method: request.method,
                headers: request.headers,
                mode: 'same-origin',
                credentials: request.credentials
            });
        }
        event.respondWith(
            caches.match(imgReq).then((cachedResponse) => {
                if (cachedResponse) return cachedResponse;

                return fetch(imgReq).then((networkResponse) => {
                    if (networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(imgReq, responseClone));
                    }
                    return networkResponse;
                }).catch(() => {
                    return new Response('', { status: 404, statusText: 'Not Found' });
                });
            })
        );
        return;
    }
});
