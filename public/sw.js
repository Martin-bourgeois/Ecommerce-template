const CACHE_VERSION = 'v1';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const DYNAMIC_CACHE = `${CACHE_VERSION}-dynamic`;
const OFFLINE_PAGE = '/offline';

// Assets statiques à mettre en cache au premier chargement
const STATIC_ASSETS = [
  '/',
  '/offline',
  '/css/app.css',
  '/js/app.js',
  '/js/bootstrap.js',
  '/images/logo.png',
  '/images/icon-192x192.png',
  '/images/icon-512x512.png',
  '/manifest.json',
];

// Installer le service worker et mettre en cache les assets statiques
self.addEventListener('install', (event) => {
  console.log('[SW] Installation...');
  
  event.waitUntil(
    caches.open(STATIC_CACHE).then((cache) => {
      console.log('[SW] Mise en cache des assets statiques');
      return cache.addAll(STATIC_ASSETS).catch((error) => {
        console.warn('[SW] Erreur lors de la mise en cache:', error);
        // Continuer même si certains assets ne peuvent pas être cachés
        return cache.addAll(STATIC_ASSETS.filter(asset => asset !== '/offline'));
      });
    })
  );
  
  self.skipWaiting();
});

// Nettoyer les anciens caches lors de l'activation
self.addEventListener('activate', (event) => {
  console.log('[SW] Activation...');
  
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames
          .filter((name) => name !== STATIC_CACHE && name !== DYNAMIC_CACHE)
          .map((name) => {
            console.log('[SW] Suppression du cache:', name);
            return caches.delete(name);
          })
      );
    })
  );
  
  self.clients.claim();
});

// Stratégies de cache pour les requêtes
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);
  
  // Ignorer les requêtes non-GET
  if (event.request.method !== 'GET') {
    return;
  }
  
  // Ignorer les requêtes API POST/PUT/DELETE (handled par background sync)
  if (url.pathname.startsWith('/api/') && event.request.method !== 'GET') {
    return;
  }
  
  // Cache First: Assets statiques (CSS, JS, images)
  if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(event.request));
    return;
  }
  
  // Network First: Pages dynamiques (produits, catégories)
  if (isDynamicPage(url.pathname)) {
    event.respondWith(networkFirst(event.request));
    return;
  }
  
  // Cache First avec fallback pour les autres requêtes
  event.respondWith(
    caches.match(event.request)
      .then((response) => response || fetch(event.request))
      .catch(() => cacheFirst(event.request))
  );
});

// Cache First: Retourner depuis le cache, sinon faire une requête réseau
async function cacheFirst(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);
  
  if (cached) {
    return cached;
  }
  
  try {
    const response = await fetch(request);
    
    // Mettre en cache les réponses réussies
    if (response.ok) {
      cache.put(request, response.clone());
    }
    
    return response;
  } catch (error) {
    console.error('[SW] Erreur Fetch:', error);
    return new Response('Vous êtes hors ligne', {
      status: 503,
      statusText: 'Service Unavailable',
      headers: new Headers({
        'Content-Type': 'text/plain; charset=utf-8',
      }),
    });
  }
}

// Network First: Faire une requête réseau, utiliser le cache en cas d'échec
async function networkFirst(request) {
  const cache = await caches.open(DYNAMIC_CACHE);
  
  try {
    const response = await fetch(request);
    
    // Mettre en cache les réponses réussies
    if (response.ok) {
      cache.put(request, response.clone());
    }
    
    return response;
  } catch (error) {
    console.error('[SW] Erreur Network First:', error);
    
    // Utiliser la version en cache
    const cached = await cache.match(request);
    if (cached) {
      return cached;
    }
    
    // Retourner la page offline
    return cache.match(OFFLINE_PAGE) || 
           new Response('Page non disponible hors ligne', {
             status: 503,
             headers: new Headers({
               'Content-Type': 'text/plain; charset=utf-8',
             }),
           });
  }
}

// Vérifier si une URL est un asset statique
function isStaticAsset(pathname) {
  const staticExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.svg', '.woff', '.woff2', '.ttf', '.eot'];
  return staticExtensions.some((ext) => pathname.endsWith(ext));
}

