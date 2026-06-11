@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.weaving.partials.transaction-shell', [
        'panelTitle' => strtoupper($screen['label']),
        'gridPartial' => 'erp.weaving.partials.fabric-line-grid',
        'showVoucher' => in_array($screen['slug'], [
            'fabric-issue-conversion-kachi',
            'fabric-issue-conversion-pachi',
            'fabric-issue-sale-kachi',
            'rejection-sale',
        ], true),
        'showParty' => false,
        'showDepartment' => false,
    ])
@endsection
