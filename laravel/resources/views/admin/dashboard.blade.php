@extends('admin.master')

@section('title')
    Admin Panel
@stop

@section('content')
   <div id="page-wrapper">
      <div class="row">
        <div class="page-header-break">LISTA SĘDZIÓW</div>
        <div class="col-lg-12">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="page-header text-start">Sędziowie</h1>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-light-blue button-menu p-0"
                  data-bs-toggle="modal"
                  data-bs-target=".passwordAllModal">
                  Hasła automatyczne
                </button>
                <button type="button" class="btn btn-deep-orange button-menu d-flex align-items-center gap-2"
                  onclick="window.location.href='admin/panel'">
                  <span>Panel sędziowski</span>
                </button>
            </div>
          </div>
          <div class="d-flex justify-content-between align-items-center mb-2">
            @if($filterInProgram)
              <div class="form-check form-check-inline align-items-center me-auto">
                <input class="form-check-input custom-checkbox mx-2 ps-0" type="checkbox" id="presentFilter">
                  <label class="form-check-label ms-2 px-2" for="presentFilter">Pokaż wszystkich</label>
              </div>
            @endif
            @if(count($judges))
              <div class="d-flex justify-content-end">
                <button type="button"
                        class="btn btn-brown button-menu btn-icon-left my-2"
                        onclick="window.print()">
                    <i class="fa fa-print"></i>
                    <span class="button-menu-sep"></span>
                    <span>Drukuj</span>
                </button>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="table-responsive w-50 mx-3">
        <table class="table ekran">
            <tbody id="judgesTable">
               @foreach($judges as $judge)
                  <tr @if($judge->isInProgram || !$filterInProgram) class="present" @else class="d-none" @endif >
                     <td>{{$judge->lastName}} {{$judge->firstName}}</td>
                     <td><a href="{{$baseURI}}/admin/password/{{$judge->id}}/true" class="btn btn-primary" role="button">Ustaw hasło</a></td>
                     <td>
                        @if($judge->status)
                           <i class="fa fa-mobile fa-lg"></i>
                           ver: {{$judge->softwareVersion}}
                           @if($judge->batteryLevel < 20)
                              <i class="fa fa-battery-empty fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 40)
                              <i class="fa fa-battery-quarter fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 60)
                              <i class="fa fa-battery-half fa-rotate-270"></i> 
                           @elseif($judge->batteryLevel < 80)
                              <i class="fa fa-battery-three-quarters fa-rotate-270"></i> 
                           @else
                              <i class="fa fa-battery-full fa-rotate-270"></i>
                           @endif 
                            {{$judge->batteryLevel}}%
                        @endif
                     </td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
      <!-- for print only -->
      <div class="table-responsive">
         <table class="table table-striped drukarka">
            <thead>
               <tr class="present">
                  <th>Lp.</th>
                  <th>Imię i Nazwisko</th>
                  <th>Miasto</th>
                  <th>Kraj</th>
               </tr>
            </thead>
            <tbody id="judgesTablePrint">
               <tr class="present">
                  <td class="font-14pt">Główny</td>
                  @if( $mainJudge )
                     <td class="font-14pt">{{$mainJudge->firstName}}&nbsp{{$mainJudge->lastName}}</td>
                     <td class="font-14pt">{{$mainJudge->city}}</td>
                     <td class="font-14pt">{{$mainJudge->country}}</td>
                  @else
                     <td></td>
                     <td></td>
                     <td></td>
                  @endif
               </tr>
               <?php $idx = 1; ?>
               @foreach($judgestoPrint as $judge)
                  <tr @if($judge->isInProgram || !$filterInProgram ) class="present" @endif>
                     <td class="btn-circle">{{$idx}}.</td>
                     @if($judge->isInProgram || !$filterInProgram )
                        <?php $idx++; ?>
                     @endif
                     <td class="font-14pt">{{$judge->firstName}}&nbsp{{$judge->lastName}}</td>
                     <td class="font-14pt">{{$judge->city}}</td>
                     <td class="font-14pt">{{$judge->country}}</td>
                  </tr>
               @endforeach
                  <tr class="present">
                     <td></td>
                     <td colspan=5><h2>SKRUTINERZY</h2></td>
                  </tr>
               <?php $idx = 1; ?>
               @foreach($scrutineers as $judge)
                  <tr class="present">
                     <td class="btn-circle">{{$idx}}.</td>
                     <?php $idx++; ?>
                     <td class="font-14pt">{{$judge->firstName}}&nbsp{{$judge->lastName}}</td>
                     <td class="font-14pt">{{$judge->city}}</td>
                     <td class="font-14pt">{{$judge->country}}</td>
                  </tr>
               @endforeach
            </tbody>
         </table>
      </div>
    </div>
    <!-- /#page-wrapper -->
    <!-- Save parameters selector -->
    <div class="modal fade passwordAllModal" tabindex="-1" aria-labelledby="passwordAllTitle" aria-hidden="true" 
         data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title fst-italic" id="passwordAllTitle">
              Wprowadź wspólną część hasła, która wystąpi po inicjałach każdego sędziego<br>
              (pierwsze imię, drugie nazwisko):
            </h5>
          </div>
  
          {{ html()->form('GET', action('Admin\DashboardController@savePasswordAll'))->open() }}
          <div class="modal-body">
            <div class="mb-3 ekran">
              <div class="form-text mb-2">Przykład: Mieczysław Kowalski =&gt; 
                <code class="fs-5 fw-semibold text-primary bg-light px-3 py-2 rounded">mk</code>
              </div>
              <div class="d-flex align-items-center gap-2">
              {{ html()->password('myPass')
                  ->placeholder('hasło')
                  ->maxlength(10)
                  ->class('form-control width_100px text-start') }}
                <span class="text-muted small">(zawsze małe litery)</span>
              </div>
              <div class="form-text mt-2">Końcowe hasło: "mk**" gdzie ** to wprowadzony ciąg znaków</div>
            </div>
          </div>
  
        <div class="modal-footer d-flex justify-content-between align-items-center">
          {{ html()->button('Anuluj')
            ->type('button')
            ->class('btn btn-lg btn-warning button-menu')
            ->attribute('data-bs-dismiss', 'modal') }}
  
          {{ html()->submit('Zapisz')
            ->class('btn btn-lg btn-success button-menu') }}
        </div>
  
        {{ html()->form()->close() }}

        </div>
      </div>
    </div>

