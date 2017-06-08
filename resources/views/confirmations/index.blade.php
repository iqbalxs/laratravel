@extends('adminlte::page')

@section('title', 'LaraTravel')

@section('content')
@include('layouts._flash')
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li><a href="{{ url('/home') }}">Dashboard</a></li>
          <li class="active">Confirmation</li>
        </ul>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h2 class="panel-title">Confirmation</h2>
          </div>
          <div class="panel-body">
            {!! $html->table(['class'=>'table-striped']) !!}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('js')
{!! $html->scripts() !!}
@endsection