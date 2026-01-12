@extends('admin.master')

@section('title')
    Zmiana hasła
@stop

@section('content')
<div id="page-wrapper">
  <div class="row justify-content-between">
    <div class="col-lg-4 col-md-6 col-sm-8">
      <h1 class="page-header mb-4 text-center">
        Zmiana hasła: <br>{{ $user->username }}
      </h1>

      {{ html()->form('POST', action('Admin\DashboardController@postChangePassword', [$user->id, $flag]))->open() }}
        <div class="mb-3">
          {{ html()->label('Hasło')->class('form-label') }}
          {{ html()->password('password')
              ->class('form-control')
              ->placeholder('Nowe hasło')
              ->required()
              ->autofocus() }}
        </div>

        <div class="d-flex justify-content-between mt-3">
          {{ html()->button('Anuluj')
              ->type('button')
              ->class('btn btn-warning button-menu')
              ->attribute('onclick', "window.history.back()") }}

          {{ html()->submit('Zapisz')
              ->class('btn btn-success button-menu') }}
        </div>
      {{ html()->form()->close() }}
    </div>
  </div>
</div>

@stop