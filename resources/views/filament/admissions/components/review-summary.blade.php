@php
    $rows = [
        ['heroicon-o-camera', 'Student Profile', 'Student photo and applicant profile', 'student-photo'],
        ['heroicon-o-identification', 'Personal Information', "CNIC: {$cnic} · Date of Birth: {$dob} · Gender: {$gender}", 'student-information'],
        ['heroicon-o-users', 'Guardian Information', "Name: {$guardian} · Contact: {$guardianPhone}", 'parent-or-guardian'],
        ['heroicon-o-academic-cap', 'Academic Qualifications', "{$qualificationCount} qualification record(s) added", 'academic-details'],
        ['heroicon-o-document-duplicate', 'Documents', "{$documentCount} document(s) uploaded", 'documents-vault'],
        ['heroicon-o-book-open', 'Course Assignment', "{$course} · {$campus} · {$session} · {$shift}", 'course-fee-plan'],
        ['heroicon-o-wallet', 'Fee & Installment Plan', 'Total Fee: PKR '.number_format($totalFee, 2)." · {$installments} installment(s)", 'course-fee-plan'],
    ];
@endphp

<div class="admission-review">
    <div class="admission-review__success">
        <span aria-hidden="true"><x-filament::icon icon="heroicon-o-check-circle" /></span>
        <span>
            <strong>Review Summary: All steps are ready for final confirmation</strong>
            <small>You are about to submit the admission for {{ $studentName }}.</small>
        </span>
    </div>

    <div class="admission-review__identity">
        <span><strong>Student Name</strong>{{ $studentName }}</span>
        <span><strong>Selected Course</strong>{{ $course }}</span>
        <span><strong>Campus</strong>{{ $campus }}</span>
        <span><strong>Shift</strong>{{ $shift }}</span>
    </div>

    <div class="admission-review__rows">
        @foreach ($rows as [$icon, $title, $detail, $editStep])
            <div class="admission-review-row">
                <span class="admission-review-row__icon" aria-hidden="true">
                    <x-filament::icon :icon="$icon" />
                </span>
                <span class="admission-review-row__copy">
                    <strong>{{ $title }}</strong>
                    <small>{{ $detail }}</small>
                </span>
                <span class="admission-review-row__status">
                    <x-filament::icon icon="heroicon-o-check-circle" />
                    Completed
                </span>
                <button type="button" x-on:click="step = @js($editStep); scroll()" class="admission-review-row__edit">
                    <x-filament::icon icon="heroicon-o-pencil-square" />
                    Edit
                </button>
            </div>
        @endforeach
    </div>
</div>
