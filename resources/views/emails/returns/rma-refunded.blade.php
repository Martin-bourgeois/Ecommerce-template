<x-mail::message>
# Remboursement traité

Bonjour {{ $rma->user->name }},

Votre demande de retour **{{ $rma->rma_number }}** a été inspectée et traitée.

## Remboursement

Votre remboursement de **{{ number_format($rma->refund_amount, 2) }}€** a été approuvé.

Les fonds seront crédités sur votre compte dans les 5-7 jours ouvrables.

## Détails

**Raison du retour:** {{ $rma->reason->label() }}
**Nombre d'articles:** {{ $rma->items->count() }}
**Montant remboursé:** {{ number_format($rma->refund_amount, 2) }}€

<x-mail::button url="{{ route('account.rma.show', $rma) }}">
Voir les détails
</x-mail::button>

Merci d'avoir choisi {{ config('app.name') }}!<br>
{{ config('app.name') }}
</x-mail::message>
