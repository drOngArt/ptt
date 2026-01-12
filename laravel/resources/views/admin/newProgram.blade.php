@extends('admin.master')

@section('title')
    Utwórz nowy program
@stop

@section('content')
    <div id="page-wrapper" class="container-fluid">


        {{ html()->form('GET', action('Admin\DashboardController@selectedCategories'))->open() }}
        <div class="row mb-3">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Wybór kategorii</h1>

                {{ html()->submit('Zatwierdź')
                    ->id('submitButtonTop')
                    ->class('btn btn-primary button-menu') }}
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover align-middle mb-3">
                        <tbody>
                        @php $idx = null; @endphp

                        @foreach($baseRounds as $round)
                            @if($round->isClosed == 0)

                                @if($idx !== $round->positionW)
                                    @php $idx = $round->positionW; @endphp
                                    <tr class="table-secondary">
                                        <td class="text-center" style="width: 130px;">
                                            <button id="select_{{ $round->positionW }}"
                                                    type="button"
                                                    class="btn btn-primary btn-sm">
                                                Zaznacz
                                            </button>
                                        </td>
                                        <td class="fw-semibold">
                                            BLOK {{ $round->positionW }}
                                        </td>
                                    </tr>
                                @endif

                                <tr>
                                  <td class="check-cell">
                                    <div class="check-wrapper">
                                        <input
                                            class="form-check-input roundCheckbox_{{ $round->positionW }}"
                                            name="selected[]"
                                            type="checkbox"
                                            value="{{ $round->roundId }}"
                                        >
                                    </div>
                                  </td>

                                  <td>
                                    <span class="description">
                                          {{ $round->roundName }}
                                          {{ $round->categoryName }}
                                          {{ $round->className }}
                                          {{ $round->styleName }}
                                    </span>
                                    @if($round->isAdditional)
                                        &nbsp;{{ $round->matchType }}
                                    @endif
                                    <span class="text-primary font-italic"> &nbsp; (
                                    @foreach($round->dances as $dance)
                                        &nbsp;{{ $dance }}
                                    @endforeach
                                    ) </span>
                                  </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-end">
                {{ html()->submit('Zatwierdź')
                    ->id('submitButtonBottom')
                    ->class('btn btn-primary button-menu') }}
            </div>
        </div>
        {{ html()->form()->close() }}
    </div>
    <!-- /#page-wrapper -->
@stop

@section('customScripts')
  <script>
    $(function () {
      $('[id^="select_"]').each(function () {
        const btn = this;
        const suffix = this.id.replace('select_', ''); // np. "I", "II", "10" itd.
        const checkboxSelector = '.roundCheckbox_' + suffix;

        $(btn).on('click', function () {
          const anyChecked = $(checkboxSelector + ':checked').length > 0;
          const newState = !anyChecked;

          $(checkboxSelector).prop('checked', newState);

          if (newState) {
              btn.innerText = 'Odznacz';
              $(btn).removeClass('btn-primary').addClass('btn-info');
          } else {
              btn.innerText = 'Zaznacz';
              $(btn).removeClass('btn-info').addClass('btn-primary');
          }
        });
      });
    });
  </script>
@stop
