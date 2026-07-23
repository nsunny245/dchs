@php
    $tips = [
        1 => 'Use a clear portrait with a white or light-blue background. Keep the face centered and fully visible.',
        2 => 'Double-check CNIC/B-Form and Date of Birth. These details must match official records.',
        3 => 'Confirm the guardian CNIC, mobile number, and emergency contact before continuing.',
        4 => 'Enter marks exactly as shown on the result card. Percentage is calculated automatically.',
        5 => 'Ensure all required documents are clear and verified. Add notes for anything still pending.',
        6 => 'Review the official fee plan, concession, and installment schedule before proceeding.',
        7 => 'Review every completed section carefully. Admission documents are generated after submission.',
    ];
@endphp

<aside class="admission-context" aria-label="Admission progress and summary">
    <section class="admission-context-card admission-progress-card">
        <div class="admission-context-card__accent" aria-hidden="true"></div>
        <header class="admission-context-card__title">
            <span class="admission-context-card__icon">
                <x-filament::icon icon="heroicon-o-shield-check" />
            </span>
            <span>Progress Overview</span>
        </header>
        <div class="admission-progress-card__meta">
            <span>Step {{ $stepIndex }} of 7 {{ $stepIndex === 7 ? 'Completed' : 'Active' }}</span>
            <strong>{{ $percentage }}%</strong>
        </div>
        <div class="admission-progress-card__track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $percentage }}">
            <span style="width: {{ $percentage }}%"></span>
        </div>
    </section>

    <section class="admission-context-card admission-summary-card">
        <h3>Admission Summary</h3>

        @foreach ([
            ['heroicon-o-user', 'Student Name', $studentName],
            ['heroicon-o-academic-cap', 'Course', $course],
            ['heroicon-o-building-library', 'Campus', $campus],
            ['heroicon-o-calendar-days', 'Session', $session],
            ['heroicon-o-clock', 'Shift', $shift],
        ] as [$itemIcon, $label, $value])
            <div class="admission-summary-item">
                <span class="admission-summary-item__icon" aria-hidden="true">
                    <x-filament::icon :icon="$itemIcon" />
                </span>
                <span class="admission-summary-item__copy">
                    <small>{{ $label }}</small>
                    <strong>{{ $value }}</strong>
                </span>
            </div>
        @endforeach

        <div class="admission-quick-tip">
            <span class="admission-quick-tip__icon" aria-hidden="true">
                <x-filament::icon icon="heroicon-o-light-bulb" />
            </span>
            <span>
                <strong>Quick Tips</strong>
                <small>{{ $tips[$stepIndex] }}</small>
            </span>
        </div>
    </section>
</aside>
