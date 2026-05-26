@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.without-contract-form', ['direction' => 'purchase'])
@endsection
