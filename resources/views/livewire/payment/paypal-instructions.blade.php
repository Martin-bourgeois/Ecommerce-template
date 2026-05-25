<div class="bg-white rounded-lg shadow-md p-8 max-w-md mx-auto">
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">Paiement par PayPal</h2>
        <p class="text-gray-600">Mode virement manuel</p>
    </div>

    <div class="space-y-6">
        <!-- PayPal Email -->
        <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
            <p class="text-sm font-semibold text-gray-700 mb-2">Adresse PayPal:</p>
            <p class="text-lg font-mono text-blue-600 break-all">{{ $paypalEmail }}</p>
            <button
                class="mt-2 w-full px-3 py-2 text-sm bg-blue-500 hover:bg-blue-600 text-white rounded transition"
                onclick="navigator.clipboard.writeText('{{ $paypalEmail }}')"
            >
                Copier l'adresse
            </button>
        </div>

        <!-- Amount -->
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm font-semibold text-gray-700 mb-2">Montant à payer:</p>
            <p class="text-3xl font-bold text-gray-900">{{ $amount }}</p>
        </div>

        <!-- Reference -->
        <div class="bg-gray-50 rounded-lg p-4">
            <p class="text-sm font-semibold text-gray-700 mb-2">Référence de commande:</p>
            <p class="text-sm font-mono text-gray-600 break-all">{{ $reference }}</p>
            <button
                class="mt-2 w-full px-3 py-2 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded transition"
                onclick="navigator.clipboard.writeText('{{ $reference }}')"
            >
                Copier la référence
            </button>
        </div>

        <!-- Countdown Timer -->
        @if ($showCountdown)
            <div class="bg-yellow-50 rounded-lg p-4 border-l-4 border-yellow-400">
                <p class="text-sm font-semibold text-gray-700 mb-2">Délai de paiement:</p>
                <p class="text-lg font-bold text-yellow-700">{{ $hoursRemaining }}h restante(s)</p>
                <p class="text-xs text-gray-600 mt-2">Votre commande sera annulée après 48 heures sans paiement.</p>
            </div>
        @else
            <div class="bg-red-50 rounded-lg p-4 border-l-4 border-red-400">
                <p class="text-sm font-semibold text-red-700">Délai de paiement dépassé</p>
                <p class="text-xs text-gray-600 mt-1">Veuillez contacter le support.</p>
            </div>
        @endif

        <!-- Instructions -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h3 class="font-semibold text-gray-900 mb-3">Instructions de paiement:</h3>
            <ol class="text-sm text-gray-700 space-y-2 list-decimal list-inside">
                <li>Ouvrez votre compte PayPal</li>
                <li>Cliquez sur "Envoyer de l'argent"</li>
                <li>Entrez l'adresse e-mail ci-dessus</li>
                <li>Saisissez le montant exact: {{ $amount }}</li>
                <li>Utilisez la référence en description: {{ $reference }}</li>
                <li>Confirmez et envoyez</li>
            </ol>
        </div>

        <!-- Confirmation Message -->
        <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-400">
            <p class="text-sm text-gray-700">
                ✓ Votre paiement sera confirmé dans les 2 heures après sa réception.
            </p>
        </div>
    </div>
</div>
