// Minimal IndexedDB helper for queueing offline actions
const DB_NAME = 'spk_bansos_offline';
const STORE = 'queue';

function openDb() {
  return new Promise((resolve, reject) => {
    const req = indexedDB.open(DB_NAME, 1);
    req.onupgradeneeded = () => {
      const db = req.result;
      if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE, {keyPath: 'id', autoIncrement: true});
    };
    req.onsuccess = () => resolve(req.result);
    req.onerror = () => reject(req.error);
  });
}

export async function enqueue(action) {
  const db = await openDb();
  return new Promise((res, rej) => {
    const tx = db.transaction(STORE, 'readwrite');
    tx.objectStore(STORE).add({...action, created_at: Date.now()});
    tx.oncomplete = () => res(true);
    tx.onerror = () => rej(tx.error);
  });
}

export async function drainQueue() {
  const db = await openDb();
  return new Promise((res, rej) => {
    const tx = db.transaction(STORE, 'readwrite');
    const store = tx.objectStore(STORE);
    const req = store.getAll();
    req.onsuccess = async () => {
      const items = req.result || [];
      for (const item of items) {
        try {
          await fetch('/api/v1/offline/sync', {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(item)});
          store.delete(item.id);
        } catch (e) {
          // leave in queue
        }
      }
      res(items.length);
    };
    req.onerror = () => rej(req.error);
  });
}
