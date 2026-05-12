@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'single',
        'singleContracts' => $contracts,
        'showGodownPair' => false,
        'lineLabel' => 'Yarn issuance lines',
        'defaultVoucherType' => 'YO',
    ])
@endsection
