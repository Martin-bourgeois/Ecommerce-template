@component('mail::message')
# Nouvelle réponse au ticket #{{ $ticket->id }}

Bonjour {{ $recipient->name }},

Une nouvelle réponse a été ajoutée au ticket **{{ $ticket->subject }}**:

@component('mail::panel')
**{{ $author->name }}** a répondu:

{{ $message->content }}
@endcomponent

@component('mail::button', ['url' => route('support.ticket.show', $ticket)])
Voir le ticket complet
@endcomponent

**Détails du ticket:**
- **Sujet:** {{ $ticket->subject }}
- **Statut:** {{ $ticket->status->label() }}
- **Priorité:** {{ $ticket->priority->label() }}
- **Catégorie:** {{ $ticket->category->label() }}

Merci,
{{ config('app.name') }}
@endcomponent
