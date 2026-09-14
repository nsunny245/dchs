<x-filament-panels::page>
    <style>
        .fi-main:has(.bulk-admission-workbench) { max-width: none !important; }
        .bulk-admission-workbench {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        /* Executive Hero Banner */
        .bulk-hero {
            position: relative;
            background: linear-gradient(135deg, #091a2f 0%, #0f2d4e 65%, #183e6d 100%);
            border-radius: 1.25rem;
            padding: 1.75rem 2.25rem;
            color: #ffffff;
            box-shadow: 0 12px 32px -4px rgba(9, 26, 47, 0.22);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.75rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .bulk-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(201, 150, 60, 0.22) 0%, rgba(201, 150, 60, 0) 70%);
            pointer-events: none;
        }
        .bulk-hero__content {
            position: relative;
            z-index: 1;
            max-width: 52rem;
        }
        .bulk-hero__title {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 800;
            letter-spacing: -0.025em;
            color: #ffffff;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .bulk-hero__subtitle {
            margin: 0.45rem 0 0;
            font-size: 0.9rem;
            line-height: 1.55;
            color: #cbd5e1;
        }
        .bulk-hero__badge {
            position: relative;
            z-index: 1;
            flex-shrink: 0;
            background: rgba(201, 150, 60, 0.16);
            border: 1px solid rgba(201, 150, 60, 0.45);
            color: #f6e0b5;
            padding: 0.55rem 1.15rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        /* Control Toolbar */
        .bulk-setup-bar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #c9963c;
            border-radius: 1rem;
            padding: 1.15rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
        }
        .bulk-setup-bar__title {
            font-size: 0.975rem;
            font-weight: 700;
            color: #0f172a;
        }
        .bulk-setup-bar__desc {
            font-size: 0.825rem;
            color: #64748b;
            margin-top: 0.15rem;
        }

        /* Form Customizations */
        .bulk-defaults {
            background: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.03) !important;
            padding: 1.5rem !important;
        }
        .bulk-defaults .fi-section-header-title {
            color: #0f172a !important;
            font-weight: 800 !important;
            font-size: 1.1rem !important;
        }

        /* Repeater Cards */
        .bulk-student-repeater .fi-fo-repeater-item {
            background: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            border-top: 4px solid #0f2d4e !important;
            border-radius: 1.25rem !important;
            box-shadow: 0 8px 24px rgba(15, 45, 78, 0.06) !important;
            margin-bottom: 1.5rem !important;
            transition: all 0.2s ease;
        }
        .bulk-student-repeater .fi-fo-repeater-item:hover {
            box-shadow: 0 12px 32px rgba(15, 45, 78, 0.1) !important;
        }

        /* Import Results Panel */
        .bulk-results {
            overflow: hidden;
            border: 1px solid #e2e8f0;
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.04);
        }
        .bulk-results__title {
            padding: 1rem 1.25rem;
            color: #ffffff;
            background: #0f2d4e;
            font-weight: 800;
            font-size: 0.95rem;
        }
        .bulk-result {
            display: grid;
            grid-template-columns: 4rem minmax(12rem, 1fr) 9rem minmax(12rem, 1fr);
            gap: 0.75rem;
            align-items: center;
            padding: 0.85rem 1.25rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.85rem;
        }
        .bulk-result--failed { background: #fef2f2; color: #991b1b; }
        .bulk-result--imported { background: #f0fdf4; color: #166534; }

        /* Sticky Action Bar */
        .bulk-bottom-bar {
            position: sticky;
            bottom: 1.25rem;
            z-index: 30;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            border: 1px solid #cbd5e1;
            border-radius: 1.25rem;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 12px 36px rgba(15, 23, 42, 0.14);
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .bulk-hero { flex-direction: column; align-items: flex-start; padding: 1.25rem; }
            .bulk-setup-bar { flex-direction: column; align-items: flex-start; }
            .bulk-result { grid-template-columns: 2.5rem 1fr; }
            .bulk-bottom-bar { flex-direction: column; gap: 0.85rem; align-items: stretch; }
            .bulk-bottom-bar__actions { flex-direction: column; width: 100%; }
        }
    </style>

    <div class="bulk-admission-workbench">
        <section class="bulk-hero">
            <div class="bulk-hero__content">
                <h2 class="bulk-hero__title">
                    <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Manual Bulk Admission Workbench
                </h2>
                <p class="bulk-hero__subtitle">Create enrolled student records and opening fee balances cleanly in one batch. Complete documents and detailed academics anytime later from the student profile.</p>
            </div>
            <div class="bulk-hero__badge">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                No vouchers generated yet
            </div>
        </section>

        @if ($lastResults)
            <section class="bulk-results">
                <div class="bulk-results__title">Latest Import Results — Batch #{{ $lastBatchId }}</div>
                @foreach ($lastResults as $result)
                    <div class="bulk-result bulk-result--{{ $result['status'] }}">
                        <strong>#{{ $result['row'] }}</strong>
                        <strong>{{ $result['name'] }}</strong>
                        <span class="font-bold">{{ $result['status'] === 'imported' ? '✓ Imported' : '⚠ Needs Correction' }}</span>
                        <span>{{ $result['enrollment'] ?: implode(' ', $result['errors']) }}</span>
                    </div>
                @endforeach
            </section>
        @endif

        <form wire:submit="import">
            <div class="bulk-setup-bar mb-6">
                <div>
                    <div class="bulk-setup-bar__title">Apply common setup before entering financial amounts</div>
                    <div class="bulk-setup-bar__desc">Copies campus, session, program, qualification, dates, and payment method into all student cards.</div>
                </div>
                <x-filament::button
                    type="button"
                    wire:click="applyDefaults"
                    color="primary"
                    icon="heroicon-o-arrow-down-on-square-stack"
                    class="font-bold shadow-sm flex-shrink-0"
                >
                    Apply defaults to all rows
                </x-filament::button>
            </div>

            {{ $this->form }}

            <div class="bulk-bottom-bar">
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Double check student entries before submitting bulk import.</span>
                </div>
                <div class="flex items-center gap-3 bulk-bottom-bar__actions">
                    <x-filament::button
                        tag="a"
                        :href="\App\Filament\Resources\AdmissionResource::getUrl('index')"
                        color="gray"
                        outlined
                        class="font-semibold"
                    >
                        Back to admissions
                    </x-filament::button>
                    <x-filament::button
                        type="submit"
                        color="primary"
                        icon="heroicon-o-user-group"
                        wire:loading.attr="disabled"
                        class="font-bold shadow-md"
                    >
                        <span wire:loading.remove wire:target="import">Import students & opening balances</span>
                        <span wire:loading wire:target="import">Importing safely…</span>
                    </x-filament::button>
                </div>
            </div>
        </form>
    </div>
</x-filament-panels::page>
