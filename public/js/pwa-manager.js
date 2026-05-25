/**
 * PWA Service Worker Registration & Management
 */

class PWAManager {
    constructor() {
        this.swPath = '/sw.js';
        this.registration = null;
        this.isSupported = 'serviceWorker' in navigator;
        this.notificationPermission = Notification.permission;
    }

    /**
     * Initialiser PWA
     */
    async init() {
        if (!this.isSupported) {
            console.log('[PWA] Service Workers non supportés');
            return false;
        }

        try {
            console.log('[PWA] Enregistrement du Service Worker');
            this.registration = await navigator.serviceWorker.register(this.swPath, {
                scope: '/',
            });

            console.log('[PWA] Service Worker enregistré:', this.registration);

            // Écouter les mises à jour
            this.registration.addEventListener('updatefound', () => {
                console.log('[PWA] Mise à jour trouvée');
                this.onUpdateFound();
            });

            // Vérifier les mises à jour tous les jours
            setInterval(() => {
                this.registration.update();
            }, 24 * 60 * 60 * 1000);

            // Initialiser les push notifications
            this.initPushNotifications();

            // Initialiser la sync
            this.initBackgroundSync();

            return true;
        } catch (error) {
            console.error('[PWA] Erreur enregistrement SW:', error);
            return false;
        }
    }

