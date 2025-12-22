// Service Worker for Digital ID PWA
const CACHE_NAME = 'digital-id-v3'; // Updated to force cache clear for dropdown fixes
const RUNTIME_CACHE = 'digital-id-runtime-v3'; // Updated to force cache clear for dropdown fixes

// Assets to cache immediately - ONLY static assets, NO PHP pages with authentication
const STATIC_ASSETS = [
  '/assets/css/style.css',
  '/manifest.json'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then((cache) => {
        return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
      })
      .then(() => self.skipWaiting())
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((cacheName) => {
            return cacheName !== CACHE_NAME && cacheName !== RUNTIME_CACHE;
          })
          .map((cacheName) => {
            return caches.delete(cacheName);
          })
      );
    })
    .then(() => self.clients.claim())
  );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
  // Skip non-GET requests
  if (event.request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests
  if (!event.request.url.startsWith(self.location.origin)) {
    return;
  }

  // CRITICAL: Never cache PHP pages - they contain authentication state
  // Only cache static assets (CSS, JS, images, manifest, etc.)
  const url = new URL(event.request.url);
  const isPhpPage = url.pathname.endsWith('.php') || url.pathname === '/' || url.pathname === '';
  const isStaticAsset = url.pathname.match(/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot|json)$/i) || 
                        url.pathname.startsWith('/assets/') ||
                        url.pathname === '/manifest.json';

  // For PHP pages, always fetch from network (never cache)
  if (isPhpPage) {
    event.respondWith(fetch(event.request));
    return;
  }

  // For static assets, try cache first, then network
  if (isStaticAsset) {
    event.respondWith(
      caches.match(event.request)
        .then((cachedResponse) => {
          if (cachedResponse) {
            return cachedResponse;
          }

          return fetch(event.request)
            .then((response) => {
              // Don't cache non-successful responses
              if (!response || response.status !== 200 || response.type !== 'basic') {
                return response;
              }

              // Clone the response
              const responseToCache = response.clone();
              caches.open(RUNTIME_CACHE).then((cache) => {
                cache.put(event.request, responseToCache);
              });

              return response;
            });
        })
    );
    return;
  }

  // For everything else, fetch from network (don't cache)
  event.respondWith(fetch(event.request));
});

