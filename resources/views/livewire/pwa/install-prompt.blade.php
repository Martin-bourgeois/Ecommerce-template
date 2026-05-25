<div 
    class="pwa-install-prompt" 
    x-data="pwaInstallPrompt()"
    @pwa:install.window="installApp()"
    x-show="showPrompt"
    x-transition
>
    <div class="fixed inset-0 bg-black/50 z-40" @click="dismissPrompt()"></div>
    
    <div class="fixed bottom-0 left-0 right-0 sm:bottom-8 sm:left-auto sm:right-8 sm:max-w-sm z-50">
        <div class="bg-white rounded-lg shadow-xl overflow-hidden">
            <div class="p-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <img 
                            src="{{ asset('images/icon-192x192.png') }}" 
                            alt="App icon" 
                            class="w-12 h-12 rounded-lg"
                        >
                        <div>
                            <h3 class="font-semibold text-gray-900">Installer l'app</h3>
                            <p class="text-sm text-gray-600">Accès rapide et offline</p>
                        </div>
                    </div>
                    <button 
                        @click="dismissPrompt()"
                        class="text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <p class="text-sm text-gray-600 mb-4">
                    Installez notre app pour un accès rapide et l'utilisation hors ligne.
                </p>

                <div class="flex gap-3">
                    <button 
                        @click="installApp()"
                        class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition"
                    >
                        Installer
                    </button>
                    <button 
                        @click="dismissPrompt()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-900 font-medium py-2 px-4 rounded-lg transition"
                    >
                        Plus tard
                    </button>
                </div>
            </div>

            <!-- Promo bar pour les navigateurs supportant PWA -->
            <div class="bg-blue-50 border-t border-blue-100 px-6 py-3">
                <p class="text-xs text-blue-900 flex items-center gap-2">
                    <span class="text-lg">📱</span>
                    Fonctionne hors ligne • Pas de mise à jour manuelle
                </p>
            </div>
        </div>
    </div>
</div>

<script>
function pwaInstallPrompt() {
    return {
        showPrompt: false,
        deferredPrompt: null,

        async init() {
            // Écouter l'événement beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                this.deferredPrompt = event;

                // Vérifier si l'app a déjà été rejetée récemment
                const dismissed = localStorage.getItem('pwa_install_dismissed');
                if (dismissed && new Date(dismissed) > new Date()) {
                    return;
                }

                // Afficher la bannière après 3 secondes
                setTimeout(() => {
                    this.showPrompt = true;
                }, 3000);
            });

            // Vérifier si l'app est déjà installée
            if (window.navigator.standalone === true) {
                console.log('[PWA] App déjà installée');
                this.showPrompt = false;
            }

            // Écouter l'événement appinstalled
            window.addEventListener('appinstalled', () => {
                console.log('[PWA] App installée');
                this.showPrompt = false;
                this.deferredPrompt = null;
            });
        },

        async installApp() {
            if (!this.deferredPrompt) {
                console.log('[PWA] Prompt d\'installation non disponible');
                return;
            }

            try {
                this.deferredPrompt.prompt();
                const { outcome } = await this.deferredPrompt.userChoice;

                if (outcome === 'accepted') {
                    console.log('[PWA] Installation acceptée');
                } else {
                    console.log('[PWA] Installation rejetée');
                }

                this.deferredPrompt = null;
                this.showPrompt = false;
            } catch (error) {
                console.error('[PWA] Erreur installation:', error);
            }
        },

        dismissPrompt() {
            this.showPrompt = false;
            // Rejeter pour 30 jours
            const dismissUntil = new Date();
            dismissUntil.setDate(dismissUntil.getDate() + 30);
            localStorage.setItem('pwa_install_dismissed', dismissUntil.toISOString());

            @this.dismissPrompt();
        },
    };
}
</script>

@assets
<script>
    // Initialiser au chargement de la page
    document.addEventListener('DOMContentLoaded', () => {
        const promptElement = document.querySelector('.pwa-install-prompt');
        if (promptElement && promptElement.__x) {
            promptElement.__x.init();
        }
    });
</script>
@endassets
