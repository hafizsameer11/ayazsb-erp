@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @php
        $config = match ($screen['slug']) {
            'store-issue' => ['panelTitle' => 'STORE ISSUANCE', 'showDepartment' => true, 'showParty' => false],
            'purchase-order' => ['panelTitle' => 'STORE PURCHASE ORDER', 'headerPartial' => 'erp.weaving.partials.purchase-order-header', 'showParty' => false],
            'purchase-return' => ['panelTitle' => 'STORE PURCHASE RETURN', 'showDepartment' => false, 'showParty' => true, 'showSourcePo' => true],
            default => ['panelTitle' => strtoupper($screen['label'])],
        };
    @endphp
    @include('erp.weaving.partials.transaction-shell', array_merge($config, [
        'gridPartial' => 'erp.weaving.partials.store-line-grid',
        'showVoucher' => true,
    ]))
@endsection
