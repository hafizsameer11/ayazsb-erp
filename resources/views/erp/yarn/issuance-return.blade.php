@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.transaction-form', ['formVariant' => 'issuance-return'])
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpGreyConversionContracts = @json($greyConversionContractsPayload ?? []);
        window.erpYarnIssuances = @json($yarnIssuanceOptions ?? []);
        window.erpIssuableLinesByPartyContract = @json($yarnIssuableLinesByPartyContract ?? []);
    </script>
@endpush
