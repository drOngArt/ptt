@extends('admin.master')

@section('title')
    Sędziowie
@stop

@section('content')
<div id="page-wrapper">
   {{ html()->form('POST', url('admin/postPanel'))->open() }}
   <div class="row">
      <div class="col-lg-12">
         <h1 class="page-header">
            Panel sędziowski
            <div class="pull-right">
               <div class="container-fluid">
                  <ul class="nav navbar-nav">
                     <li>{{ html()->submit('Skład komisji')->name('zestaw')->class('btn btn-tangerine button-menu') }}</li>
                  </ul>
               </div>
            </div>
         </h1>
      </div>
      <!-- /.col-lg-12 -->
   </div>

   @if(session('status'))
        @if(session('status') == 'success')
            <div class="alert alert-success">
                Składy sędziowskie zostały zapisane do pliku.
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>
        @else
            <div class="alert alert-danger">
                Błąd dostępu do pliku. Składy nie zostały zapisane.
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            </div>
        @endif
    @endif

    <div class="row">
        <div class="col-lg-12">
            <table class="table table-striped table-bordered table-hover w-75">
                <tbody id="baseRounds">
                <?php $idx = 10; ?>
                @foreach($baseRounds as $index => $round)
                    <tr>
                        @if($idx != $round->positionW)
                            <td class="text-center">
                                <button id="select_{{ $round->positionW }}" type="button" class="btn btn-primary btn-xs">&nbsp;Zaznacz&nbsp;</button>
                            </td>
                            <?php $idx = $round->positionW; ?>
                            <td class="text-center">
                                &nbsp;BLOK&nbsp;{{ $round->positionW }}
                            </td>
                    </tr>
                    <tr>
                        <td class="text-center">
                            {{ html()->checkbox($round->roundId)->class("roundCheckbox_{$round->positionW} px-2")->id($round->roundId) }}
                        </td>
                        <td>
                            {{ html()->hidden('roundId[]', $round->roundId) }}
                            {{ $round->categoryName }} {{ $round->className }} {{ $round->styleName }}
                        </td>
                    @else
                        <td class="text-center">
                            {{ html()->checkbox($round->roundId)->class("roundCheckbox_{$round->positionW} px-2")->id($round->roundId) }}
                        </td>
                        <td>
                            {{ html()->hidden('roundId[]', $round->roundId) }}
                            {{ $round->categoryName }} {{ $round->className }} {{ $round->styleName }}
                        </td>
                    @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
            <div class="pull-right">
                <div class="container-fluid">
                    <ul class="nav navbar-nav">
                        <li>
                            {{ html()->submit('Skład komisji')->name('zestaw')->class('btn btn-tangerine button-menu') }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
  {{ html()->form()->close() }}
</div>
    <!-- /#page-wrapper -->
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
