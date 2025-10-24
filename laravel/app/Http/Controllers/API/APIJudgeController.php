<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\API\Transformers\RoundTransformer;
use App\Http\Controllers\Competition;
use App\Http\Controllers\Controller;
use App\Layout;
use App\Round;
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

    // usuwamy wstrzykiwanie Larasponse
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
                    ->addMinutes($layoutData[0]->parameter1);
            }
        } else {
            $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)
                ->addMinutes($layoutData[0]->parameter1);
        }

        foreach ($dances as $programRound) {
            $programRound->isFinal = false;
            if ($programRound->description[0] == 'F' || $programRound->description[0] == 'P') {
                $programRound->isFinal = true;
            }

            if ($programRound->closed == '1') {
                $modify_dances[] = $programRound;
            } else {
                if ($description === null) {
                    $rounds = array_add($rounds, $definedTime->format('H:i'), $programRound->description);
                    $description = $programRound->description;
                    $programRound->description = '[ '.$definedTime->format('H:i').' ] - '.$programRound->description;
                    $modify_dances[] = $programRound;
                } elseif ($description != $programRound->description) {
                    if (! in_array($programRound->description, $rounds)) {
                        $rounds = array_add($rounds, $definedTime->format('H:i'), $programRound->description);
                        $description = $programRound->description;
                        $programRound->description = '[ '.$definedTime->format('H:i').' ] - '.$programRound->description;
                        $modify_dances[] = $programRound;
                    } else {
                        $key = array_search($programRound->description, $rounds, true);
                        if ($key !== false) {
                            $description = $programRound->description;
                            $programRound->description = '[ '.$key.' ] - '.$programRound->description;
                            $modify_dances[] = $programRound;
                        }
                    }
                } else {
                    $key = array_search($programRound->description, $rounds, true);
                    if ($key !== false) {
                        $description = $programRound->description;
                        $programRound->description = '[ '.$key.' ] - '.$programRound->description;
                        $modify_dances[] = $programRound;
                    } else {
                        $modify_dances[] = $programRound;
                    }
                }

                $counter = $programRound->groups > 0 ? $programRound->groups : 1;
                if ($programRound->isFinal) {
                    $definedTime = $definedTime->addSeconds($layoutData[0]->durationFinal * $counter);
                } else {
                    $definedTime = $definedTime->addSeconds($layoutData[0]->durationRound * $counter);
                }
            }
        }

        usort($modify_dances, function ($a, $b) {
            if ($a->description[2] == '0' && $b->description[2] == '2') {
                return true;
            } elseif ($a->description[2] == '2' && $b->description[2] == '0') {
                return false;
            } elseif ($a->description[0] == '[' && $b->description[0] != '[') {
                return true;
            } elseif ($a->description[0] != '[' && $b->description[0] == '[') {
                return false;
            } elseif ($a->description[0] != '[' && $b->description[0] != '[') {
                return $a->id > $b->id;
            } elseif ($a->description == $b->description) {
                return $a->id > $b->id;
            } else {
                return $a->description > $b->description;
            }
        });

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
        $data = request()->json(); // zamiast \Input::json()

        $danceSignature  = $data->get('danceSignature');
        $adjudicatorSign = $data->get('adjudicatorSign');
        $votes           = $data->get('votes');

        \Log::debug($votes);

        $DBResult = $this->tournamentHelper->setVotes((int) $danceId, $danceSignature, $adjudicatorSign, $votes);

        return $DBResult
            ? Response::json(['error' => 'false'], 200)
            : Response::json(['error' => 'true'], 401);
    }

    public function postStatus()
    {
        $data = request()->json(); // zamiast \Input::json()
        $adjudicator = Auth::user();

        $key = 'Status '.$adjudicator->firstName.' '.$adjudicator->lastName.','.$adjudicator->judgeId;
        $status = ['time' => time()];
        foreach ($data as $datakey => $name) {
            $status[$datakey] = $name;
        }
        Cache::put($key, $status, 20); // minutes

        return Response::json(['error' => 'false'], 200);
    }
}
