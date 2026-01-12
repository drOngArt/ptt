<div class="wall-schedule">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <tbody>
       <?php $firstDanceShown = false; ?>

        @foreach($compressedProgram as $index => $programRound)

          @php
            $desc = $programRound->alternative_description != ""
              ? $programRound->alternative_description
              : $programRound->description;
          @endphp

          @if($programRound->isDance)
            <tr>
              {{-- OPIS – zawijany --}}
              <td class="wall-schedule-desc">
                {{ $programRound->alternative_description ?: $programRound->description }}
              </td>
            
              {{-- TAŃCE – jedna linia --}}
              @foreach($programRound->dances as $dance)
                <td class="wall-schedule-dance">
                  @php
                    $isCurrent = (!$firstDanceShown); // tylko raz
                  @endphp
              
                  @if($isCurrent)
                    <button class="btn-deep-orange badge" type="button">
                      {{ $dance['dance'] }}
                      @if(!empty($dance['order']))
                        <span class="badge badge-pill">{{ $dance['order'] }}</span>
                      @endif
                      <span class="fa fa-spinner fa-pulse ms-1"></span>
                    </button>
                    @php $firstDanceShown = true; @endphp
                  @else
                    {{-- reszta już bez spinnera --}}
                    @if(!empty($dance['order']))
                      <button class="btn btn-indigo badge" type="button">
                        {{ $dance['dance'] }}
                        <span class="badge badge-pill">{{ $dance['order'] }}</span>
                      </button>
                    @else
                      <span class="class_next">{{ $dance['dance'] }}</span>
                    @endif
                  @endif
                </td>
              @endforeach
            </tr>
          @else
            <tr>
              <td class="text-muted">
                {{ $desc }}
              </td>
            </tr>
          @endif

        @endforeach
      </tbody>
    </table>
  </div>
</div>
