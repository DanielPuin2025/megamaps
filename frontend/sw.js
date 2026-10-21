const CACHE = "megamaps-v17-assets";
const ARCHIVOS = [
  "./",
  "./index.html",
  "./manifest.json",
  "./assets/Salones/escudo.png",
  "./assets/Salones/logo_megamaps.png",
  "./assets/Salones/plano_piso1.jpg",
  "./assets/Salones/plano_piso2.jpg",
  "./assets/Salones/icon-192.png",
  "./assets/Salones/icon-512.png",
  "./assets/integrantes/Bladimir.jpeg",
  "./assets/integrantes/cesar.jpeg",
  "./assets/integrantes/Luifer David (Lider).jpeg",
];

self.addEventListener("install", (e) => {
  e.waitUntil(caches.open(CACHE).then((c) => c.addAll(ARCHIVOS)));
  self.skipWaiting();
});

self.addEventListener("activate", (e) => {
  e.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// Estrategia: cache primero, y si no está, va a la red y lo guarda para la próxima.
self.addEventListener("fetch", (e) => {
  const pathname = new URL(e.request.url).pathname;
  if (e.request.method !== "GET" || pathname.endsWith("/api.php") || pathname.endsWith("/guardar_foto.php")) return;
  // HTML/JS/CSS: red primero para que las actualizaciones se vean al instante.
  if (e.request.mode === "navigate" || /\.(html|js|css)$/.test(pathname)) {
    e.respondWith(
      fetch(e.request).then((res) => {
        const copia = res.clone();
        caches.open(CACHE).then((c) => c.put(e.request, copia));
        return res;
      }).catch(() => caches.match(e.request))
    );
    return;
  }
  e.respondWith(
    caches.match(e.request).then(
      (cached) =>
        cached ||
        fetch(e.request).then((res) => {
          const copia = res.clone();
          caches.open(CACHE).then((c) => c.put(e.request, copia));
          return res;
        }).catch(() => cached)
    )
  );
});
