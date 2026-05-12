@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'single',
        'singleContracts' => $purchaseContracts,
        'showGodownPair' => false,
        'lineLabel' => 'Contract-wise purchase lines',
        'defaultVoucherType' => 'PV',
    ])
@endsection
