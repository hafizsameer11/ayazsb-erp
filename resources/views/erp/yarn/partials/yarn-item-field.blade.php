@php
    $name = $name ?? 'item_id';
    $selected = $selected ?? '';
    $required = $required ?? false;
    $readonly = $readonly ?? false;
    $itemsPayload = collect($yarnItemsPayload ?? []);
@endphp
<label class="erp-field {{ $class ?? '' }}">
    <span class="erp-label">Yarn Id</span>
    <div class="grid grid-cols-[120px_1fr] gap-1">
        <select class="erp-input" name="{{ $name }}" data-yarn-item-select @if($required) required @endif @if($readonly) disabled @endif>
            <option value=""></option>
            @foreach($itemsPayload as $item)
                <option value="{{ $item['id'] }}" data-payload="{{ json_encode($item) }}" @selected((string)$selected===(string)$item['id'])>{{ $item['code'] }}</option>
            @endforeach
        </select>
        <input class="erp-input bg-slate-50" data-yarn-item-description readonly value="{{ $itemsPayload->firstWhere('id', (int)$selected)['name'] ?? '' }}">
    </div>
    @if($readonly && $selected)
        <input type="hidden" name="{{ $name }}" value="{{ $selected }}">
    @endif
</label>
