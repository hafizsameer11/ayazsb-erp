@extends('layouts.erp')

@section('title', $screen['label'])

@section('content')
    @include('erp.yarn.partials.contract-form', ['direction' => 'purchase'])
@endsection
