// Lymity IA — Service Worker (shell cache only, no sensitive data cached)
const CACHE_NAME = 'lymity-shell-v1';

const SHELL_ASSETS = [
    '/app',
    '/app/approvals',
];

// Install: cache shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(SHELL_ASSETS).catch(() => {
                // Silently fail if assets unavailable during install
            });
        })
    );
    self.skipWaiting();
});

// Activate: remove old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch: network-first for API and POST; cache-first for static shell only
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Never cache API, auth, or POST requests
    if (
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/logout') ||
        request.method !== 'GET'
    ) {
        return; // pass through to network
    }

    // For app pages: network-first with cache fallback
    if (url.pathname.startsWith('/app')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Only cache successful responses
                    if (response && response.status === 200) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() => caches.match(request))
        );
        return;
    }

    // Static assets: cache-first
    if (
        url.pathname.match(/\.(css|js|woff2?|png|jpg|svg|ico)$/)
    ) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request))
        );
    }
});
