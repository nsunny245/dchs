<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $timetable->title }}</title>
    <style>
        @page { size: A4 landscape; margin: 15mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #0A1526; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .header-table td { vertical-align: middle; }
        .logo { height: 50px; width: auto; }
        .title { font-size: 18px; font-weight: bold; color: #082A4A; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 11px; color: #D89A34; font-weight: bold; margin-top: 2px; }
        .meta-box { background-color: #F8FAFC; border: 1px solid #E2E8F0; padding: 8px; border-radius: 6px; font-size: 9px; }
        .meta-box table { width: 100%; border-collapse: collapse; }
        .meta-box td { padding: 3px 6px; }
        .grid-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9px; }
        .grid-table th { background-color: #082A4A; color: #FFFFFF; font-weight: bold; padding: 6px; border: 1px solid #06233F; text-transform: uppercase; text-align: center; }
        .grid-table td { border: 1px solid #CBD5E1; padding: 5px; text-align: center; vertical-align: top; height: 35px; }
        .period-col { background-color: #F1F5F9; font-weight: bold; width: 12%; }
        .break-row { background-color: #FEF3C7; color: #92400E; font-weight: bold; text-align: center; }
        .slot-title { font-weight: bold; color: #0F172A; font-size: 9px; }
        .slot-teacher { color: #475569; font-size: 8px; }
        .slot-room { color: #64748B; font-size: 8px; font-style: italic; }
        .signatures { margin-top: 25px; width: 100%; border-collapse: collapse; font-size: 9px; text-align: center; }
        .signatures td { width: 33%; padding-top: 40px; border-top: 1px border-slate-300; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td style="width: 15%;">
                @php
                    $logoPath = public_path('images/branding/daniyal-group-of-colleges-logo.png');
                @endphp
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}" class="logo" />
                @endif
            </td>
            <td style="width: 55%;">
                <h1 class="title">Daniyal Group of Colleges</h1>
                <div class="subtitle">{{ $timetable->campus->name ?? 'Campus' }} — Program Academic Timetable</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div class="meta-box">
                    <table>
                        <tr><td><strong>Program:</strong></td><td>{{ $timetable->course->name ?? 'Course' }}</td></tr>
                        <tr><td><strong>Session / Batch:</strong></td><td>{{ $timetable->academicSession->name ?? 'N/A' }} ({{ $timetable->batch_name }})</td></tr>
                        <tr><td><strong>Semester / Sec:</strong></td><td>{{ $timetable->semester_name }} — {{ $timetable->section_name }}</td></tr>
                        <tr><td><strong>Effective Date:</strong></td><td>{{ $timetable->effective_from ? $timetable->effective_from->format('d M Y') : 'N/A' }}</td></tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 10%;">Time</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
            </tr>
        </thead>
        <tbody>
            @foreach($periods as $p)
                @php
                    $st = date('H:i', strtotime($p->start_time));
                    $et = date('H:i', strtotime($p->end_time));
                @endphp
                @if($p->is_break)
                    <tr class="break-row">
                        <td class="period-col">{{ $st }}-{{ $et }}</td>
                        <td colspan="6">● {{ strtoupper($p->name) }} BREAK PERIOD ●</td>
                    </tr>
                @else
                    <tr>
                        <td class="period-col">{{ $st }}-{{ $et }}</td>
                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'] as $day)
                            @php
                                $cellSlots = $timetable->slots->filter(function ($s) use ($day, $st) {
                                    return strtolower($s->day_of_week) === $day && date('H:i', strtotime($s->start_time)) === $st;
                                });
                            @endphp
                            <td>
                                @foreach($cellSlots as $slot)
                                    <div class="slot-title">{{ $slot->subject_name }}</div>
                                    <div class="slot-teacher">{{ $slot->teacher?->full_name ?? 'Faculty' }}</div>
                                    @if($slot->room)<div class="slot-room">[{{ $slot->room->name }}]</div>@endif
                                @endforeach
                            </td>
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>_______________________<br><strong>Prepared By (Coordinator)</strong></td>
            <td>_______________________<br><strong>Verified By (HOD)</strong></td>
            <td>_______________________<br><strong>Approved By (Campus Principal)</strong></td>
        </tr>
    </table>
</body>
</html>
