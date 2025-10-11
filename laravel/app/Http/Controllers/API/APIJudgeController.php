<?php namespace App\Http\Controllers\API;

use App;
use app\Http\Controllers\API\transformers\RoundTransformer;
use App\Http\Controllers\Competition;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Round;
use App\Layout;
use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sorskod\Larasponse\Larasponse;

class APIJudgeController extends Controller {

   private $tournamentHelper;
   private $pttLogFile;
   private $response;

   private function loadTournamentData(){
      $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));
   }

   public function __construct(Larasponse $response){
      $this->response = $response;

      $this->loadTournamentData();

      $this->pttLogFile = new Logger("PTTLog");
      $logFilename = App::storagePath().'/logs/pttLog-';
      $logFilename .= Carbon::now()->toDateString();
      $logFilename .= '.log';
      $this->pttLogFile->pushHandler(new StreamHandler($logFilename), Logger::INFO);
   }

   public function getDances(){
      $dances = Round::all();
      $layoutData =  Layout::get();
      $modify_dances = [];
      $definedTime = null;
      $rounds = [];
      $timeToWrite = null;

      if( count($dances) > 0 ) {
         if( $dances[0]->closed == '1' )//first dance closed => probably program started, use current time
            $definedTime = Carbon::now('Europe/Warsaw');
         else
            $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);
      }
      else
         $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);

      $description = null;
      foreach( $dances as $programRound ) {
         $programRound->isFinal = false;
         if( $programRound->description[0] == 'F' || $programRound->description[0] == 'P') //probably final(Fina³), show(Pokaz) or break(Przerwa)
            $programRound->isFinal = true;
         if( $programRound->closed == '1' )
            $modify_dances[] = $programRound;
         else{
            if( $description == null ){
               $rounds = array_add($rounds, $definedTime->Format('H:i'),$programRound->description);
               $description = $programRound->description;
               $programRound->description = '[ '.$definedTime->Format('H:i').' ] - '.$programRound->description;
               $modify_dances[] = $programRound;
            }
            else if( $description != $programRound->description ){
               if( !in_array($programRound->description, $rounds) ){
                  $rounds = array_add($rounds, $definedTime->Format('H:i'),$programRound->description);
                  $description = $programRound->description;
                  $programRound->description = '[ '.$definedTime->Format('H:i').' ] - '.$programRound->description;
                  $modify_dances[] = $programRound;
               }
               else {
                  $key = array_search( $programRound->description, $rounds, true );
                  if( $key != false ){
                     $description = $programRound->description;
                     $programRound->description = '[ '.$key.' ] - '.$programRound->description;
                     $modify_dances[] = $programRound;
                  }
               }
            }
            else
            {
               $key = array_search($programRound->description, $rounds, true);
               if( $key != false ){
                  $description = $programRound->description;
                  $programRound->description = '[ '.$key.' ] - '.$programRound->description;
                  $modify_dances[] = $programRound;
               }
               else
                  $modify_dances[] = $programRound;
            }
            $counter = $programRound->groups > 0 ? $programRound->groups:1;
            if( $programRound->isFinal )
               $definedTime = $definedTime->addSeconds($layoutData[0]->durationFinal * $counter);
            else
               $definedTime = $definedTime->addSeconds($layoutData[0]->durationRound * $counter);
         }
      }

      usort($modify_dances, function($a, $b) {
         if( $a->description[2] == '0' && $b->description[2] == '2')
            return( true );
         else if( $a->description[2] == '2' && $b->description[2] == '0')
            return( false );
         else if( $a->description[0] == '[' && $b->description[0] != '[' )
            return( true );
         else if( $a->description[0] != '[' && $b->description[0] == '[' )
            return( false );
         else if( $a->description[0] != '[' && $b->description[0] != '[' )
            return( $a->id > $b->id );
         else if( $a->description == $b->description )
            return( $a->id > $b->id );
         else
            return( $a->description > $b->description );
     });

     $data = $this->response->collection($modify_dances, new RoundTransformer());
     $data = $data['data'];
     return \Response::json($data);
   }

   private function getRequiredVotes($round, $groups) {
      $votesRequired = $round->votesRequired;
      if ($round->isFinal && count($groups->couples) > 0){
         $votesRequired = count($groups->couples[0]);
      }
      return $votesRequired;
   }

   private function transformVotesReady($competition, $localRound, $round, $adjudicatorSign, $groups){
      $votesRequired = $this->getRequiredVotes($round, $groups);
      $votes = [
         "status" => 1,
         "competition" => $competition,
         "danceId" => $round->roundId,
         "danceSignature" => $localRound->dance,
         "votesRequired" => $votesRequired,
         "adjudicatorSign" => $adjudicatorSign,
         "roundName" => $localRound->description,
         "isFinal" => (boolean) $round->isFinal,
         "groups" => $groups
      ];
      return $votes;
   }

   private function getCompetition(){
      $data = [
         'eventName' => $this->tournamentHelper->getName(),
         'eventId' => $this->tournamentHelper->getEventId()
      ];
      return $data;
   }


   private function checkVotes($votes, $round, $groups){
      $votesRequired = $this->getRequiredVotes($round, $groups);
      $votesCount = 0;
      foreach ($votes as $vote) {
         if ($vote->note == 'X' or is_numeric($vote->note)) {
            $votesCount++;
         }
      }
      if ($votesCount >= $votesRequired)
         return true;
      else
         return false;
   }

   public function getVotes(){
      $adjudicator = Auth::user();
      $error = -1;
      $roundsToCheck = Round::where('isDance', '=', '1')->where('closed', '=', '0')->get()->sortBy('id');
      foreach ($roundsToCheck as $roundToCheck) {
         $round = $this->tournamentHelper->getRoundWithType($roundToCheck->description, $roundToCheck->type);
         $danceSign = $roundToCheck->dance;
         if ($round != false){
            $judgeSign = $this->tournamentHelper->getJudgeSign($adjudicator->firstName, $adjudicator->lastName, $adjudicator->plId, $round->roundId);
            if(!$judgeSign){
               continue;
            }
            $votes = $this->tournamentHelper->getVotes($round->roundId, $judgeSign, $danceSign);
            $groups = $this->tournamentHelper->getDanceCouples($round->roundId, $danceSign, $error);
            if( $error == 0 ){// bad dance
              \Log::debug('error !!! brak tanca "'.$danceSign.'" w rundzie '.$roundToCheck->description);
            }
            $requiredVotes = $this->checkVotes($votes, $round, $groups);
            $competition = $this->getCompetition();
            if(!$votes || !$requiredVotes){
               return \Response::json($this->transformVotesReady($competition, $roundToCheck, $round, $judgeSign, $groups));
            }
         }
         else{
            return \Response::json(["status" => 2]);
         }
      }
      return \Response::json(["status" => 0]);
   }

   public function postVotes($danceId){
      $data = \Input::json();

      $danceSignature = $data->get('danceSignature');
      $adjudicatorSign = $data->get('adjudicatorSign');
      $votes = $data->get('votes');

      \Log::debug($votes);

      $DBResult = $this->tournamentHelper->setVotes(intval($danceId), $danceSignature, $adjudicatorSign, $votes);

      if($DBResult == true)
         return \Response::json(["error" => "false"], 200);
      else
         return \Response::json(["error" => "true"], 401);
   }

   public function postStatus(){
      $data = \Input::json();
      $adjudicator = Auth::user();

      $key = 'Status ' . $adjudicator->firstName . ' ' . $adjudicator->lastName . ',' . $adjudicator->judgeId;
      $status = [];
      $status['time'] = time();
      foreach($data as $datakey=>$name)
         $status[$datakey] = $name;
      Cache::put($key, $status, 20); //minutes

      return \Response::json(["error" => "false"], 200);
   }
}
