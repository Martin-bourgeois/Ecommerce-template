<x-mail::message>
# Retour approuvé

Bonjour {{ $rma->user->name }},

Votre demande de retour **{{ $rma->rma_number }}** a été approuvée par notre équipe.

## Prochaines étapes

Veuillez préparer votre colis et l'envoyer à l'adresse suivante:

**Adresse de retour:**
[Adresse de retour à configurer]

**Numéro de suivi de retour:** {{ $rma->rma_number }}

## Détails de votre retour

**Raison:** {{ $rma->reason->label() }}
**Nombre d'articles:** {{ $rma->items->count() }}
**Montant estimé:** {{ number_format($rma->getTotalRefundAmount(), 2) }}€

<x-mail::button url="{{ route('account.rma.show', $rma) }}">
Voir les détails
</x-mail::button>

Merci!<br>
{{ config('app.name') }}
</x-mail::message>
