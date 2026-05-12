@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'single',
        'singleContracts' => $saleContracts,
        'showGodownPair' => false,
        'lineLabel' => 'Contract-wise sale lines',
        'defaultVoucherType' => 'YSV',
    ])
@endsection
