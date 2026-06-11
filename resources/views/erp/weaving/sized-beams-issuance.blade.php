@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.weaving.partials.transaction-shell', [
        'panelTitle' => 'SIZED BEAMS ISSUANCE',
        'showVoucher' => false,
        'showDepartment' => false,
        'headerPartial' => 'erp.weaving.partials.beams-issuance-header',
        'gridPartial' => 'erp.weaving.partials.beams-issuance-grid',
    ])
@endsection
