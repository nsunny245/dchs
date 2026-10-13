<style>
    .teacher-sheet { page-break-inside: auto; }
    .teacher-sheet h2 { font-size: 14px; margin-bottom: 4px; text-align: center; }
    .teacher-sheet h3 { font-size: 12px; margin: 8px 0; }
    .teacher-sheet .grid-table { table-layout: fixed; }
    .teacher-sheet .grid-table td { height: 62px; overflow-wrap: break-word; }
    .teacher-sheet .grid-table thead { display: table-header-group; }
    .teacher-sheet .grid-table tr { page-break-inside: avoid; }
    .teacher-sheet .teacher-name { width: 16%; font-weight: bold; background: #F1F5F9; }
    .teacher-sheet .cell-text { white-space: pre-wrap; }
</style>
@foreach($layout['groups'] as $group)
    <div class="teacher-sheet">
        <table style="width:100%; border-collapse:collapse; page-break-inside:avoid;">
            <tr><td style="text-align:center; border:0; padding:0;">
                <h2>{{ $layout['heading'] }}</h2>
                <div>{{ $layout['subtitle'] }} — {{ $timetable->title }}</div>
                <h3 style="text-align:left;">{{ $group['label'] }} <small>({{ implode(', ', array_map('ucfirst', $group['days'])) }})</small></h3>
            </td></tr>
        </table>
        <table class="grid-table">
            <thead><tr><th class="teacher-name">Teacher name</th>
                @foreach($group['periods'] as $period)<th>{{ $period['label'] }}<br>{{ $period['start'] }}–{{ $period['end'] }}</th>@endforeach
            </tr></thead>
            <tbody>
                @foreach($group['teachers'] as $teacher)
                    <tr><td class="teacher-name">{{ $teacher['name'] }}</td>
                        @foreach($group['periods'] as $p => $period)
                            <td class="{{ $period['is_break'] ? 'break-row' : '' }}">
                                @if($period['is_break']){{ $period['label'] }}@else
                                    <div class="cell-text">{{ $teacher['cells'][$p]['text'] }}</div>
                                    @if($teacher['cells'][$p]['days'])<br><small>{{ implode(', ', array_map('ucfirst', $teacher['cells'][$p]['days'])) }} only</small>@endif
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if(! $loop->last)
        <div style="page-break-after: always;"></div>
    @endif
@endforeach
@if($timetable->notes)<p style="white-space:pre-wrap">{{ $timetable->notes }}</p>@endif
