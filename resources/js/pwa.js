import { enqueue, drainQueue } from './idb-helpers.js';
import './sw-register';

const enqueueButton = document.getElementById('enqueue');

enqueueButton?.addEventListener('click', async () => {
    await enqueue({ action: 'submit_candidate', payload: { name: 'Test' } });
    window.alert('Enqueued');
});

window.processOfflineQueue = async () => {
    await drainQueue();
};
