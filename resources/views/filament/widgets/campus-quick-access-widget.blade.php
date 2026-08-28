<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Campus Quick Access</x-slot>
        <x-slot name="description">Open a campus administration workspace without signing out of Super Admin.</x-slot>

        @if (session('campus_access_error'))
            <div class="mb-5 rounded-xl border border-danger-200 bg-danger-50 px-4 py-3 text-sm font-medium text-danger-700 dark:border-danger-900 dark:bg-danger-950/30 dark:text-danger-300" role="alert">
                {{ session('campus_access_error') }}
            </div>
        @endif

        @if (session('campus_access_success'))
            <div class="mb-5 rounded-xl border border-success-200 bg-success-50 px-4 py-3 text-sm font-medium text-success-700 dark:border-success-900 dark:bg-success-950/30 dark:text-success-300" role="status">
                {{ session('campus_access_success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            @forelse ($campuses as $campus)
                @php($principal = $campus->principals->first())

                @if ($principal)
                    <form
                        method="POST"
                        action="{{ route('admin.campus-access.enter', $campus) }}"
                        x-data="{ submitting: false }"
                        x-on:submit="submitting = true"
                        class="h-full"
                    >
                        @csrf
                        <button
                            type="submit"
                            x-bind:disabled="submitting"
                            class="group flex min-h-44 w-full flex-col rounded-2xl border border-gray-200 bg-white p-5 text-left shadow-sm transition duration-150 hover:-translate-y-0.5 hover:border-primary-400 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-70 dark:border-white/10 dark:bg-gray-900 dark:hover:border-primary-500"
                            aria-label="Open {{ $campus->name }} administration dashboard"
                        >
                            <span class="flex w-full items-start justify-between gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 ring-1 ring-primary-200 dark:bg-primary-500/10 dark:text-primary-300 dark:ring-primary-500/20">
                                    <x-heroicon-o-building-office-2 class="h-6 w-6" />
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-success-50 px-2.5 py-1 text-xs font-bold text-success-700 dark:bg-success-500/10 dark:text-success-300">
                                    <span class="h-1.5 w-1.5 rounded-full bg-success-500"></span>
                                    Available
                                </span>
                            </span>

                            <span class="mt-4 block text-base font-extrabold leading-tight text-navy-900 group-hover:text-primary-700 dark:text-white dark:group-hover:text-primary-300">
                                {{ $campus->name }}
                            </span>
                            <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">
                                {{ $campus->city }}
                            </span>

                            <span class="mt-auto flex w-full items-end justify-between gap-3 pt-5">
                                <span class="min-w-0">
                                    <span class="block text-xs font-semibold uppercase tracking-wide text-gray-400">Campus administrator</span>
                                    <span class="mt-0.5 block truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $principal->name }}</span>
                                </span>
                                <span class="shrink-0 text-primary-700 transition-transform group-hover:translate-x-1 dark:text-primary-300" aria-hidden="true">
                                    <x-heroicon-o-arrow-right class="h-5 w-5" />
                                </span>
                            </span>

                            <span x-show="submitting" x-cloak class="mt-3 text-xs font-bold text-primary-700 dark:text-primary-300">Opening dashboard…</span>
                        </button>
                    </form>
                @else
                    <div class="flex min-h-44 flex-col rounded-2xl border border-dashed border-warning-300 bg-warning-50/60 p-5 dark:border-warning-700 dark:bg-warning-950/20">
                        <span class="flex items-start justify-between gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning-100 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">
                                <x-heroicon-o-building-office-2 class="h-6 w-6" />
                            </span>
                            <span class="rounded-full bg-warning-100 px-2.5 py-1 text-xs font-bold text-warning-800 dark:bg-warning-500/10 dark:text-warning-300">Setup required</span>
                        </span>
                        <span class="mt-4 block text-base font-extrabold leading-tight text-navy-900 dark:text-white">{{ $campus->name }}</span>
                        <span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">{{ $campus->city }}</span>
                        <span class="mt-auto pt-5 text-sm font-medium text-warning-800 dark:text-warning-300">Assign an active Campus Principal to enable access.</span>
                    </div>
                @endif
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center dark:border-white/10">
                    <x-heroicon-o-building-office-2 class="mx-auto h-8 w-8 text-gray-400" />
                    <p class="mt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">No active campuses are available.</p>
                </div>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