@stop

@section('customScripts')
  <script>
    $(function() {
      function restripeTable($table) {
        const $rows = $table.find('> tr:visible');
        console.log('visible in ', $table.attr('id'), '- rows: ',$rows.length);
        $rows.filter(':even').css({"background-color": "rgba(0, 0, 0, 0.03)"});
      };
      restripeTable($('#judgesTable'));
      $("#presentFilter").click(function(){
        var rows = $("#judgesTable").find("tr");
        var rowsp = $("#judgesTablePrint").find("tr");
        var checked = $("#presentFilter").prop('checked');

        if (!checked) {
           rows.each(function() {
              $(this).css({"background-color": "rgba(0, 0, 0, 0.0)"});
              if (!$(this).hasClass("present")) {
                 $(this).addClass('d-none');
              } else {
                  $(this).removeClass('d-none');
              }
           });
           var idx = 1;
           rowsp.each(function() {
              $(this).css({"background-color": "rgba(0, 0, 0, 0.0)"});
              if (!$(this).hasClass("present")) {
                 $(this).addClass('d-none');
              } else {
                 $(this).removeClass('d-none');
                 if( isNaN(parseInt($(this).find('td:first').text(),10)) == false ) {
                    $(this).find('td:first').text(idx);
                    $(this).find('td:first').append('.');
                    idx = idx+1;
                 }
                 else
                    idx = 1;
              }
           });
        } else {
           rows.each(function() {
              $(this).css({"background-color": "rgba(0, 0, 0, 0.0)"});
              $(this).removeClass('d-none')
           });
           var idx = 1;
           rowsp.each(function() {
              $(this).css({"background-color": "rgba(0, 0, 0, 0.0)"});
              $(this).removeClass('d-none');
              if( isNaN(parseInt($(this).find('td:first').text(),10)) == false ) {
                 $(this).find('td:first').text(idx);
                 $(this).find('td:first').append('.');
                 idx = idx+1;
              }
              else
                 idx = 1;
           });
        }
        restripeTable($('#judgesTable'));
        restripeTable($('#judgesTablePrint'));
    });
  });
  </script>
@stop