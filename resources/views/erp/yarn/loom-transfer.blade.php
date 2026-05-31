@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.loom-transfer-form')
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpYarnContracts = @json($yarnContractsPayload ?? []);
    </script>
@endpush
