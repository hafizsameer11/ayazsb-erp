@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.godown-transfer-form')
@endsection

@push('scripts')
    <script>window.erpYarnItems = @json($yarnItemsPayload ?? []);</script>
@endpush
