// Reemplaza al service worker antiguo (que estaba en la raiz): borra su cache,
// se desinstala y recarga las pestañas para que carguen /frontend/.
self.addEventListener("install", () => self.skipWaiting());
self.addEventListener("activate", (e) => {
  e.waitUntil(
    (async () => {
      for (const k of await caches.keys()) await caches.delete(k);
      await self.registration.unregister();
      for (const c of await self.clients.matchAll({ type: "window" })) c.navigate(c.url);
    })()
  );
});
