const CACHE_NAME = 'spk-bansos-v1';
const ASSETS = [
  '/',
  '/pwa',
  '/manifest.json'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(ASSETS))
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (event) => {
  event.respondWith(
    caches.match(event.request).then((resp) => resp || fetch(event.request))
  );
});

self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-offline-queue') {
    event.waitUntil(syncQueue());
  }
});

async function syncQueue() {
  // Attempt to read queued items from IndexedDB via client message
  const allClients = await self.clients.matchAll({includeUncontrolled: true});
  for (const client of allClients) {
    client.postMessage({type: 'SYNC_REQUEST'});
  }
}
