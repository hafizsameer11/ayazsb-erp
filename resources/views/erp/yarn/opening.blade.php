@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.opening-form')
@endsection

@push('scripts')
    <script>
        window.erpYarnItems = @json($yarnItemsPayload ?? []);
        window.erpYarnContractRemarksByAccount = @json($yarnContractRemarksByAccount ?? []);
    </script>
@endpush
