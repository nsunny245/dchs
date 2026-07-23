@php
    $itemsCount = $voucher->items->count();
    $densityClass = 'density-normal';
    if ($itemsCount >= 9) {
        $densityClass = 'density-dense';
    } elseif ($itemsCount >= 7) {
        $densityClass = 'density-compact';
    }
@endphp

<div class="voucher-wrapper {{ $layout }} {{ $densityClass }}">
    <!-- Cut line scissors indicator if horizontal -->
    @if($layout === 'horizontal' && $copyLabel !== 'Bank Copy')
        <div class="cut-indicator">
            <span>✂--------------------------------------------------------------------------------------------------------------------------------------</span>
        </div>
    @endif

    <div class="voucher-inner">
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="logo-cell">
                    <img src="{{ public_path('images/branding/daniyal-group-of-colleges-logo.png') }}" alt="Logo" class="college-logo" onerror="this.src='{{ asset('images/branding/daniyal-group-of-colleges-logo.png') }}'">
                </td>
                <td class="title-cell">
                    <h1 class="college-name">Daniyal Group of Colleges</h1>
                    <p class="tagline">Where Success Is A Tradition</p>
                </td>
            </tr>
        </table>
        
        <div class="badge-cell">
            <div class="copy-badge">{{ $copyLabel }}</div>
            <div class="voucher-title">FEE VOUCHER / INVOICE</div>
            <div class="voucher-type-label">
                {{ $voucher->voucher_type === 'new_enrollment' ? 'NEW ENROLLMENT' : 'MONTHLY / INSTALLMENT FEE' }}
            </div>
        </div>

        <!-- STUDENT INFORMATION -->
        @if($layout === 'portrait')
            <!-- Stacked layout for thin portrait columns -->
            <table class="student-info-table">
                <tr>
                    <td class="info-label">Student Name:</td>
                    <td class="info-value">{{ $voucher->student->full_name }}</td>
                </tr>
                <tr>
                    <td class="info-label">Student ID:</td>
                    <td class="info-value font-bold">{{ $voucher->student->enrollment_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Father / Guardian:</td>
                    <td class="info-value">{{ $voucher->student->admission->father_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Mobile No:</td>
                    <td class="info-value">{{ $voucher->student->admission->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Course / Program:</td>
                    <td class="info-value">{{ $voucher->course->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Session:</td>
                    <td class="info-value">{{ $voucher->academicSession->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Campus:</td>
                    <td class="info-value">{{ $voucher->campus->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Voucher No:</td>
                    <td class="info-value font-bold">{{ $voucher->voucher_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Issue Date:</td>
                    <td class="info-value">{{ $voucher->issue_date ? $voucher->issue_date->format('d-M-Y') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Due Date:</td>
                    <td class="info-value font-bold text-danger">{{ $voucher->due_date ? $voucher->due_date->format('d-M-Y') : 'N/A' }}</td>
                </tr>
            </table>
        @else
            <!-- Side-by-side layout for horizontal copies -->
            <table class="student-info-table">
                <tr>
                    <td class="info-label">Student Name:</td>
                    <td class="info-value">{{ $voucher->student->full_name }}</td>
                    <td class="info-label">Student ID:</td>
                    <td class="info-value font-bold">{{ $voucher->student->enrollment_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Father / Guardian:</td>
                    <td class="info-value">{{ $voucher->student->admission->father_name ?? 'N/A' }}</td>
                    <td class="info-label">Mobile No:</td>
                    <td class="info-value">{{ $voucher->student->admission->phone ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Course / Program:</td>
                    <td class="info-value">{{ $voucher->course->name ?? 'N/A' }}</td>
                    <td class="info-label">Session:</td>
                    <td class="info-value">{{ $voucher->academicSession->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Campus:</td>
                    <td class="info-value">{{ $voucher->campus->name ?? 'N/A' }}</td>
                    <td class="info-label">Voucher No:</td>
                    <td class="info-value font-bold">{{ $voucher->voucher_number }}</td>
                </tr>
                <tr>
                    <td class="info-label">Issue Date:</td>
                    <td class="info-value">{{ $voucher->issue_date ? $voucher->issue_date->format('d-M-Y') : 'N/A' }}</td>
                    <td class="info-label">Due Date:</td>
                    <td class="info-value font-bold text-danger">{{ $voucher->due_date ? $voucher->due_date->format('d-M-Y') : 'N/A' }}</td>
                </tr>
            </table>
        @endif

        <!-- FEE TABLE -->
        <table class="fee-table">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;">Sr.</th>
                    <th style="width: 60%; text-align: left;">Fee Details</th>
                    <th style="width: 30%; text-align: right;">Amount (PKR)</th>
                </tr>
            </thead>
            <tbody>
                @php $sr = 1; @endphp
                @foreach($voucher->items as $item)
                    <tr>
                        <td style="text-align: center;">{{ $sr++ }}</td>
                        <td class="fee-description">{{ $item->description }}</td>
                        <td style="text-align: right;">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach

                <!-- Summary / Additional fields -->
                @if($voucher->previous_balance > 0)
                    <tr class="summary-row">
                        <td colspan="2" class="text-right">Arrears / Previous Balance:</td>
                        <td style="text-align: right;">{{ number_format($voucher->previous_balance, 2) }}</td>
                    </tr>
                @endif
                @if($voucher->late_fee_amount > 0)
                    <tr class="summary-row">
                        <td colspan="2" class="text-right">Late Fee:</td>
                        <td style="text-align: right;">{{ number_format($voucher->late_fee_amount, 2) }}</td>
                    </tr>
                @endif
                @if($voucher->fine_amount > 0)
                    <tr class="summary-row">
                        <td colspan="2" class="text-right">Fine:</td>
                        <td style="text-align: right;">{{ number_format($voucher->fine_amount, 2) }}</td>
                    </tr>
                @endif
                @if($voucher->discount_amount > 0)
                    <tr class="summary-row text-success">
                        <td colspan="2" class="text-right">Discount:</td>
                        <td style="text-align: right;">-{{ number_format($voucher->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if($voucher->scholarship_amount > 0)
                    <tr class="summary-row text-success">
                        <td colspan="2" class="text-right">Scholarship Adjustment:</td>
                        <td style="text-align: right;">-{{ number_format($voucher->scholarship_amount, 2) }}</td>
                    </tr>
                @endif

                <!-- Total Payable -->
                <tr class="total-row">
                    <td colspan="2" class="text-right font-bold">TOTAL PAYABLE:</td>
                    <td style="text-align: right; font-weight: bold;">PKR {{ number_format($voucher->total_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- FOOTER / NOTE AND SIGNATURES -->
        <table class="footer-layout-table">
            <tr>
                <td class="notes-cell">
                    <div class="legal-note">
                        <strong>Note:</strong> Fee once deposited is non-refundable and non-transferable.<br>
                        • Please deposit the fee before the due date.<br>
                        • Keep the Student Copy for your record.<br>
                        • Late fee may apply according to college policy.
                    </div>
                </td>
            </tr>
            @if($layout === 'portrait')
                <tr>
                    <td class="sig-cell">
                        <div class="sig-wrapper" style="margin-top: 10px;">
                            <div class="sig-line">Cashier / Bank Stamp</div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="sig-cell">
                        <div class="sig-wrapper" style="margin-top: 10px;">
                            <div class="sig-line">Authorized Signature</div>
                        </div>
                    </td>
                </tr>
            @else
                <tr>
                    <td class="sig-cell">
                        <div class="sig-wrapper">
                            <div class="sig-line">Cashier / Bank Stamp</div>
                        </div>
                    </td>
                    <td class="sig-cell">
                        <div class="sig-wrapper">
                            <div class="sig-line">Authorized Signature</div>
                        </div>
                    </td>
                </tr>
            @endif
        </table>
    </div>
</div>
