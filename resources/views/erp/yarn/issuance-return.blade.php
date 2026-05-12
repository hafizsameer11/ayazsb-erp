@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'single',
        'singleContracts' => $contracts,
        'showSourceIssue' => true,
        'lineLabel' => 'Yarn return lines',
        'defaultVoucherType' => 'YOR',
    ])
@endsection
