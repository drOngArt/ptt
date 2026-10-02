<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\API\Transformers\RoundTransformer;
use App\Http\Controllers\Competition;
use App\Http\Controllers\Controller;
use App\Layout;
use App\Round;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\ArraySerializer;
use Illuminate\Support\Facades\Log;

class APIJudgeController extends Controller
{
    private $tournamentHelper;
    private $pttLogFile;

    private function loadTournamentData(): void
    {
        $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));
    }

    public function __construct()
    {
        $this->loadTournamentData();

        $this->pttLogFile = new Logger('PTTLog');
        $logFilename = App::storagePath().'/logs/pttLog-'.Carbon::now()->toDateString().'.log';
        $this->pttLogFile->pushHandler(new StreamHandler($logFilename), Logger::INFO);
    }

    public function getDances()
    {
        $dances = Round::all();
        $layoutData = Layout::get();
        $modify_dances = [];
        $definedTime = null;
        $rounds = [];
        $description = null;
        $definedTimePrev = null;

        if (count($dances) > 0) {
            if ($dances[0]->closed == '1') {
                $definedTime = Carbon::now('Europe/Warsaw');
            } else {
                $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)
                    ->addMinutes((int)$layoutData[0]->parameter1);
            }
        } else {
            $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)
                ->addMinutes((int)$layoutData[0]->parameter1);
        }
        $orderNo = 1;

        foreach ($dances as $programRound) {
          $roundDescription = !empty(trim($programRound->alternative_description ?? ''))
            ? $programRound->alternative_description
            : $programRound->description;
    
          if ($programRound->closed == '1') {    
            if ($description === null) {    
              $rounds = Arr::add( $rounds, $definedTime->format('H:i'), $roundDescription );
      
              $description = $roundDescription;
              $programRound->description = $orderNo.'. '.$roundDescription;
              $modify_dances[] = $programRound;    
            } elseif ($description != $roundDescription) {    
              $orderNo++;
              if (! in_array($roundDescription, $rounds)) {
                $rounds = Arr::add( $rounds, $definedTime->format('H:i'), $roundDescription );
  
              $description = $roundDescription;
              $programRound->description = $orderNo.'. '.$roundDescription;
              $modify_dances[] = $programRound;
              } else {
                $key = array_search($roundDescription, $rounds, true);
                if ($key !== false) {
                  $description = $roundDescription;
                  $programRound->description = $orderNo.'. '.$roundDescription;
                  $modify_dances[] = $programRound;
                }
              }
            } else {
              $key = array_search($roundDescription, $rounds, true);
              if( $key !== false ) {
                $description = $roundDescription;
                $programRound->description = $orderNo.'. '.$roundDescription;
                $modify_dances[] = $programRound;
              } else {
                $programRound->description = $orderNo.'. '.$roundDescription;
                $modify_dances[] = $programRound;
              }
            }

          } else {
            if( $description === null ) {
              $rounds = Arr::add( $rounds, $definedTime->format('H:i'), $roundDescription );
              $description = $roundDescription;
              $programRound->description = $orderNo.'. '.'[ '.$definedTime->format('H:i').' ] - '.$roundDescription;
              $modify_dances[] = $programRound;    
            } 
            elseif( $description != $roundDescription ) {
              $orderNo++;
              if( !in_array($roundDescription, $rounds) ) {
                if( $programRound->groups > 0 ) {
                  $rounds = Arr::add( $rounds, $definedTime->format('H:i'), $roundDescription );
                  $definedTimePrev = $definedTime->format('H:i');
                }
                else {
                  $rounds[$definedTimePrev] = $roundDescription;
                }
                $description = $roundDescription;
                if( mb_strpos( mb_strtoupper($roundDescription, 'UTF-8'), 'PRZERWA' ) !== false || 
                  mb_strtoupper(mb_substr($roundDescription, 0, 5, 'UTF-8'), 'UTF-8') === 'POKAZ') {
                  $programRound->dance = ($programRound->dance ? (int)$programRound->dance : 10).' min ---------------';
                }
                if( $programRound->groups > 0 )
                  $programRound->description = $orderNo.'. '.'[ '.$definedTime->format('H:i').' ] - '.$roundDescription;
                else
                  $programRound->description = $orderNo.'. '.($definedTimePrev ? '[ '.$definedTimePrev.' ] - ':'').$roundDescription;
                $modify_dances[] = $programRound;
              } 
              else {
                $key = array_search($roundDescription, $rounds, true);
                if( $key !== false ) {
                  $description = $roundDescription;
                  $programRound->description = $orderNo.'. '.'[ '.$key.' ] - '.$roundDescription;
                  $modify_dances[] = $programRound;
                }
              }
            } 
            else {
              $key = array_search($roundDescription, $rounds, true);
              /*
              Log::info('SESSION DEBUG', [
                'round' => $roundDescription.'-'.$programRound->dance,
                'key' => $key,
              ]);
              */
              if( $key !== false && !empty($key )) {
                $description = $roundDescription;
                $programRound->description = $orderNo.'. '.'[ '.$key.' ] - '.$roundDescription;
                $modify_dances[] = $programRound;
                if( !$definedTimePrev )
                  $definedTimePrev = $key;
              } 
              else {
                $programRound->description = $orderNo.'. '.$roundDescription;
                $modify_dances[] = $programRound;
              }
            }
            if( $programRound->groups > 0 ) { //if 0 means that dnaces at the same time. np calculate new
              $counter = $programRound->groups;
              if( mb_strpos( mb_strtoupper($programRound->dance, 'UTF-8'), 'MIN --' ) !== false ) { //break or show
                $seconds = 60 * ( $programRound->dance ? (int)$programRound->dance : 10 );
                $definedTime = $definedTime->addSeconds($seconds);
              } 
              elseif( mb_strpos( mb_strtoupper($programRound->description, 'UTF-8'), 'WSTĘPNA' ) !== false ) {
                $seconds = (int)$layoutData[0]->durationFinal * (int)$counter;
                $definedTime = $definedTime->addSeconds($seconds);
              } 
              elseif( mb_strpos( mb_strtoupper($programRound->description, 'UTF-8'), 'OCEN' ) !== false ) {
                $seconds = (int)$layoutData[0]->durationFinal * (int)$counter;
                $definedTime = $definedTime->addSeconds($seconds);
              }
              elseif( ($pos = mb_strpos($programRound->description, ']', 0, 'UTF-8')) !== false &&
                      mb_strtoupper( mb_substr($programRound->description, $pos + 4, 3, 'UTF-8'), 'UTF-8' ) === 'FIN' ) {
                $seconds = (int)$layoutData[0]->durationFinal * (int)$counter;
                $definedTime = $definedTime->addSeconds($seconds);
              }
              else {
                $seconds = (int)$layoutData[0]->durationRound * (int)$counter;
                $definedTime = $definedTime->addSeconds($seconds);
              }
            }
          }
        }
      
        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer()); // bez wrappera "data"

        $resource = new FractalCollection($modify_dances, new RoundTransformer());
        $payload = $fractal->createData($resource)->toArray();

        $data = $payload['data']; //remove 'data' element
        return \Response::json($data);
    }

    private function getRequiredVotes($round, $groups)
    {
        $votesRequired = $round->votesRequired;
        if ($round->isFinal && count($groups->couples) > 0) {
            $votesRequired = count($groups->couples[0]);
        }
        return $votesRequired;
    }

    private function transformVotesReady($competition, $localRound, $round, $adjudicatorSign, $groups)
    {
        $votesRequired = $this->getRequiredVotes($round, $groups);
        return [
            'status'          => 1,
            'competition'     => $competition,
            'danceId'         => $round->roundId,
            'danceSignature'  => $localRound->dance,
            'votesRequired'   => $votesRequired,
            'adjudicatorSign' => $adjudicatorSign,
            'roundName'       => !empty(trim($localRound->alternative_description ?? '')) ? 
                                  $localRound->alternative_description : $localRound->description,
            'isFinal'         => (bool) $round->isFinal,
            'groups'          => $groups,
        ];
    }

    private function getCompetition()
    {
        return [
            'eventName' => $this->tournamentHelper->getName(),
            'eventId'   => $this->tournamentHelper->getEventId(),
        ];
    }

    private function checkVotes($votes, $round, $groups)
    {
        $votesRequired = $this->getRequiredVotes($round, $groups);
        $votesCount = 0;
        foreach ($votes as $vote) {
            if ($vote->note == 'X' || is_numeric($vote->note)) {
                $votesCount++;
            }
        }
        return $votesCount >= $votesRequired;
    }

    public function getVotes()
    {
        $adjudicator = Auth::user();
        $error = -1;
        $roundsToCheck = Round::where('isDance', '1')->where('closed', '0')->get()->sortBy('id');

        foreach ($roundsToCheck as $roundToCheck) {
            $round = $this->tournamentHelper->getRoundWithType($roundToCheck->description, $roundToCheck->type);
            $danceSign = $roundToCheck->dance;

            if ($round != false) {
                $judgeSign = $this->tournamentHelper->getJudgeSign($adjudicator->firstName, $adjudicator->lastName, $adjudicator->plId, $round->roundId);
                if (! $judgeSign) {
                    continue;
                }
                $votes  = $this->tournamentHelper->getVotes($round->roundId, $judgeSign, $danceSign);
                $groups = $this->tournamentHelper->getDanceCouples($round->roundId, $danceSign, $error);
                if ($error == 0) {
                    \Log::debug('error !!! brak tanca "'.$danceSign.'" w rundzie '.$roundToCheck->description);
                }

                $requiredVotesMet = $this->checkVotes($votes, $round, $groups);
                $competition = $this->getCompetition();

                if (! $votes || ! $requiredVotesMet) {
                    return Response::json($this->transformVotesReady($competition, $roundToCheck, $round, $judgeSign, $groups));
                }
            } else {
                return Response::json(['status' => 2]);
            }
        }
        return Response::json(['status' => 0]);
    }

    public function postVotes($danceId)
    {
        $data = request()->isJson() ? request()->json()->all() : request()->all();

        if (empty($data)) {
          \Log::warning('postVotes empty data', [
          'ct' => request()->header('content-type'),
          'raw' => request()->getContent(),
          ]);
        }

        $danceSignature  = $data['danceSignature'] ?? null;
        $adjudicatorSign = $data['adjudicatorSign'] ?? null;
        $votes           = $data['votes'] ?? [];
        if (isset($data['danceId']) && (int)$data['danceId'] !== (int)$danceId) {
          \Log::warning('danceId mismatch', ['url' => $danceId, 'body' => $data['danceId']]);
        }
        //$votesObj = json_decode(json_encode($votes), false); // map -> stdClass
        $DBResult = $this->tournamentHelper->setVotes((int) $danceId, $danceSignature, $adjudicatorSign, $votes);

        return $DBResult
            ? Response::json(['error' => 'false'], 200)
            : Response::json(['error' => 'true'], 401);
    }

    public function postStatus()
    {
        $data = request()->json()->all();
        $adjudicator = Auth::user();

        $key = 'Status '.$adjudicator->firstName.' '.$adjudicator->lastName.','.$adjudicator->judgeId;
        $status = ['time' => time()];
        foreach ($data as $datakey => $name) {
            $status[$datakey] = $name;
        }
        Cache::put($key, $status, 600); // 10 minutes

        return Response::json(['error' => 'false'], 200);
    }
}
