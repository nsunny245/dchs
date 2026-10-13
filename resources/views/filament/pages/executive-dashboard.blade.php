<x-filament-panels::page>
    @once
        <style>{!! file_get_contents(resource_path('css/filament/executive-dashboard-inline.css')) !!}</style>
    @endonce

    @php
        $cashMax = max(1, collect($cashSeries)->max(fn ($point) => max($point['in'], $point['out'])));
        $admissionMax = max(1, collect($admissionSeries)->max('value'));
        $chartWidth = 900;
        $chartHeight = 220;
        $chartPad = 24;
        $cashCount = max(1, count($cashSeries) - 1);
        $admissionCount = max(1, count($admissionSeries) - 1);
        $cashInPoints = collect($cashSeries)->map(fn ($point, $index) => ($chartPad + ($index / $cashCount) * ($chartWidth - ($chartPad * 2))).','.($chartHeight - $chartPad - (($point['in'] / $cashMax) * ($chartHeight - ($chartPad * 2)))))->implode(' ');
        $cashOutPoints = collect($cashSeries)->map(fn ($point, $index) => ($chartPad + ($index / $cashCount) * ($chartWidth - ($chartPad * 2))).','.($chartHeight - $chartPad - (($point['out'] / $cashMax) * ($chartHeight - ($chartPad * 2)))))->implode(' ');
        $admissionPoints = collect($admissionSeries)->map(fn ($point, $index) => ($chartPad + ($index / $admissionCount) * ($chartWidth - ($chartPad * 2))).','.($chartHeight - $chartPad - (($point['value'] / $admissionMax) * ($chartHeight - ($chartPad * 2)))))->implode(' ');
    @endphp

    <div class="executive-dashboard" x-data="{ chart: 'cash', filtersOpen: false }">
        <section class="dashboard-command" aria-labelledby="dashboard-heading">
            <div>
                <p class="dashboard-eyebrow">Daniyal Group of Colleges</p>
                <h2 id="dashboard-heading">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }}, {{ filament()->auth()->user()->name }}</h2>
                <p>Live operational view for <strong>{{ $selectedCampus }}</strong>. Updated {{ $generatedAt }}.</p>
            </div>
            <button type="button" class="dashboard-filter-toggle" x-on:click="filtersOpen = !filtersOpen" x-bind:aria-expanded="filtersOpen.toString()">
                <x-heroicon-o-adjustments-horizontal />
                Dashboard filters
            </button>
            <div class="dashboard-filters" x-bind:class="filtersOpen ? 'is-open' : ''">
                @if($isSuperAdmin)
                    <label>Campus
                        <select wire:model.live="campusFilter" aria-label="Filter dashboard by campus">
                            <option value="all">All campuses</option>
                            @foreach($campuses as $campus)<option value="{{ $campus->id }}">{{ $campus->name }}</option>@endforeach
                        </select>
                    </label>
                @endif
                <label>Reporting month
                    <input type="month" wire:model.live="month" max="{{ now()->format('Y-m') }}" aria-label="Dashboard reporting month">
                </label>
                <span class="dashboard-period"><x-heroicon-o-calendar-days /> {{ $periodLabel }}</span>
            </div>
        </section>

        <div wire:loading.flex wire:target="campusFilter,month" class="dashboard-loading" role="status">
            <x-filament::loading-indicator /> Refreshing dashboard…
        </div>

        <section class="dashboard-kpis" aria-label="Key performance indicators" wire:loading.class="is-refreshing" wire:target="campusFilter,month">
            @foreach($metrics as $metric)
                <a href="{{ $links[$metric['route']] }}" class="dashboard-kpi dashboard-kpi--{{ $metric['tone'] }}">
                    <span class="dashboard-kpi__icon"><x-filament::icon :icon="$metric['icon']" /></span>
                    <span class="dashboard-kpi__value">{{ $metric['value'] }}</span>
                    <span class="dashboard-kpi__label">{{ $metric['label'] }}</span>
                    <span class="dashboard-kpi__detail">{{ $metric['detail'] }}</span>
                    <span class="dashboard-kpi__arrow" aria-hidden="true">→</span>
                </a>
            @endforeach
        </section>

        @if($isSuperAdmin)
            <section class="dashboard-campus-access" aria-labelledby="campus-access-title">
                <header>
                    <div>
                        <p class="dashboard-eyebrow">Campus workspaces</p>
                        <h3 id="campus-access-title">Quick Campus Login</h3>
                        <p>Open a campus dashboard while keeping your Super Admin session active.</p>
                    </div>
                    <x-heroicon-o-building-office-2 />
                </header>
                <div class="dashboard-campus-access__grid">
                    @forelse($campuses as $campus)
                        @php($principal = $campus->principals->first())
                        @if($principal)
                            <form method="POST" action="{{ route('admin.campus-access.enter', $campus) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                @csrf
                                <button type="submit" x-bind:disabled="submitting" aria-label="Open {{ $campus->city ?: $campus->name }} campus dashboard">
                                    <span class="dashboard-campus-access__sequence">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span>
                                        <strong>{{ $campus->city ?: $campus->name }}</strong>
                                        <small>{{ $campus->name }} · {{ $principal->name }}</small>
                                    </span>
                                    <x-heroicon-o-arrow-right />
                                    <em x-show="submitting" x-cloak>Opening…</em>
                                </button>
                            </form>
                        @else
                            <div class="dashboard-campus-access__disabled">
                                <span class="dashboard-campus-access__sequence">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span><strong>{{ $campus->city ?: $campus->name }}</strong><small>Assign an active Campus Principal</small></span>
                                <x-heroicon-o-exclamation-triangle />
                            </div>
                        @endif
                    @empty
                        <p class="dashboard-campus-access__empty">No city campuses are available.</p>
                    @endforelse
                </div>
            </section>
        @endif

        <section class="dashboard-workspace">
            <div class="dashboard-primary">
                <article class="dashboard-panel dashboard-panel--chart">
                    <header class="dashboard-panel__header">
                        <div><p class="dashboard-eyebrow">Performance</p><h3>Monthly activity</h3><p>Compare cash movement and admission volume for {{ $periodLabel }}.</p></div>
                        <div class="dashboard-tabs" role="tablist" aria-label="Select chart">
                            <button type="button" role="tab" x-on:click="chart = 'cash'" x-bind:aria-selected="chart === 'cash'" x-bind:class="chart === 'cash' ? 'is-active' : ''">Cash flow</button>
                            <button type="button" role="tab" x-on:click="chart = 'admissions'" x-bind:aria-selected="chart === 'admissions'" x-bind:class="chart === 'admissions' ? 'is-active' : ''">Admissions</button>
                        </div>
                    </header>

                    <div x-show="chart === 'cash'" role="tabpanel" class="dashboard-chart">
                        <div class="dashboard-chart__legend"><span><i class="is-in"></i> Cash in: PKR {{ number_format($finance['collected']) }}</span><span><i class="is-out"></i> Cash out: PKR {{ number_format($finance['spent']) }}</span></div>
                        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Daily cash received and expenses during {{ $periodLabel }}">
                            <line x1="{{ $chartPad }}" y1="{{ $chartHeight - $chartPad }}" x2="{{ $chartWidth - $chartPad }}" y2="{{ $chartHeight - $chartPad }}" class="chart-axis" />
                            <line x1="{{ $chartPad }}" y1="{{ $chartPad }}" x2="{{ $chartPad }}" y2="{{ $chartHeight - $chartPad }}" class="chart-axis" />
                            <polyline points="{{ $cashInPoints }}" class="chart-line chart-line--in" />
                            <polyline points="{{ $cashOutPoints }}" class="chart-line chart-line--out" />
                            @foreach($cashSeries as $index => $point)
                                @php($x = $chartPad + ($index / $cashCount) * ($chartWidth - ($chartPad * 2)))
                                @php($yIn = $chartHeight - $chartPad - (($point['in'] / $cashMax) * ($chartHeight - ($chartPad * 2))))
                                @php($yOut = $chartHeight - $chartPad - (($point['out'] / $cashMax) * ($chartHeight - ($chartPad * 2))))
                                <circle cx="{{ $x }}" cy="{{ $yIn }}" r="5" class="chart-point chart-point--in" tabindex="0"><title>{{ $point['label'] }}: PKR {{ number_format($point['in']) }} received</title></circle>
                                <circle cx="{{ $x }}" cy="{{ $yOut }}" r="5" class="chart-point chart-point--out" tabindex="0"><title>{{ $point['label'] }}: PKR {{ number_format($point['out']) }} spent</title></circle>
                            @endforeach
                        </svg>
                        <p class="dashboard-chart__note">Focus or hover a point for its exact daily amount.</p>
                    </div>

                    <div x-show="chart === 'admissions'" x-cloak role="tabpanel" class="dashboard-chart">
                        <div class="dashboard-chart__legend"><span><i class="is-admissions"></i> New admissions by day</span></div>
                        <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="Daily admissions during {{ $periodLabel }}">
                            <line x1="{{ $chartPad }}" y1="{{ $chartHeight - $chartPad }}" x2="{{ $chartWidth - $chartPad }}" y2="{{ $chartHeight - $chartPad }}" class="chart-axis" />
                            <polyline points="{{ $admissionPoints }}" class="chart-line chart-line--admissions" />
                            @foreach($admissionSeries as $index => $point)
                                @php($x = $chartPad + ($index / $admissionCount) * ($chartWidth - ($chartPad * 2)))
                                @php($y = $chartHeight - $chartPad - (($point['value'] / $admissionMax) * ($chartHeight - ($chartPad * 2))))
                                <circle cx="{{ $x }}" cy="{{ $y }}" r="5" class="chart-point chart-point--admissions" tabindex="0"><title>{{ $point['label'] }}: {{ $point['value'] }} admissions</title></circle>
                            @endforeach
                        </svg>
                    </div>
                </article>

                <article class="dashboard-panel">
                    <header class="dashboard-panel__header"><div><p class="dashboard-eyebrow">Latest activity</p><h3>Recent admissions</h3><p>Newest applications in the selected campus scope.</p></div><a href="{{ $links['admissions'] }}">View all →</a></header>
                    <div class="dashboard-table-wrap">
                        <table class="dashboard-table">
                            <thead><tr><th>Applicant</th><th>Program</th><th>Campus</th><th>Status</th><th>Received</th></tr></thead>
                            <tbody>
                                @forelse($recentAdmissions as $admission)
                                    <tr><td><a href="{{ \App\Filament\Resources\AdmissionResource::getUrl('edit', ['record' => $admission], panel: filament()->getCurrentPanel()?->getId()) }}">{{ $admission->applicant_name }}</a></td><td>{{ $admission->course?->name ?: 'Not selected' }}</td><td>{{ $admission->campus?->name ?: 'Unassigned' }}</td><td><span class="dashboard-status dashboard-status--{{ $admission->status }}">{{ ucfirst(str_replace('_', ' ', $admission->status)) }}</span></td><td>{{ $admission->created_at?->diffForHumans() }}</td></tr>
                                @empty
                                    <tr><td colspan="5" class="dashboard-empty">No admissions found for this scope.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                @if($isSuperAdmin)
                    <article class="dashboard-panel dashboard-franchise-activity">
                        <header class="dashboard-panel__header">
                            <div><p class="dashboard-eyebrow">Network activity</p><h3>Franchisor activity</h3><p>Current franchise seats, collections and recent ledgers.</p></div>
                            <a href="{{ $links['franchisors'] }}">View franchisors →</a>
                        </header>
                        <div class="dashboard-franchise-stats">
                            <a href="{{ $links['franchisors'] }}"><span>Active partners</span><strong>{{ number_format($franchise['active']) }}</strong></a>
                            <a href="{{ $links['franchisor-payments'] }}"><span>Enrolled seats</span><strong>{{ number_format($franchise['seats']) }}</strong></a>
                            <a href="{{ $links['franchisor-payments'] }}"><span>Collected</span><strong>PKR {{ number_format($franchise['paid']) }}</strong></a>
                            <a href="{{ $links['franchisor-payments'] }}"><span>Outstanding</span><strong>PKR {{ number_format($franchise['outstanding']) }}</strong></a>
                        </div>
                        <div class="dashboard-franchise-list">
                            @forelse($franchise['recent'] as $payment)
                                <a href="{{ \App\Filament\Resources\SuperAdminFranchisorPaymentResource::getUrl('edit', ['record' => $payment], panel: filament()->getCurrentPanel()?->getId()) }}">
                                    <span class="dashboard-franchise-list__icon"><x-heroicon-o-building-storefront /></span>
                                    <span><strong>{{ $payment->franchisor?->name ?: 'Unassigned franchisor' }}</strong><small>{{ $payment->admission?->applicant_name ?: 'Student ledger' }} · {{ $payment->created_at?->diffForHumans() }}</small></span>
                                    <span class="dashboard-status dashboard-status--{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                                    <strong>PKR {{ number_format($payment->paid_amount) }} / {{ number_format($payment->total_amount) }}</strong>
                                </a>
                            @empty
                                <div class="dashboard-franchise-empty"><x-heroicon-o-building-storefront /><span><strong>No franchisor ledger activity yet</strong><small>New franchise seat payments will appear here.</small></span></div>
                            @endforelse
                        </div>
                    </article>
                @endif
            </div>

            <aside class="dashboard-rail" aria-label="Operational summaries">
                <article class="dashboard-panel dashboard-finance-card">
                    <header><div><p class="dashboard-eyebrow">Fee receivables</p><h3>Collection progress</h3></div><strong>{{ $finance['collectionRate'] }}%</strong></header>
                    <div class="dashboard-progress"><span style="width: {{ min(100, $finance['collectionRate']) }}%"></span></div>
                    <dl><div><dt>Collected</dt><dd>PKR {{ number_format($finance['collected']) }}</dd></div><div><dt>Outstanding</dt><dd>PKR {{ number_format($finance['outstanding']) }}</dd></div><div><dt>Expenses</dt><dd>PKR {{ number_format($finance['spent']) }}</dd></div></dl>
                    <a href="{{ $links['fee-vouchers'] }}">Open receivables →</a>
                </article>

                @foreach([['title' => 'Student attendance', 'data' => $studentAttendance, 'link' => 'attendance', 'tone' => 'green'], ['title' => 'Staff attendance', 'data' => $staffAttendance, 'link' => 'teacher-attendance', 'tone' => 'blue']] as $attendanceCard)
                    <article class="dashboard-panel dashboard-attendance dashboard-attendance--{{ $attendanceCard['tone'] }}">
                        <header><div><p class="dashboard-eyebrow">Today</p><h3>{{ $attendanceCard['title'] }}</h3></div><span>{{ $attendanceCard['data']['marked'] }}/{{ $attendanceCard['data']['population'] }} marked</span></header>
                        @foreach($attendanceCard['data']['rows'] as $row)
                            <div class="attendance-row"><div><span>{{ $row['label'] }} · {{ $row['count'] }}</span><strong>{{ $row['percent'] }}%</strong></div><div class="attendance-track"><span class="attendance-track--{{ $row['status'] }}" style="width: {{ min(100, $row['percent']) }}%"></span></div></div>
                        @endforeach
                        <a href="{{ $links[$attendanceCard['link']] }}">Manage attendance →</a>
                    </article>
                @endforeach

                <article class="dashboard-panel dashboard-exceptions">
                    <header><div><p class="dashboard-eyebrow">Needs attention</p><h3>Operational exceptions</h3></div><x-heroicon-o-bell-alert /></header>
                    <a href="{{ $links['fee-vouchers'] }}"><span>Overdue vouchers</span><strong>{{ $exceptions['overdueVouchers'] }}</strong></a>
                    <a href="{{ $links['admissions'] }}"><span>Admissions missing documents</span><strong>{{ $exceptions['missingDocuments'] }}</strong></a>
                    <a href="{{ $links['admissions'] }}"><span>Pending admission records</span><strong>{{ $exceptions['pendingAdmissions'] }}</strong></a>
                </article>
            </aside>
        </section>

        <section class="dashboard-quick-actions" aria-labelledby="quick-actions-title">
            <header><div><p class="dashboard-eyebrow">Shortcuts</p><h3 id="quick-actions-title">Quick actions</h3></div></header>
            <div>
                <a href="{{ \App\Filament\Resources\AdmissionResource::getUrl('create', panel: filament()->getCurrentPanel()?->getId()) }}"><x-heroicon-o-user-plus /><span><strong>New admission</strong><small>Start the admission workflow</small></span></a>
                <a href="{{ \App\Filament\Resources\AttendanceResource::getUrl('create', panel: filament()->getCurrentPanel()?->getId()) }}"><x-heroicon-o-clipboard-document-check /><span><strong>Mark attendance</strong><small>Record student attendance</small></span></a>
                <a href="{{ \App\Filament\Resources\FeeCollectionResource::getUrl('index', panel: filament()->getCurrentPanel()?->getId()) }}"><x-heroicon-o-banknotes /><span><strong>Collect fee</strong><small>Post a student payment</small></span></a>
                <a href="{{ \App\Filament\Resources\TimetableResource::getUrl('teacher-create', panel: filament()->getCurrentPanel()?->getId()) }}"><x-heroicon-o-calendar-days /><span><strong>Create timetable</strong><small>Build a teacher schedule</small></span></a>
            </div>
        </section>
    </div>
</x-filament-panels::page>
