@php
    $user = filament()->auth()->user();
    $role = method_exists($user, 'getRoleNames')
        ? ($user->getRoleNames()->first() ?: 'Administrator')
        : 'Administrator';
@endphp

<span class="dgc-topbar-user-copy">
    <strong>{{ $user->name }}</strong>
    <small>{{ $role }}</small>
</span>
