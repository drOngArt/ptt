@extends('admin.master')

@section('title')
    Program Turnieju
@stop

@section('content')
    <div id="page-wrapper">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="page-header">Program turnieju
                    <div class="btn-group pull-right">
                        <a href="program/editProgram">
                            <button class="btn btn-primary">Edytuj</button>
                        </a>
                    </div>
                </h1>
            </div>
        </div>
        <!-- /.row -->
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>
                                Nazwa
                            </th>
                            <th>
                                Taniec
                            </th>
                            <th>
                                Zakończony
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $lastDescription = null; ?>
                        @foreach($program as $programRound)
                            @if($programRound->isDance)
                                 <tr>
                                    <td>
                                        @if($lastDescription == null || $lastDescription != $programRound->description)
                                            {{$programRound->description}}
                                        @endif
                                            <?php $lastDescription = $programRound->description ?>
                                    </td>                                    
                                    <td>
                                        @if($programRound->closed)
                                            <input type="checkbox" disabled checked>
                                        @else
                                            <input type="checkbox" disabled>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{$programRound->dance}}</strong>
                                    </td>
                                </tr>
                            @else
                                <?php $lastDescription = null; ?>
                                <tr>
                                    <td class="text-muted">
                                        {{$programRound->description}}
                                    </td>
                                    <td></td>
                                    <td>

                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /#page-wrapper -->
@stop