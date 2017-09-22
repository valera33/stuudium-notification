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
    @if ($mark['prev_mark']!=='')
      <span style="color: #808080"> {{ $mark['prev_mark'] }}/</span>
    @endif
    @if ((int)$mark['nr'] === 5)
      <span style="color: #008000">
    @elseif ($mark['nr'] === 4)
      <span style="color: #008000">
    @elseif ($mark['nr'] === 3)
      <span style="color: #FF4500">
    @else
      <span style="color: #FF0000; font-weight: bold;">
    @endif
    {{ $mark['mark'] }}</span> {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif
    <span style="color: #808080"> {{ $mark['date'] }} </span><br>
@endforeach
@endif

@if (isset($previousMarks) && count($previousMarks)>0)
<br><b>Eelmised Hinnad:</b><br>
@foreach ($previousMarks as $mark)
    @if (!is_empty($mark['prev_mark']))
      <span style="color: #808080"> {{ $mark['prev_mark'] }}/</span>
    @endif
    @if ($mark['nr'] === 5)
      <span style="color: #008000">
    @elseif ($mark['nr'] === 4)
      <span style="color: #008000">
    @elseif ($mark['nr'] === 3)
      <span style="color: #FF4500">
    @else
      <span style="color: #FF0000; font-weight: bold;">
    @endif
    {{ $mark['mark'] }}</span> {{ $mark['lesson'] }} @if (!empty($mark['notes'])) ({{ $mark['notes']}}) @endif
    <span style="color: #808080"> {{ $mark['date'] }} </span>
@endforeach
@endif

<br><br><a href="{{ $url }}">{{ $url }}</a><br><br>
