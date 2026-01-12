@extends('admin.master')

@section('title')
  Sędziowie
@stop

@section('content')
<div id="page-wrapper" class="container-fluid">

  {{ html()->form('POST', url('admin/postPanel'))->attribute('id','panelForm')->open() }}
  @csrf

  <div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center">
      <h1 class="h3 mb-0">Panel sędziowski</h1>

      <div class="d-flex gap-2">
        {{ html()->submit('Skład komisji')->name('zestaw')->class('btn btn-tangerine button-menu') }}
      </div>
    </div>
  </div>

  @if(session('status'))
    @if(session('status') == 'success')
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        Składy sędziowskie zostały zapisane do pliku.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
      </div>
    @else
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        Błąd dostępu do pliku. Składy nie zostały zapisane.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Zamknij"></button>
      </div>
    @endif
  @endif

  <div class="row">
    <div class="col-12">

      <table class="table table-striped table-bordered table-hover w-50 align-middle">
        <tbody id="baseRounds">
        @php $idx = null; @endphp

        @foreach($baseRounds as $round)
          @if($idx !== $round->positionW)
            @php $idx = $round->positionW; @endphp
            <tr class="table-secondary">
              <td class="text-center" style="width:150px;">
                <button id="select_{{ $round->positionW }}"
                        type="button"
                        class="btn btn-primary btn-sm">
                  Zaznacz
                </button>
              </td>
              <td class="fw-bold">
                BLOK {{ $round->positionW }}
              </td>
            </tr>
          @endif
          <tr>
            <td class="check-cell">
              <div class="check-wrapper">
                <!--{{ html()->checkbox($round->roundId)->class("form-check-input roundCheckbox_{$round->positionW} px-2")->id($round->roundId) }}-->
                <input class="form-check-input roundCheckbox_{{ $round->positionW }}"
                      name="selected[]"
                      type="checkbox"
                      value="{{ $round->roundId }}"
                      id="r{{ $round->roundId }}">
              </div>
            </td>
            <td>
              <!--{{ html()->hidden('roundId[]', $round->roundId) }}-->
              {{ $round->categoryName }} {{ $round->className }} <span class="text-primary font-italic"> {{ $round->styleName }}</span>
            </td>
          </tr>
        @endforeach

        </tbody>
      </table>
      <div class="d-flex justify-content-end ms-auto">
        {{ html()->submit('Skład komisji')->name('zestaw')->class('btn btn-tangerine button-menu') }}
      </div>

    </div>
  </div>

  {{ html()->form()->close() }}
</div>
@stop

@section('customScripts')
<script>
  $(function() {
    // nie ograniczaj do [0..IX] – lepiej automatycznie po przyciskach
    $('[id^="select_"]').each(function() {
      const btn = this;
      const suffix = this.id.replace('select_', '');
      const sel = '.roundCheckbox_' + suffix;

      $(btn).on('click', function() {
        const anyChecked = $(sel + ':checked').length > 0;
        const newState = !anyChecked;

        $(sel).prop('checked', newState);

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
