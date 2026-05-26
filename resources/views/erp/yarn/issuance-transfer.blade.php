@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.transaction-form', ['formVariant' => 'issuance-transfer'])
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpGreyConversionContracts = @json($greyConversionContractsPayload ?? []);
        window.erpFromGreyContractsByAccount = @json($fromGreyContractsByAccount ?? []);
        window.erpIssuableLinesByPartyContract = @json($yarnIssuableLinesByPartyContract ?? []);
    </script>
@endpush