    /**
     * Gérer les mises à jour du SW
     */
    onUpdateFound() {
        const newWorker = this.registration.installing;

        newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                // Une nouvelle version est disponible
                console.log('[PWA] Nouvelle version disponible');
                this.notifyUpdate();
            }
        });
    }

    /**
     * Notifier l'utilisateur d'une mise à jour
     */
    notifyUpdate() {
        // Afficher une notification ou une bannière
        const event = new CustomEvent('pwa:update-available', {
            detail: {
                registration: this.registration,
            },
        });
        window.dispatchEvent(event);
    }

    /**
     * Initialiser les push notifications
     */
    async initPushNotifications() {
        if (!('Notification' in window)) {
            console.log('[PWA] Notifications non supportées');
            return;
        }

        // Demander la permission lors de la première action utilisateur
        document.addEventListener('click', () => {
            this.requestNotificationPermission();
        }, { once: true });

        // S'abonner si permission déjà accordée
        if (Notification.permission === 'granted') {
            await this.subscribeToPushNotifications();
        }
    }

    /**
     * Demander la permission pour les notifications
     */
    async requestNotificationPermission() {
        if (Notification.permission === 'granted') {
            return;
        }

        if (Notification.permission === 'denied') {
            return;
        }

        try {
            const permission = await Notification.requestPermission();

            if (permission === 'granted') {
                console.log('[PWA] Permission de notification accordée');
                await this.subscribeToPushNotifications();
            }
        } catch (error) {
            console.error('[PWA] Erreur permission notification:', error);
        }
    }

    /**
     * S'abonner aux notifications push
     */
    async subscribeToPushNotifications() {
        if (!this.registration) {
            console.log('[PWA] Service Worker non enregistré');
            return;
        }

        try {
            let subscription = await this.registration.pushManager.getSubscription();

            if (!subscription) {
                // Créer une nouvelle souscription
                const vapidPublicKey = document.querySelector('meta[name="vapid-public-key"]')?.content;

                if (!vapidPublicKey) {
                    console.log('[PWA] Clé VAPID non trouvée');
                    return;
                }

                subscription = await this.registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey),
                });

                console.log('[PWA] Souscription créée:', subscription);
            }

            // Envoyer la souscription au serveur
            await this.sendSubscriptionToServer(subscription);

            console.log('[PWA] Notifications push activées');
        } catch (error) {
            console.error('[PWA] Erreur souscription push:', error);
        }
    }

    /**
     * Envoyer la souscription au serveur
     */
    async sendSubscriptionToServer(subscription) {
        try {
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
                },
                body: JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: this.arrayBufferToBase64(subscription.getKey('p256dh')),
                        auth: this.arrayBufferToBase64(subscription.getKey('auth')),
                    },
                }),
            });

            if (!response.ok) {
                throw new Error(`Erreur: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('[PWA] Souscription envoyée:', data);
        } catch (error) {
            console.error('[PWA] Erreur envoi souscription:', error);
        }
    }

    /**
     * Initialiser la background sync
     */
    initBackgroundSync() {
        if (!('SyncManager' in window)) {
            console.log('[PWA] Background Sync non supportée');
            return;
        }

        // Enregistrer les tags de sync
        this.registerSyncTag('sync-cart');
        this.registerSyncTag('sync-notifications');
    }

    /**
     * Enregistrer un tag de sync
     */
    async registerSyncTag(tag) {
        try {
            await this.registration.sync.register(tag);
            console.log(`[PWA] Tag sync enregistré: ${tag}`);
        } catch (error) {
            console.error(`[PWA] Erreur enregistrement sync ${tag}:`, error);
        }
    }

    /**
     * Déclencher la sync manuellement
     */
    async triggerSync(tag) {
        try {
            await this.registration.sync.register(tag);
            console.log(`[PWA] Sync déclenché: ${tag}`);
        } catch (error) {
            console.error(`[PWA] Erreur sync ${tag}:`, error);
        }
    }

    /**
     * Ajouter un item au panier hors ligne
     */
    addToCartOffline(item) {
        if (!navigator.onLine) {
            console.log('[PWA] Ajout au panier hors ligne');

            // Récupérer la liste actuelle
            let pendingItems = JSON.parse(localStorage.getItem('pending_cart_items') || '[]');

            // Ajouter le nouvel item
            pendingItems.push({
                ...item,
                addedAt: new Date().toISOString(),
            });

            // Sauvegarder
            localStorage.setItem('pending_cart_items', JSON.stringify(pendingItems));

            // Déclencher la sync
            this.triggerSync('sync-cart').catch((error) => {
                console.log('[PWA] Sync non disponible, sera utilisée à la reconnexion');
            });

            return true;
        }

        return false;
    }

    /**
     * Vérifier si l'app est en mode standalone
     */
    isStandalone() {
        return window.navigator.standalone === true ||
               window.matchMedia('(display-mode: standalone)').matches ||
               document.referrer.includes('android-app://');
    }

    /**
     * Convertir VAPID public key
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    /**
     * Convertir ArrayBuffer en Base64
     */
    arrayBufferToBase64(buffer) {
        const bytes = new Uint8Array(buffer);
        let binary = '';

        for (let i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }

        return window.btoa(binary);
    }

    /**
     * Mettre à jour le badge du panier
     */
    updateCartBadge(count) {
        if ('setAppBadge' in navigator) {
            if (count > 0) {
                navigator.setAppBadge(count);
            } else {
                navigator.clearAppBadge();
            }
        }
    }

    /**
     * Obtenir les informations du cache
     */
    async getCacheInfo() {
        if (!('caches' in window)) {
            return null;
        }

        const cacheNames = await caches.keys();
        const cacheInfo = {};

        for (const name of cacheNames) {
            const cache = await caches.open(name);
            const keys = await cache.keys();
            cacheInfo[name] = {
                entries: keys.length,
                size: keys.reduce((sum, request) => sum + (request.size || 0), 0),
            };
        }

        return cacheInfo;
    }

    /**
     * Nettoyer le cache
     */
    async clearCache(cacheName) {
        try {
            if (cacheName) {
                await caches.delete(cacheName);
            } else {
                const cacheNames = await caches.keys();
                await Promise.all(cacheNames.map(name => caches.delete(name)));
            }

            console.log('[PWA] Cache nettoyé');
        } catch (error) {
            console.error('[PWA] Erreur nettoyage cache:', error);
        }
    }
}

// Initialiser PWA au chargement
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.pwaManager = new PWAManager();
        window.pwaManager.init();
    });
} else {
    window.pwaManager = new PWAManager();
    window.pwaManager.init();
}

// Exporter pour utilisation externe
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PWAManager;
}
