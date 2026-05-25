if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').then(reg => {
      console.log('SW registered', reg);
    }).catch(err => console.error('SW register failed', err));
  });

  navigator.serviceWorker.addEventListener('message', (ev) => {
    if (ev.data && ev.data.type === 'SYNC_REQUEST') {
      // Trigger local sync processing
      if (window.processOfflineQueue) window.processOfflineQueue();
    }
  });
}
