<?php

namespace app\Http\Controllers\API;

use App;
use app\Http\Controllers\API\transformers\AdjudicatorTransformer;
use App\Http\Controllers\Competition;
use App\Http\Controllers\Controller;
use App\Role;
use Cache;
use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Response;
use Sorskod\Larasponse\Larasponse;

class APICompetitionController extends Controller
{
    private $tournamentHelper;

    private $pttLogFile;

    protected $response;

    private function loadTournamentData()
    {
        $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));
    }

    public function __construct(Larasponse $response)
    {
        $this->response = $response;

        $this->loadTournamentData();

        $this->pttLogFile = new Logger('PTTLog');
        $logFilename = App::storagePath().'/logs/pttLog-';
        $logFilename .= Carbon::now()->toDateString();
        $logFilename .= '.log';
        $this->pttLogFile->pushHandler(new StreamHandler($logFilename), Logger::INFO);
    }

    public function getCompetition()
    {
        $data = [
            'eventName' => $this->tournamentHelper->getName(),
            'eventId' => $this->tournamentHelper->getEventId(),
        ];

        return Response::json($data);
    }

    public function getAdjudicators()
    {
        $judgeRole = Role::where('name', 'judge')->first();
        $judges = Role::find($judgeRole->id)->users()->get()->sortBy('lastName');
        $judgesResponse = $this->response->collection($judges, new AdjudicatorTransformer, 'adjudicators');

        return Response::json($judgesResponse['adjudicators']);
    }
}