// Vérifier si une URL est une page dynamique
function isDynamicPage(pathname) {
  return pathname === '/' || 
         pathname.startsWith('/products') || 
         pathname.startsWith('/categories') ||
         pathname.startsWith('/cart') ||
         pathname.startsWith('/orders') ||
         pathname.startsWith('/account');
}

// Background Sync pour le panier
self.addEventListener('sync', (event) => {
  console.log('[SW] Background Sync:', event.tag);
  
  if (event.tag === 'sync-cart') {
    event.waitUntil(syncCart());
  } else if (event.tag === 'sync-notifications') {
    event.waitUntil(syncNotifications());
  }
});

// Synchroniser le panier hors ligne
async function syncCart() {
  try {
    // Récupérer les items en attente du localStorage
    const pendingItems = JSON.parse(localStorage.getItem('pending_cart_items') || '[]');
    
    if (pendingItems.length === 0) {
      return;
    }
    
    // Envoyer chaque item à l'API
    for (const item of pendingItems) {
      const response = await fetch('/api/cart/add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': getCsrfToken(),
        },
        body: JSON.stringify(item),
      });
      
      if (!response.ok) {
        throw new Error(`Erreur sync cart: ${response.status}`);
      }
    }
    
    // Vider la file d'attente
    localStorage.removeItem('pending_cart_items');
    
    // Notifier le client que la sync est complétée
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
      client.postMessage({
        type: 'CART_SYNCED',
        message: 'Votre panier a été synchronisé',
      });
    });
  } catch (error) {
    console.error('[SW] Erreur sync cart:', error);
    throw error; // Relancer pour retry
  }
}

// Synchroniser les notifications
async function syncNotifications() {
  try {
    const response = await fetch('/api/notifications/pending');
    const data = await response.json();
    
    if (data.notifications && data.notifications.length > 0) {
      data.notifications.forEach((notification) => {
        showNotification(notification.title, notification.options);
      });
    }
  } catch (error) {
    console.error('[SW] Erreur sync notifications:', error);
  }
}

// Push Notifications
self.addEventListener('push', (event) => {
  console.log('[SW] Push reçu');
  
  if (!event.data) {
    console.log('[SW] Pas de données dans le push');
    return;
  }
  
  let notification;
  
  try {
    notification = event.data.json();
  } catch (error) {
    notification = {
      title: 'Notification',
      options: {
        body: event.data.text(),
      },
    };
  }
  
  event.waitUntil(
    showNotification(notification.title, notification.options)
  );
});

// Afficher une notification
async function showNotification(title, options = {}) {
  const tag = options.tag || 'default';
  
  // Vérifier si une notification avec le même tag existe
  const existingNotifications = await self.registration.getNotifications({
    tag,
  });
  
  if (existingNotifications.length > 0) {
    return;
  }
  
  return self.registration.showNotification(title, {
    icon: '/images/icon-192x192.png',
    badge: '/images/badge-72x72.png',
    ...options,
  });
}

// Gérer les clics sur les notifications
self.addEventListener('notificationclick', (event) => {
  console.log('[SW] Notification cliquée');
  
  event.notification.close();
  
  const urlToOpen = event.notification.data?.url || '/';
  
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then((clientList) => {
      // Vérifier si une fenêtre avec l'URL existe déjà
      for (const client of clientList) {
        if (client.url === urlToOpen && 'focus' in client) {
          return client.focus();
        }
      }
      
      // Sinon, ouvrir une nouvelle fenêtre
      if (clients.openWindow) {
        return clients.openWindow(urlToOpen);
      }
    })
  );
});

// Fermer une notification
self.addEventListener('notificationclose', (event) => {
  console.log('[SW] Notification fermée');
});

// Helper: Récupérer le token CSRF
function getCsrfToken() {
  const name = 'XSRF-TOKEN=';
  const decodedCookie = decodeURIComponent(document.cookie);
  const cookieArray = decodedCookie.split(';');
  
  for (let cookie of cookieArray) {
    cookie = cookie.trim();
    if (cookie.indexOf(name) === 0) {
      return cookie.substring(name.length);
    }
  }
  
  return '';
}
