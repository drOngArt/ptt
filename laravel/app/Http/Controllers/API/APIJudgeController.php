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
            $programRound->isFinal = false;
            if ($programRound->description[0] == 'F' || $programRound->description[0] == 'P') {
                $programRound->isFinal = true;
            }

            if ($programRound->closed == '1') {
                if ($description === null) {
                    $rounds = Arr::add($rounds, $definedTime->format('H:i'), $programRound->description);
                    $description = $programRound->description;
                    $programRound->description = $orderNo.'. '.$programRound->description;
                    $modify_dances[] = $programRound;
                } elseif ($description != $programRound->description) {
                    $orderNo++;
                    if (! in_array($programRound->description, $rounds)) {
                        $rounds = Arr::add($rounds, $definedTime->format('H:i'), $programRound->description);
                        $description = $programRound->description;
                        $programRound->description = $orderNo.'. '.$programRound->description;
                        $modify_dances[] = $programRound;
                    } else {
                        $key = array_search($programRound->description, $rounds, true);
                        if ($key !== false) {
                            $description = $programRound->description;
                            $programRound->description = $orderNo.'. '.$programRound->description;
                            $modify_dances[] = $programRound;
                        }
                    }
                } else {
                    $key = array_search($programRound->description, $rounds, true);
                    if ($key !== false) {
                        $description = $programRound->description;
                        $programRound->description = $orderNo.'. '.$programRound->description;
                        $modify_dances[] = $programRound;
                    } else {
                        $programRound->description = $orderNo.'. '.$programRound->description;
                        $modify_dances[] = $programRound;
                    }
                }
            } else {
                if ($description === null) {
                    $rounds = Arr::add($rounds, $definedTime->format('H:i'), $programRound->description);
                    $description = $programRound->description;
                    $programRound->description = $orderNo.'. '.'[ '.$definedTime->format('H:i').' ] - '.$programRound->description;
                    $modify_dances[] = $programRound;
                } elseif ($description != $programRound->description) {
                    $orderNo++;
                    if (! in_array($programRound->description, $rounds)) {
                        $rounds = Arr::add($rounds, $definedTime->format('H:i'), $programRound->description);
                        $description = $programRound->description;
                        $programRound->description = $orderNo.'. '.'[ '.$definedTime->format('H:i').' ] - '.$programRound->description;
                        if( mb_strpos(mb_strtoupper($programRound->description, 'UTF-8'), 'PRZERWA') !== false )
                          $programRound->dance = $programRound->dance.' min ---------------';
                        $modify_dances[] = $programRound;
                    } else {
                        $key = array_search($programRound->description, $rounds, true);
                        if ($key !== false) {
                            $description = $programRound->description;
                            $programRound->description = $orderNo.'. '.'[ '.$key.' ] - '.$programRound->description;
                            $modify_dances[] = $programRound;
                        }
                    }
                } else {
                    $key = array_search($programRound->description, $rounds, true);
                    if ($key !== false) {
                        $description = $programRound->description;
                        $programRound->description = $orderNo.'. '.'[ '.$key.' ] - '.$programRound->description;
                        $modify_dances[] = $programRound;
                    } else {
                        $programRound->description = $orderNo.'. '.$programRound->description;
                        $modify_dances[] = $programRound;
                    }
                }

                $counter = $programRound->groups > 0 ? $programRound->groups : 1;
                if( mb_strpos(mb_strtoupper($programRound->description, 'UTF-8'), 'PRZERWA') !== false ){
                  $seconds = 60 * ($programRound->dance ? (int)$programRound->dance : 5);
                  $definedTime = $definedTime->addSeconds($seconds);
                }
                else if ($programRound->isFinal) {
                  $seconds = (int)$layoutData[0]->durationFinal * (int)$counter;
                  $definedTime = $definedTime->addSeconds($seconds);
                } else {
                  $seconds = (int)$layoutData[0]->durationRound * (int)$counter;
                  $definedTime = $definedTime->addSeconds($seconds);
                }
            }
        }

        /*usort($modify_dances, function ($a, $b) {
            if ($a->description[2] == '0' && $b->description[2] == '2') return -1;
            if ($a->description[2] == '2' && $b->description[2] == '0') return 1;
            if ($a->description[0] == '[' && $b->description[0] != '[') return -1;
            if ($a->description[0] != '[' && $b->description[0] == '[') return 1;
            // zamiast $a->id > $b->id
            if ($a->description[0] != '[' && $b->description[0] != '[') return $a->id <=> $b->id;
            if ($a->description == $b->description) return $a->id <=> $b->id;
            return $a->description <=> $b->description;
        });*/

        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer()); // bez wrappera "data"

        $resource = new FractalCollection($modify_dances, new RoundTransformer());
        $payload = $fractal->createData($resource)->toArray();
        //dd($data);
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
            'roundName'       => $localRound->description,
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
