@extends('layouts.erp')

@section('title', $moduleLabel)

@section('content')
    <div class="erp-panel flex min-h-[420px] flex-col border border-slate-500 bg-white shadow-md">
        <div class="border-b border-slate-400 bg-[#e8e8e8] px-3 py-2 text-[12px] font-semibold text-slate-800">
            Pulse ERP — {{ $moduleLabel }}
        </div>
        <div class="flex-1 p-4">
            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($groups as $groupTitle => $items)
                    <section class="border border-slate-400 bg-[#f5f5f5] p-2">
                        <h2 class="mb-2 text-[11px] font-bold uppercase text-slate-600">{{ $groupTitle }}</h2>
                        <ul class="space-y-0.5 text-[12px]">
                            @foreach ($items as $item)
                                <li>
                                    @allowed($moduleKey . '.' . $item['slug'] . '.view')
                                        <a class="erp-tree-link" href="{{ route('erp.' . $moduleKey . '.screen', ['screen' => $item['slug']]) }}">
                                            {{ $item['label'] }}
                                        </a>
                                    @else
                                        <span class="block cursor-not-allowed rounded px-1 py-0.5 text-slate-400">
                                            {{ $item['label'] }}
                                        </span>
                                    @endallowed
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
@endsection

