<!-- Example PWA Integration in Blade Layout -->
<!-- Add this to your main layout file (e.g., resources/views/layouts/app.blade.php) -->

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Plateforme e-commerce progressive">
    
    <!-- PWA Theme Colors -->
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Commerce">
    
    <!-- PWA Icons -->
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/icon-192x192.png') }}">
    
    <!-- Web Push VAPID Public Key -->
    @if(config('pwa.push.vapid_public_key'))
        <meta name="vapid-public-key" content="{{ config('pwa.push.vapid_public_key') }}">
    @endif
    
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <title>@yield('title', 'New Commerce')</title>
</head>
<body>
    <!-- PWA Install Prompt -->
    @livewire('pwa.install-prompt')
    
    <!-- Page Content -->
    @yield('content')
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    
    <!-- PWA Manager Script -->
    <script src="{{ asset('js/pwa-manager.js') }}" defer></script>
    
    <!-- Service Worker Registration -->
    <script>
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker registered successfully');
                        
                        // Listen for updates
                        registration.addEventListener('updatefound', () => {
                            console.log('[PWA] Update found');
                        });
                    })
                    .catch(error => {
                        console.error('[PWA] Service Worker registration failed:', error);
                    });
            });
        }
        
        // Listen for PWA update available
        window.addEventListener('pwa:update-available', (event) => {
            console.log('[PWA] New version available');
            // Show update notification to user
            // Example: show a banner or toast notification
        });
        
        // Listen for cart sync completion
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data.type === 'CART_SYNCED') {
                    console.log('[PWA] Cart synced:', event.data.message);
                    // Reload cart or show notification
                }
            });
        }
    </script>
    
    <!-- Add Cart Badge on App Icon (optional) -->
    <script>
        // Update app badge with cart count
        function updateCartBadge(count) {
            if (window.pwaManager) {
                window.pwaManager.updateCartBadge(count);
            }
        }
        
        // Example: Update badge when cart changes
        // document.addEventListener('cart:updated', (e) => {
        //     updateCartBadge(e.detail.count);
        // });
    </script>
    
    <!-- Handle Offline Mode -->
    <script>
        window.addEventListener('online', () => {
            console.log('[App] Back online');
            // Trigger cart sync
            if (window.pwaManager) {
                window.pwaManager.triggerSync('sync-cart');
            }
        });
        
        window.addEventListener('offline', () => {
            console.log('[App] Now offline');
            // Show notification to user
        });
    </script>
    
    <!-- Example: Add to Cart Offline Support -->
    <script>
        // Hook into add to cart event
        // Example for Alpine or other framework:
        // 
        // function addToCart(productId, quantity) {
        //     if (navigator.onLine) {
        //         // Normal add to cart
        //         addToCartAPI(productId, quantity);
        //     } else {
        //         // Add to offline queue
        //         window.pwaManager.addToCartOffline({
        //             product_id: productId,
        //             quantity: quantity
        //         });
        //         showNotification('Ajouté au panier (synced when online)');
        //     }
        // }
    </script>
</body>
</html>

<!--
=== INTEGRATION CHECKLIST ===

1. Routes Configuration
   ☐ Add PWA routes to routes/web.php
   ☐ Add Push API routes to routes/api.php

2. Database
   ☐ Run: php artisan migrate

3. Environment Setup
   ☐ Set PWA_CACHE_VERSION=v1 in .env
   ☐ Generate and set VAPID keys (if using Web Push)
   ☐ Set PUSH_PROVIDER (web-push or onesignal)

4. Assets
   ☐ Create required icon files in public/images/
   ☐ Create screenshots for manifest
   ☐ Ensure CSS/JS files are accessible at /css/app.css and /js/app.js

5. Blade Integration
   ☐ Add this template to main layout
   ☐ Include @livewire('pwa.install-prompt')
   ☐ Include PWA meta tags
   ☐ Include pwa-manager.js script

6. Testing
   ☐ Run: php artisan test tests/Feature/Pwa/PwaTest.php
   ☐ Test manifest: curl http://localhost/manifest.json
   ☐ Test SW: curl http://localhost/sw.js
   ☐ Test offline page: visit http://localhost/offline

7. Browser Testing
   ☐ Install app on Chrome (Desktop)
   ☐ Test offline functionality
   ☐ Enable push notifications
   ☐ Test push notification receive
   ☐ Test add to cart offline (sync on reconnect)

8. Optional: Push Notifications
   ☐ Send test notification: php artisan push:send-pending --order-id=1
   ☐ Monitor browser console for errors
   ☐ Verify notification appears

=== KEY ENDPOINTS ===

Web Routes:
- GET  /manifest.json        → App manifest
- GET  /offline              → Offline fallback page
- GET  /ping                 → Connection test

API Routes (auth required):
- POST /api/push/subscribe   → Subscribe to push notifications
- POST /api/push/unsubscribe → Unsubscribe from push
- GET  /api/push/subscriptions → List user subscriptions
- POST /api/push/test        → Send test notification
- GET  /api/push/pending     → Get pending notifications

=== EXAMPLE USAGE ===

// Send push notification on order confirm
$pushService = app(\App\Domains\Pwa\Services\PushNotificationService::class);
$pushService->sendOrderNotification($order, 'order_confirmed');

// Add to cart offline (frontend)
window.pwaManager.addToCartOffline({
    product_id: 1,
    quantity: 2,
    price: 29.99
});

// Update cart badge
window.pwaManager.updateCartBadge(5);

// Check cache info
const cacheInfo = await window.pwaManager.getCacheInfo();
console.log(cacheInfo);
-->
