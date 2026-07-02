@props([
    'variant' => 'info' // success, warning, danger, info
])

@php
    $class = match($variant) {
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        default => 'badge-info'
    };
@endphp

<span class="badge {{ $class }} font-body">
    {{ $slot }}
</span>
