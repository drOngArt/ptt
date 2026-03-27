<div class="wall-schedule">
  <div class="table-responsive">
    <table class="table table-striped mb-0">
      <tbody>
       <?php 
        $firstDanceShown = false;
        if( !empty($compressedProgram) && count($compressedProgram) ) 
          $maxDances = max(array_map(fn($r) => count($r->dances), $compressedProgram)); 
        else
          $maxDances = 3;
      ?>
        @foreach($compressedProgram as $index => $programRound)
          @php
            $desc = $programRound->alternative_description != ""
              ? $programRound->alternative_description
              : $programRound->description;
          @endphp

          @if($programRound->isDance)
            <tr>
              <td class="wall-schedule-desc">
                <span class="lp-ball">
                  <span>{{ $programRound->noPrg }}</span>
                </span>
                <span class="desc-text">
                  {{ $programRound->alternative_description ?: $programRound->description }}
                </span>
              </td>
              @foreach($programRound->dances as $dance)
                <td class="wall-schedule-dance">
                  @php
                    $isCurrent = (!$firstDanceShown);
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
              @for($i = count($programRound->dances); $i < $maxDances; $i++)
                <td class="p-1">&nbsp;</td>
              @endfor
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
