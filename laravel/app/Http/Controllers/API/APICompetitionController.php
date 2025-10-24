<?php

namespace App\Http\Controllers\API;

use App;
use App\Http\Controllers\API\Transformers\AdjudicatorTransformer;
use App\Http\Controllers\Competition;
use App\Http\Controllers\Controller;
use App\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Serializer\ArraySerializer;

class APICompetitionController extends Controller
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

    public function getCompetition()
    {
        $data = [
            'eventName' => $this->tournamentHelper->getName(),
            'eventId'   => $this->tournamentHelper->getEventId(),
        ];

        return \Response::json($data);
    }

    public function getAdjudicators()
    {
        //old
        //$judgeRole = Role::where('name', 'judge')->first();
        //$judges = Role::find($judgeRole->id)->users()->get()->sortBy('lastName');
        //$judgesResponse = $this->response->collection($judges, new AdjudicatorTransformer(), 'adjudicators');
        //return Response::json($judgesResponse['adjudicators']);

        $judgeRole = \App\Role::where('name', 'judge')->first();
        $judges = \App\Role::find($judgeRole->id)->users()->get()->sortBy('lastName');

        $fractal = new Manager();
        $fractal->setSerializer(new ArraySerializer());

        $resource = new FractalCollection($judges, new AdjudicatorTransformer());
        $payload = $fractal->createData($resource)->toArray();
        $data = $payload['data']; //remove 'data' element
        return \Response::json($data);
    }
}
