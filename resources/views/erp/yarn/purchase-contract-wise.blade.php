@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.contract-wise-form', [
        'direction' => 'purchase',
        'contracts' => $purchaseContracts,
    ])
@endsection
