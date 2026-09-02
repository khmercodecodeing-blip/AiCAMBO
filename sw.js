/**
 * Minimal service worker — caches static assets so the site feels app-like
 * and keeps working (cached shell) on flaky mobile connections.
 */
const CACHE_NAME = 'coursehub-static-v1';
const STATIC_ASSETS = [
    '/public/assets/css/style.css',
    '/public/assets/js/app.js',
    '/public/assets/images/icons/icon-192.png',
    '/public/assets/images/icons/icon-512.png',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS)).catch(() => {})
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key)))
        )
    );
    self.clients.claim();
});

// Cache-first for static assets only; everything else (pages, APIs) always goes to the network
self.addEventListener('fetch', (event) => {
    const url = new URL(event.request.url);
    const isStaticAsset = url.pathname.startsWith('/public/assets/');

    if (event.request.method !== 'GET' || !isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;
            return fetch(event.request).then((response) => {
                const clone = response.clone();
                caches.open(CACHE_NAME).then((cache) => cache.put(event.request, clone));
                return response;
            });
        })
    );
});
