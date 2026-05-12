@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'single',
        'singleContracts' => $contracts,
        'showSourceIssue' => true,
        'showAdjustment' => true,
        'lineLabel' => 'Yarn gain / shortage lines',
        'defaultVoucherType' => 'CN',
    ])
@endsection
