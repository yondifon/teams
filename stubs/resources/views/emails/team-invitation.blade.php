<x-mail::message>
@if($invitation->expires_at && $invitation->expires_at->isPast())
# Invitation Expired

Your invitation to join **{{ $invitation->team->name }}** has expired.

@if($invitedByName)
Contact {{ $invitedByName }} for a new invitation.
@else
Contact the team administrator for a new invitation.
@endif
@else
# Join {{ $invitation->team->name }}

@if($invitedByName)
{{ $invitedByName }} invited you to join their team and start collaborating.
@else
You've been invited to collaborate with this team.
@endif

<x-mail::button :url="$acceptUrl">
Join Team
</x-mail::button>

@if($invitation->expires_at)
**Expires {{ $invitation->expires_at->format('M j, Y') }}**
@endif
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
