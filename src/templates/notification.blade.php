{{ $name }} uuenduesed koolis:
<br><br>

@if (isset($absenceTotal))
Puudumised kokku muutunud: {{ $absenceTotal['from'] }} -> {{ $absenceTotal['to'] }}
<br>
@endif

@if (isset($absenceTotal))
Puudumised pohjuseta muutunud: {{ $absenceTotal['from'] }} -> {{ $absenceTotal['to'] }}
<br>
@endif

@if (isset($marks) && count($marks)>0)
Hinnad muutunud:<br>
@foreach ($marks as $mark)
    {{ $mark['mark'] }} {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif<br>
@endforeach
@endif

@if (isset($previousMarks) && count($previousMarks)>0)
Eelmised Hinnad:<br>
@foreach ($previousMarks as $mark)
    {{ $mark['mark'] }} {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif<br>
@endforeach
@endif

<br>{{ $url }}<br><br>
