@extends('admin.master')

@section('title')
    Raporty / Wyniki
@stop

@section('content')
   <div id="page-wrapper">
      {!! html()->form('POST', url('admin/postReport'))->open() !!}
         <div class="row">
           <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
             <h1 class="page-header mb-0">Raporty / Wyniki</h1>
             <div class="d-flex justify-content-end align-items-center gap-2">
               <ul class="nav navbar-nav d-flex flex-row gap-2 mb-0">
                 <li class="dropdown">
                   {!! html()->button('LISTY', 'button')
                       ->class('btn btn-tangerine button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- przydział numerów')->name('ranges')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- startowe')->name('lists')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- klubów')->name('clubs')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- klubów aktualnych')->name('clubsOpen')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- par o różnych klas.', '')->name('couplesBr')->class('btn-coral button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li class="dropdown">
                   {!! html()->button('RAPORTY', 'button')
                       ->class('btn btn-lsalmon button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- wykaz rund')->name('rounds')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- numery par')->name('couples')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- stażysta')->name('trainee')->class('btn-coral button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li class="dropdown">
                   {!! html()->button('WYNIKI', 'button')
                       ->class('btn btn-coral button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- imienne')->name('results_f')->class('btn-coral button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- skrócone')->name('results_s')->class('btn-coral button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li>
                   {!! html()->submit('IMPREZA')->name('impreza')->class('btn btn-dorange button-menu') !!}
                 </li>
               </ul>
             </div>
           </div>
          </div>

         @if($eventId)
            <div class="alert alert-danger alert-dismissible">
               <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
               <h3><strong>UWAGA !!</strong> {{ $eventId }}</h3>
            </div>
         @endif

         @if($listyCSV)
            <div class="alert alert-warning alert-dismissible">
               <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
               <h3><strong>UWAGA !!</strong> {{ $listyCSV }}</h3>
            </div>
         @endif

         @if(session('status'))
            @if(session('status') === 'success')
               <div class="alert alert-success">
                  Wyniki zostały zapisane do pliku.
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
               </div>
            @else
               <div class="alert alert-danger">
                  Błąd dostępu do pliku. Wyniki nie zostały zapisane.
                  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
               </div>
            @endif
         @endif

         @if(Session::has('conflict'))
            <div class="alert alert-success">
               {{ Session::get('conflict') }}
               <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>
         @endif

         <div class="row">
            <div class="col-lg-12">
               <table class="table table-striped table-bordered table-hover w-75 p-2 mx-3">
                  <tbody id="baseRounds">
                     @php $idx = 10; @endphp
                     @foreach($baseRounds as $index => $round)
                        <tr>
                           @if($idx != $round->positionW)
                              @php $idx = $round->positionW; @endphp
                              <td class="text-center"  style="width: 15%">
                                 <button id="select_{{$round->positionW}}" type="button" class="btn btn-primary">&nbsp;Zaznacz&nbsp;</button>
                              </td>
                              <td class="text-center">&nbsp;BLOK&nbsp;{{$round->positionW}}</td>
                              <td class="text-center">PAR</td>
                           </tr>
                           <tr>
                              <td class="text-center">
                                 <input class="roundCheckbox_{{$round->positionW}}" type="checkbox" id="{{$round->roundId}}" name="{{$round->roundId}}">
                              </td>
                              <td>
                                 {!! html()->hidden('roundId[]', $round->roundId) !!}
                                 {{$round->categoryName}} {{$round->className}} {{$round->styleName}}
                              </td>
                              <td class="text-center">{{$round->baseNumberOfCouples}}</td>
                              @if($isManual[$index])
                                 <td class="text-center bg-body p-1" style="width: 20%">
                                    <a href="reportSet/{{$round->roundId}}" class="btn btn-dbisque" role="button">Ustal miejsca</a>
                                 </td>
                              @endif
                           @else
                              <tr>
                                 <td class="text-center" style="width: 15%">
                                    <input class="roundCheckbox_{{$round->positionW}}" type="checkbox" id="{{$round->roundId}}" name="{{$round->roundId}}">
                                 </td>
                                 <td>
                                    {!! html()->hidden('roundId[]', $round->roundId) !!}
                                    {{$round->categoryName}} {{$round->className}} {{$round->styleName}}
                                 </td>
                                 <td class="text-center">{{$round->baseNumberOfCouples}}</td>
                                 @if($isManual[$index])
                                    <td class="text-center bg-body p-1" style="width: 20%">
                                       <a href="reportSet/{{$round->roundId}}" class="btn btn-dbisque" role="button">Ustal miejsca</a>
                                    </td>
                                 @endif
                           @endif
                        </tr>
                     @endforeach
                  </tbody>
               </table>
            </div>
         </div>

         <div class="row">
           <div class="col-lg-12 d-flex justify-content-between align-items-center mb-3">
             <h1 class="page-header mb-0">Raporty / Wyniki</h1>
             <div class="d-flex justify-content-end align-items-center gap-2">
               <ul class="nav navbar-nav d-flex flex-row gap-2 mb-0">
                 <li class="dropdown">
                   {!! html()->button('LISTY', 'button')
                       ->class('btn btn-tangerine button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- przydział numerów')->name('ranges')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- startowe')->name('lists')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- klubów')->name('clubs')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- klubów aktualnych')->name('clubsOpen')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- par o różnych klas.', '')->name('couplesBr')->class('btn-lsalmon button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li class="dropdown">
                   {!! html()->button('RAPORTY', 'button')
                       ->class('btn btn-coral button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- wykaz rund')->name('rounds')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- numery par')->name('couples')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- stażysta')->name('trainee')->class('btn-lsalmon button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li class="dropdown">
                   {!! html()->button('WYNIKI', 'button')
                       ->class('btn btn-lsalmon button-menu dropdown-toggle')
                       ->attribute('data-bs-toggle','dropdown') !!}
                   <ul class="dropdown-menu">
                     <li>{!! html()->submit('- imienne')->name('results_f')->class('btn-lsalmon button-menu text-left') !!}</li>
                     <li>{!! html()->submit('- skrócone')->name('results_s')->class('btn-lsalmon button-menu text-left') !!}</li>
                   </ul>
                 </li>
                 <li>
                   {!! html()->submit('IMPREZA')->name('impreza')->class('btn btn-dorange button-menu') !!}
                 </li>
               </ul>
             </div>
           </div>
          </div>

      {!! html()->form()->close() !!}
   </div>
@stop

@section('customScripts')
   <script>
     $(function() {
       const buttons = [
         '0','I','II','III','IV','V','VI','VII','VIII','IX'
       ];

       buttons.forEach(idSuffix => {
         const btn = document.getElementById('select_' + idSuffix);
         if (btn) btn.classList.add('btn-primary');

         $('#' + 'select_' + idSuffix).on('click', function() {
           const checkboxClass = '.roundCheckbox_' + idSuffix;
           const isChecked     = $(checkboxClass + ':checked').length > 0;

           $(checkboxClass).prop('checked', !isChecked);

           if (isChecked) {
             btn.innerText = 'Zaznacz';
             $(btn).removeClass('btn-info').addClass('btn-primary');
           } else {
             btn.innerText = 'Odznacz';
             $(btn).removeClass('btn-primary').addClass('btn-info');
           }
         });
       });
     });
   </script>
@stop
