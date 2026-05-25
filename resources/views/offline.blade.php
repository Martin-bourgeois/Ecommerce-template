<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hors ligne</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .offline-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px 40px;
            text-align: center;
            max-width: 500px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .offline-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .offline-message {
            font-size: 16px;
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .offline-suggestions {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: left;
        }

        .offline-suggestions h3 {
            font-size: 14px;
            color: #1f2937;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .offline-suggestions ul {
            list-style: none;
            font-size: 14px;
            color: #6b7280;
        }

        .offline-suggestions li {
            padding: 8px 0;
            display: flex;
            align-items: center;
        }

        .offline-suggestions li:before {
            content: '✓';
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-size: 12px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .offline-actions {
            display: flex;
            gap: 12px;
            flex-direction: column;
        }

        button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #1f2937;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .offline-status {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }

        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1>Vous êtes hors ligne</h1>
        <p class="offline-message">
            Vous n'êtes pas connecté à Internet. Certaines fonctionnalités sont limitées, mais vous pouvez continuer à consulter les pages mises en cache.
        </p>

        <div class="offline-suggestions">
            <h3>Ce que vous pouvez faire :</h3>
            <ul>
                <li>Consulter les produits précédemment consultés</li>
                <li>Continuer vos achats (synchronisés au retour)</li>
                <li>Lire votre historique de commandes</li>
            </ul>
        </div>

        <div class="offline-actions">
            <button class="btn-primary" onclick="tryRefresh()">Réessayer la connexion</button>
            <button class="btn-secondary" onclick="goHome()">Retourner à l'accueil</button>
        </div>

        <div class="offline-status">
            <span class="status-indicator"></span>
            Statut: Hors ligne
        </div>
    </div>

    <script>
        // Vérifier la connexion Internet
        function checkConnection() {
            fetch('/ping', { method: 'HEAD', cache: 'no-store' })
                .then(() => {
                    console.log('[Offline] Connexion rétablie');
                    location.reload();
                })
                .catch(() => {
                    console.log('[Offline] Toujours hors ligne');
                });
        }

        function tryRefresh() {
            checkConnection();
        }

        function goHome() {
            window.location.href = '/';
        }

        // Vérifier la connexion toutes les 3 secondes
        setInterval(checkConnection, 3000);

        // Écouter les événements online/offline
        window.addEventListener('online', () => {
            console.log('[Offline] Événement: en ligne');
            location.reload();
        });

        window.addEventListener('offline', () => {
            console.log('[Offline] Événement: hors ligne');
        });
    </script>
</body>
</html>
