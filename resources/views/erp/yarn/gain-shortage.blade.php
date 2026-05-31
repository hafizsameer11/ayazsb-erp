@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    <div class="space-y-4">
        @include('erp.yarn.partials.gain-shortage-panel', ['side' => 'gain'])
        @include('erp.yarn.partials.gain-shortage-panel', ['side' => 'shortage'])
    </div>
    @include('erp.yarn.partials.recent-transactions')
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpYarnIssuances = @json($yarnIssuanceOptions ?? []);
        window.erpYarnContracts = @json($yarnContractsPayload ?? []);
    </script>
@endpush
