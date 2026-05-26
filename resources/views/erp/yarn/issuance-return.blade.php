@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.transaction-form', ['formVariant' => 'issuance-return'])
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpYarnIssuances = @json($yarnIssuanceOptions ?? []);
    </script>
@endpush
