{{-- Empty value must have no label text or Tom Select shows it twice (item + input placeholder). --}}
<option value=""></option>
@foreach (($accounts ?? []) as $acc)
    <option value="{{ $acc->id }}" @selected(isset($selectedAccountId) && (string) $selectedAccountId === (string) $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
@endforeach
