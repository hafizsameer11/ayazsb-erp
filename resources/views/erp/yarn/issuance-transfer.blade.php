@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.movement-form', [
        'contractMode' => 'transfer',
        'showGodownPair' => false,
        'lineLabel' => 'Yarn contract transfer lines',
        'defaultVoucherType' => 'BTV',
    ])
@endsection
