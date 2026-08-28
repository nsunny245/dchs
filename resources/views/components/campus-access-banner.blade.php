@if ($campusAccess?->campus && $campusAccess?->superAdmin)
    <div class="mb-6 flex flex-col gap-3 rounded-xl border border-primary-300 bg-primary-50 px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-primary-700 dark:bg-primary-950/30">
        <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-navy-900 text-primary-300">
                <x-heroicon-o-building-office-2 class="h-5 w-5" />
            </span>
            <div class="min-w-0">
                <p class="text-sm font-extrabold text-navy-900 dark:text-white">
                    Viewing {{ $campusAccess->campus->name }}
                </p>
                <p class="truncate text-xs text-gray-600 dark:text-gray-300">
                    Super Admin: {{ $campusAccess->superAdmin->name }} · Campus account: {{ $campusAccess->campusUser?->name }}
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('campus-access.exit') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex min-h-10 w-full items-center justify-center gap-2 rounded-lg bg-navy-900 px-4 py-2 text-sm font-bold text-white transition hover:bg-navy-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 sm:w-auto">
                <x-heroicon-o-arrow-left class="h-4 w-4" />
                Return to Super Admin
            </button>
        </form>
    </div>
@endif
