<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\transformers\AdjudicatorTransformer;
use App\Http\Controllers\Competition;
use App\Http\Controllers\CompetitionPTT;
use App\Http\Controllers\CompetitionW;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\ArraySerializer;

class APICompetitionController extends Controller
{
    /** @var CompetitionPTT|CompetitionW */
    private $tournamentHelper;

    /** @var Logger */
    private $pttLogFile;

    private function loadTournamentData(): void
    {
        $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));
    }

    public function __construct()
    {
        $this->loadTournamentData();

        $this->pttLogFile = new Logger('PTTLog');

        $logFilename = storage_path('logs/pttLog-'.Carbon::now()->toDateString().'.log');

        $this->pttLogFile->pushHandler( new StreamHandler($logFilename, Logger::INFO) );
    }

    public function getCompetition(): JsonResponse
    {
        $data = [
            'eventName' => $this->tournamentHelper->getName(),
            'eventId'   => $this->tournamentHelper->getEventId(),
        ];

        return response()->json($data);
    }

    public function getAdjudicators(): JsonResponse
    {
        $judgeRole = Role::where('name', 'judge')->firstOrFail();
        $judges = $judgeRole->users()->get()->sortBy('lastName');

        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer());

        $resource = new FractalCollection($judges, new AdjudicatorTransformer());
        $payload = $fractal->createData($resource)->toArray();
        $data = $payload['data']; //remove 'data' element
        return response()->json($data);
    }
}
