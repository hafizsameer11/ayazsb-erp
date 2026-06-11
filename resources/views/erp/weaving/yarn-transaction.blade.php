@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.weaving.partials.transaction-shell', [
        'panelTitle' => strtoupper($screen['label']),
        'gridPartial' => 'erp.weaving.partials.yarn-line-grid',
        'showVoucher' => in_array($screen['slug'], ['yarn-issuance-to-sizing', 'yarn-return-stock-to-party'], true),
        'showParty' => false,
        'showDepartment' => false,
    ])
@endsection
