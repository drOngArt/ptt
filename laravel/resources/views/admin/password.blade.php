@extends('admin.master')

@section('title')
    Zmiana hasła
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Zmiana hasła: {{$user->username}}</h1>
            </div>
            <!-- /.col-lg-12 -->
        </div>
        <!-- /.row -->
        <div class="row">
            {!! Form::open(array('method' => 'post', 'action' => array('Admin\DashboardController@postChangePassword', $user->id, $flag))) !!}
                <div class="col-lg-8">
                    <div class="col-lg-8">
                        <div class="form-group">
                            {!! Form::label('password', 'Hasło',  array('class' => 'sr-only')) !!}
                            {!! Form::password('password', array('class' => 'form-control', 'placeholder' => 'Hasło', 'required' => 'true', 'autofocus' => 'true')) !!}
                        </div>
                    </div>
                    <div class="col-lg-4">
                        {!! Form::submit('Zapisz', array('class' => 'btn btn-success ')) !!}
                    </div>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
    <!-- /#page-wrapper -->
@stop