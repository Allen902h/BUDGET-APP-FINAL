const CACHE_NAME = 'budget-app-v4';
const CORE_ASSETS = [
    '/',
    '/manifest.json',
    '/css/app.css',
    '/js/app.js',
    '/icons/icon.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(CORE_ASSETS))
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

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const requestUrl = new URL(event.request.url);
    const isSameOrigin = requestUrl.origin === self.location.origin;
    const isStaticAsset =
        isSameOrigin &&
        (
            requestUrl.pathname.startsWith('/css/') ||
            requestUrl.pathname.startsWith('/js/') ||
            requestUrl.pathname.startsWith('/icons/') ||
            requestUrl.pathname === '/manifest.json' ||
            requestUrl.pathname === '/favicon.ico'
        );

    const isDocumentRequest = event.request.mode === 'navigate' || event.request.destination === 'document';

    if (isDocumentRequest) {
        event.respondWith(
            fetch(event.request)
                .then((response) => response)
                .catch(() => caches.match('/'))
        );

        return;
    }

    if (! isStaticAsset) {
        return;
    }

    event.respondWith(
        caches.match(event.request).then((cachedResponse) => {
            if (cachedResponse) {
                return cachedResponse;
            }

            return fetch(event.request)
                .then((networkResponse) => {
                    const copy = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(event.request, copy));
                    return networkResponse;
                })
                .catch(() => cachedResponse);
        })
    );
});
