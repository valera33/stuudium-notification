{{ $name }} uuenduesed koolis:
<br><br>

@if (isset($absenceTotal))
Puudumised kokku muutunud: {{ $absenceTotal['from'] }} -> {{ $absenceTotal['to'] }}
<br>
@endif

@if (isset($absenceBad))
Puudumised pohjuseta muutunud: {{ $absenceBad['from'] }} -> {{ $absenceBad['to'] }}
<br><br>
@endif

@if (isset($marks) && count($marks)>0)
<br><b>Hinnad muutunud:</b><br>
@foreach ($marks as $mark)
    @if ($mark['mark'] === 5)
      <span style="color: #008000">
    @elseif ($mark['mark'] === 4)
      <span style="color: #008000">
    @elseif ($mark['mark'] === 3)
      <span style="color: #FF4500">
    @else
      <span style="color: #FF0000; font-weight: bold;">
    @endif
    {{ $mark['mark'] }}</span> {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif<br>
@endforeach
@endif

@if (isset($previousMarks) && count($previousMarks)>0)
<br><b>Eelmised Hinnad:</b><br>
@foreach ($previousMarks as $mark)
    {{ $mark['mark'] }} {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif<br>
@endforeach
@endif

<br><br><a href="{{ $url }}">{{ $url }}</a><br><br>
