@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.transaction-form', ['formVariant' => 'issuance'])
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpGreyConversionContracts = @json($greyConversionContractsPayload ?? []);
        window.erpYarnIssuances = @json($yarnIssuanceOptions ?? []);
    </script>
@endpush
