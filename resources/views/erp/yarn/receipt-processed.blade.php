@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.receipt-processed-form', ['autoConsumption' => false])
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpIssuanceConsumptionOptions = @json($yarnIssuanceConsumptionOptions ?? []);
        window.erpBlendItems = @json($yarnBlendItemsPayload ?? []);
    </script>
@endpush
