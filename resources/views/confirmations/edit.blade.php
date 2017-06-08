@extends('adminlte::page')

@section('title', 'LaraTravel')

@section('content')
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <ul class="breadcrumb">
          <li><a href="{{ url('/home') }}">Dashboard</a></li>
      		<li><a href="{{ url('/admin/confirmations') }}">Confirmation</a></li>
      		<li class="active">Edit Confirmation</li>
        </ul>
        <div class="panel panel-default">
          <div class="panel-heading">
            <h2 class="panel-title">Edit confirmation</h2>
          </div>
          <div class="panel-body">
            {!! Form::model($confirmation, ['url' => route('confirmations.update', $confirmation->id),'method'=>'put', 'class'=>'form-horizontal']) !!}
            @include('confirmations._form')
            {!! Form::close() !!}
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection