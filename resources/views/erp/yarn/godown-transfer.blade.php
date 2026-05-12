@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'none',
        'showGodownPair' => true,
        'lineLabel' => 'Yarn godown transfer lines',
        'defaultVoucherType' => 'GTV',
    ])
@endsection
