@extends('admin.master')

@section('title')
    Papiery dla stażysty
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

    {{ html()->form('GET', url('admin/report'))->open() }}
    <div class="row">
        <div class="col-lg-12">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="page-header mb-0">Stażysta</h1>
                {{ html()
                    ->submit('Powrót')
                    ->id('submitButton1')
                    ->class('btn btn-primary button-menu') }}
            </div>
        </div>
    </div>

    {{-- Przycisk Drukuj --}}
    <div class="row mb-3">
        <div class="col-lg-12">
            <div class="d-flex justify-content-end">
              <button type="button"
                  class="btn btn-brown button-menu btn-icon-left my-2"
                  onclick="window.print()">
              <i class="fa fa-print"></i>
              <span class="button-menu-sep"></span>
              <span>Drukuj</span>
              </button>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">

            @php $pos = 0; $name = ''; @endphp

            @foreach($rounds as $round)
                @if($round)
                    <div style="clear: both;"></div>

                    <div class="table-responsive mb-4">
                        <table class="trainee table table-cont table-striped table-bordered" style="table-layout: fixed; width: auto;">
                            <tbody>
                                @if(isset($couples[$pos]) && $couples[$pos])
                                    @if(strpos($name, $round->roundName.$round->categoryName.$round->className.$round->styleName) === false)
                                        <tr>
                                            <th class="row-name text-center" width="80">
                                                @if(!$round->isFinal)
                                                    {{ $couplesNo[$pos] }} =&gt; {{ $round->votesRequired }}
                                                @endif
                                            </th>
                                            <th class="row-name" colspan="20">
                                                {{ $round->roundName }} {{ $round->categoryName }} {{ $round->className }} {{ $round->styleName }}
                                            </th>
                                        </tr>
                                        @php
                                            $name = $round->roundName.$round->categoryName.$round->className.$round->styleName;
                                        @endphp
                                    @endif
                                    @foreach($couples[$pos] as $index => $group)
                                        <tr>
                                            <th class="row-name text-center" width="80">
                                                @if(count($couples[$pos]) > 1)
                                                    {{ $danceNames[$pos] }} - {{ $index + 1 }}
                                                @else
                                                    {{ $danceNames[$pos] }}
                                                @endif
                                            </th>

                                            @foreach($group as $couple)
                                                <td class="row-number" width="45">
                                                    <div class="text-center">{{ $couple->number }}</div>
                                                </td>
                                            @endforeach

                                            @if((7 - count($group)) > 0)
                                                @for($i = 0; $i < (7 - count($group)); $i++)
                                                    <td class="row-number" width="45"></td>
                                                @endfor
                                            @endif
                                        </tr>

                                        {{-- Wiersz na X / Miejsce --}}
                                        <tr>
                                            <th width="80">
                                                <div class="text-center">
                                                    @if(!$round->isFinal)
                                                        X =&gt;
                                                    @else
                                                        Miejsce
                                                    @endif
                                                </div>
                                            </th>

                                            @foreach($group as $couple)
                                                <td></td>
                                            @endforeach

                                            @if((7 - count($group)) > 0)
                                                @for($i = 0; $i < (7 - count($group)); $i++)
                                                    <td class="row-number" width="45"></td>
                                                @endfor
                                            @endif
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                @endif
                @php $pos++; @endphp
            @endforeach
        </div>
    </div>
    {{ html()->form()->close() }}
</div>
@stop

@section('customScripts')
<script>
</script>
@stop
