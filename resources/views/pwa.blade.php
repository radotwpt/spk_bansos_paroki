<!doctype html>
<html>
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>SPK Bansos PWA</title>
    <link rel="manifest" href="/manifest.json">
  </head>
  <body>
    <h1>SPK Bansos - PWA Test</h1>
    <button id="enqueue">Enqueue offline action</button>
    <script type="module">
      import { enqueue, drainQueue } from '/resources/js/idb-helpers.js';
      import '/resources/js/sw-register.js';

      document.getElementById('enqueue').addEventListener('click', async () => {
        await enqueue({action: 'submit_candidate', payload: {name: 'Test'}});
        alert('Enqueued');
      });

      window.processOfflineQueue = async () => {
        await drainQueue();
      }
    </script>
  </body>
 </html>
