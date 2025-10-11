<?php namespace App\Http\Controllers\Wall;

use App;
use App\Http\Controllers\Competition;
use App\Http\Requests;
use App\Http\Controllers\Controller;

//use Tracy\Debugger;
//require 'c:\xampp\htdocs\ptt\laravel\vendor\tracy\tracy\src\tracy.php';
//Debugger::enable();
//Debugger::$strictMode = TRUE;

use App\Round;
use App\Layout;
use Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Input;
use View;
use Config;

class DashboardController extends Controller {

   private $tournamentHelper;

   private function loadTournamentData(){
      $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));}

   public function __construct(){
      $this->loadTournamentData();
      View::share('baseURI', '/ptt');
      View::share('tournamentName', $this->tournamentHelper->getName());
      View::share('wallPrefix', 'wall');
   }

   private function convert_dance($shortName){
    $replaceDance = Config::get('ptt.replaceDance');
    return strtr($shortName, $replaceDance);
   }

   public function showConfig($type=0){

      $colorSet = [];
      $divideFactor = [];

      $colorSet = array_add($colorSet, 0,'Zmień kolory:');
      $colorSet = array_add($colorSet, 1,'White-Black');
      $colorSet = array_add($colorSet, 2,'Black-White');
      $colorSet = array_add($colorSet, 3,'Blue-Yellow ');
      $colorSet = array_add($colorSet, 4,'Violet-Gold');
      $colorSet = array_add($colorSet, 5,'ZW-Blue-Yellow');
      $colorSet = array_add($colorSet, 10,'User');

      $divideFactor = array_add($divideFactor, 0, 'Proporcja:');
      $divideFactor = array_add($divideFactor,50, '50%-50%');
      $divideFactor = array_add($divideFactor,45, '45%-55%');
      $divideFactor = array_add($divideFactor,40, '40%-60%');
      $divideFactor = array_add($divideFactor,35, '35%-65%');
      
      if( $type == 'color' ){
         $color = Input::get('colorSet');
      }
      elseif($type == 'factor'){
         $factor = Input::get('divideFactor');
      }

    return view('wall.config')
       ->with('colorSet', $colorSet)
       ->with('divideFactor', $divideFactor);
    }

    private function getCompressedProgram(){
      $yes = Config::get('ptt.wallAllProgram');
      if($yes)//show all rounds
         $mainRounds = Round::orderBy('id')->get();
      else
         $mainRounds = Round::where('closed', '=', 0)->orderBy('id')->get();

      $compressedOrder = [];
      $rounds = [];

      $firstIndex = PHP_INT_MAX;
      $lastIndex = 0;
      foreach($mainRounds as $programRound){
         if(in_array($programRound->description, $rounds))
            continue;
         foreach($mainRounds as $index => $round){
            if($programRound->description == $round->description) {
               if(!in_array($programRound->description, $rounds))
                  $rounds[] = $programRound->description;
               if($index != count($compressedOrder)) {
                  if($index < $firstIndex)
                     $firstIndex = count($compressedOrder);
                  $lastIndex = count($compressedOrder);
               }
               $compressedOrder[] = $index;
            }
         }
      }

      $compressedProgram = [];
      foreach($rounds as $roundDescription) {
         $dances = [];
         $programRound = false;
         for($i = 0; $i < count($compressedOrder); $i++) {
            $round = $mainRounds[$compressedOrder[$i]];
            if($round->description != $roundDescription)
               continue;
            if($programRound === false)
               $programRound = $mainRounds[$compressedOrder[$i]];
            $order = '';
            if($compressedOrder[$i] >= $firstIndex-1 && $compressedOrder[$i] <= $lastIndex+1) {
               $order = $compressedOrder[$i] - $firstIndex + 2;
            }
            $dances[] = ['dance' => $round->dance, 'closed' => $round->closed, 'danceId' => $round->id, 'order' => $order];
         }
         if($programRound !== false) {
            $programRound->dances = $dances;
            $compressedProgram[] = $programRound;
         }
      }
      return $compressedProgram;
   }

   public function showDashboard(){
      $program = Round::all();
      $mainRounds = Round::orderBy('id')->groupBy('description')->get();
      $compressedProgram = $this->getCompressedProgram();

      $maxDance = Config::get('ptt.wallNoOfDances');
      $maxLines = Config::get('ptt.wallLines');
      $roundsFromDB = Round::where('closed', '=', 0)->get();
      $roundDescriptions = [];
      $roundAlternativeDescriptions = [];
      $rounds    = [];
      $danceNames = [];
      $couples    = [];
      $couplesNo    = [];
      $groupConst   = [];
      $display   = false;
      
      $groups  = false;
      $tmp_pos = 0;
      $pos = 0;
      $first = false;
      $different=true;
      $error = -1;

      //$wallColor = Config::get('ptt.wallColor');
      //$wallFactor = Config::get('ptt.wallFactor');
      //$fileName = Input::get('fileName');
      //$color = Input::get('colorSet');
      //$factor = Input::get('divideFactor');
      //dd($color,$factor);
      
      //calculate times
      $times = [];
      $layoutData =  Layout::get();
      if( count($program) > 0 ){
         if( $program[0]->closed == '1' )//first dance closed => probably program started, use current time
            $definedTime = Carbon::now('Europe/Warsaw');
         else
            $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);
      }
      else
         $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);
      $times[] = Carbon::now('Europe/Warsaw')->Format('H:i');
      $flag = 0;
      foreach( $compressedProgram as $index=>$programRound ){
         $bBreak = false;
         if( ($posit = mb_strpos( mb_strtoupper($programRound->description,'UTF-8'),'PRZERWA')) !== false ){
            $bBreak = true;
            $round = false;
         }
         else if( ($posit = mb_strpos( mb_strtoupper($programRound->description,'UTF-8'),'POKAZOWA')) !== false ){
            $round = $this->tournamentHelper->getRound('Wstępna'.substr($programRound->description, $posit+8, strlen($programRound->description)-$posit-8));
            if( $round == false )
               $round = $this->tournamentHelper->getRound('Finał'.substr($programRound->description, $posit+8, strlen($programRound->description)-$posit-8));            
         }
         else
            $round = $this->tournamentHelper->getRound($programRound->description);
         $counter = 0;
         foreach( $programRound->dances as $dance ){
            if( $bBreak ){
               $counter = $dance['dance'];
               break;
            }
            else if( $dance['closed'] == '0' )
               $counter += $programRound->groups;
            else
               $flag = 1;
         }
         if( $counter > 0 ){
            if( $flag == 1 )
               $flag = 2;
            $times[] = $definedTime->Format('H:i');
            if( $bBreak )
               $definedTime = $definedTime->addMinutes($counter);
            else if( $programRound->isFinal )
               $definedTime = $definedTime->addSeconds($layoutData[0]->durationFinal * $counter);
            else
               $definedTime = $definedTime->addSeconds($layoutData[0]->durationRound * $counter);
         }
         else{
            $times[] = '';
         }
      }
      if( count($compressedProgram) > 0 )//exist rounds
         $times[] = $definedTime->addMinutes($layoutData[0]->parameter2)->Format('H:i');
//   try {
      for($screen_max = 1; $pos < $maxDance; $pos++){ //configured in Config/ptt.php
         $different=true;
         if( $pos!=0 ){
            $roundDescriptions[$pos] = $roundAlternativeDescriptions[$pos] = null;
            $danceNames[$pos] = $rounds[$pos] = $couples[$pos] = $couplesNo[$pos] = null;
            $groupConst[$pos] = false;
         }
         else{
            $roundDescriptions[$tmp_pos] = $roundAlternativeDescriptions[$tmp_pos] = null;
            $danceNames[$tmp_pos] = $rounds[$tmp_pos] = $couples[$tmp_pos] = $couplesNo[$tmp_pos] = null;
            $groupConst[$tmp_pos] = false;
         }
         if($roundsFromDB != null && (count($roundsFromDB) > $pos ) ){
            if($roundsFromDB[$pos]->description != $roundDescriptions[$tmp_pos]){// && $tmp_pos!=$pos){
               $only_one_dance = false; 
               $group_const = false;
               //maybe one dance by turns or const groups
               if( in_array($roundsFromDB[$pos]->description, $roundDescriptions, true)){ 
                  $idx=0;
                  if( $tmp_pos > 3)//verify last 3 positions only
                     $idx = $tmp_pos-3;
                  for(; $idx < $tmp_pos; $idx++){
                     if( mb_strpos($roundDescriptions[$idx],$roundsFromDB[$pos]->description) !== false ){
                        $dance = null;
                        if($rounds[$idx] != false)
                           $dance = $this->tournamentHelper->getDanceCouples($rounds[$idx]->roundId, $roundsFromDB[$pos]->dance, $error);
                        if($dance !== false) {
                           if(count($couples[$idx]) == 1 ){//one group !!
                              $danceNames[$idx] .= " / ".$this->convert_dance($roundsFromDB[$pos]->dance);
                              $only_one_dance = true;
                           }
                           else if( count($couples[$idx]) > 1) { //groups
                              //maybe constant groups??
                              foreach($dance->couples as $index=>$group){
                                 if( $tmp_pos > 0 && count($dance->couples) == count($couples[$idx]) ){//the same group number, not for first group of course
                                    $group_const = true;
                                    asort($dance->couples[$index]);
                                    for($id=0; $id < count($group); $id++){
                                       if(count($group) != count($couples[$idx][$index])){
                                          //  dd(count($group),$idx,$index,$id,$group,$couples );
                                          $group_const = false;
                                          break; //different groups
                                       }
                                       $a = intval($group[$id]->number);
                                       $b = intval($couples[$idx][$index][$id]->number);
                                       if( $a != $b ){
                                          $group_const = false;
                                          break; //different groups
                                       }
                                    }
                                    if( $group_const == false )
                                       break; //different groups
                                 }
                              }
                              if( $group_const == true ){
                                 $danceNames[$idx] .= " / ".$this->convert_dance($roundsFromDB[$pos]->dance);
                                 $groupConst[$idx] = true;
                              }
                           }//else groups
                           else{
                              ;//no groups
                           }
                        }//if($dance !=
                     }//if( mb_strpos
                  }//for{}
               }
               if($only_one_dance == true || $group_const == true )
                  continue;
               else{
                  if($roundDescriptions[$tmp_pos] != null )
                     $tmp_pos++;
                  $screen_max++; //new description
                  $first = true;
               }
            }
            else{
               $different = false;
            }
            if($screen_max > $maxLines && ($groups == true || $first == true))//not too much lines on the screen
               break;
            $rounds[$tmp_pos] = $this->tournamentHelper->getRoundWithType($roundsFromDB[$pos]->description, $roundsFromDB[$pos]->type);
            $roundDescriptions[$tmp_pos] = $roundsFromDB[$pos]->description;
            $roundAlternativeDescriptions[$tmp_pos] = $roundsFromDB[$pos]->alternative_description;

            $couples[$tmp_pos] = null;
            $couplesNo[$tmp_pos] = false;
            $groupConst[$tmp_pos] = false;
            if($rounds[$tmp_pos] !== false) {
               $dance = $this->tournamentHelper->getDanceCouples($rounds[$tmp_pos]->roundId, $roundsFromDB[$pos]->dance, $error);
               if($dance !== false && count($dance->couples)) {
                  $display = true;
                  if(count($dance->couples) > 1) //in group
                     $groups = true;
                  else
                     $groups = false;
                  $group_const = false;
                  if($groups == true){
                     //maybe constant groups??
                     foreach($dance->couples as $index=>$group){
                        if( $tmp_pos > 0 && count($dance->couples) == count($couples[$tmp_pos-1]) ){//the same group number, not for first group of course
                           $group_const = true;
                           asort($dance->couples[$index]);
                           for($idx=0; $idx < count($group); $idx++){
                              if(count($group) != count($couples[$idx][$index])){
                                 //  dd(count($group),$idx,$index,$id,$group,$couples );
                                 $group_const = false;
                                 break; //different groups
                              }
                              if( intval($group[$idx]->number) != intval($couples[$tmp_pos-1][$index][$idx]->number) ){
                                 $group_const = false;
                                 break; //different groups
                              }
                           }
                           if( $group_const == false )
                              break; //different groups
                        }
                     }
                     if( $group_const == true ){
                        if($tmp_pos>0){
                           $danceNames[$tmp_pos-1] = $danceNames[$tmp_pos-1]." / ".$this->convert_dance($roundsFromDB[$pos]->dance);
                           $groupConst[$tmp_pos-1] = true;
                           if($different) //for Wieczysty should be commented !!!
                           {
                              $roundDescriptions[$tmp_pos] = null;
                              $tmp_pos--;
                           }
                        }
                        else{
                           $danceNames[$tmp_pos] = $danceNames[$tmp_pos]." / ".$this->convert_dance($roundsFromDB[$pos]->dance);
                           $groupConst[$tmp_pos] = true;
                        }
                     }
                     else{
                        $danceNames[$tmp_pos] = $this->convert_dance($roundsFromDB[$pos]->dance);
                        foreach($dance->couples as $index=>$group){
                           asort($dance->couples[$index]);
                           $couplesNo[$tmp_pos] += count($group);
                        }
                        $couples[$tmp_pos] = $dance->couples;
                        $tmp_pos++;
                        $screen_max = 1 + $screen_max + count($dance->couples); //each grup as line
                     }
                  }
                  else{
                     if( $first == true ){
                        $first = false;
                        $screen_max+=2;
                        $danceNames[$tmp_pos] = $this->convert_dance($roundsFromDB[$pos]->dance);
                     }
                     else if($tmp_pos != $pos)
                        $danceNames[$tmp_pos] = $danceNames[$tmp_pos]." / ".$this->convert_dance($roundsFromDB[$pos]->dance);
                     else{
                        $screen_max+=2;
                        $danceNames[$tmp_pos] = $this->convert_dance($roundsFromDB[$pos]->dance);
                     }
                     foreach($dance->couples as $index => $group){
                        asort($dance->couples[$index]);
                        $couplesNo[$tmp_pos] += count($group);
                     }
                     $couples[$tmp_pos] = $dance->couples;
                  }
               }
               else if( $error == 0 )
                  $danceNames[$tmp_pos] = "...brak tańca...";
               else
                  $danceNames[$tmp_pos] = ".....please wait....";
            }
            else{
               if( $danceNames[$tmp_pos] == null )
                  $danceNames[$tmp_pos] = $roundsFromDB[$pos]->dance;
               else
                  $danceNames[$tmp_pos] = $danceNames[$tmp_pos]."  ".$roundsFromDB[$pos]->dance;
            }
         }
      }
//   } catch (Exception $e) {
//              
//            $message = $e->getMessage();
//            dd('Exception Message: '. $message);
//  
//            $code = $e->getCode();       
//            dd('Exception Code: '. $code);
//  
//            $string = $e->__toString();       
//            dd('Exception String: '. $string);
//  
//            exit;
//        }

      if( count($roundDescriptions) > 0 )
         foreach($roundDescriptions as $index=>$desc)
         {
            if( ($pos = mb_strpos( mb_strtoupper($desc,'UTF-8'),'KOMBINACJA')) !== false ){
               $len = strlen( $desc );
               $roundDescriptions[$index] = substr($desc, 0, $pos).' Komb.'.substr($desc, $pos+11, $len-$pos-10);
            }
         }

      if($display != false){
         return view('wall.program')
            ->with('program', $program)
            ->with('mainRounds', $mainRounds)
            ->with('compressedProgram', $compressedProgram)
            ->with('rounds', $rounds)
            ->with('roundDescriptions', $roundDescriptions)
            ->with('roundAlternativeDescriptions', $roundAlternativeDescriptions)
            ->with('danceNames', $danceNames)
            ->with('couples', $couples)
            ->with('couplesNo', $couplesNo)
            ->with('groupConst', $groupConst)
            ->with('times', $times);
            //->with('color', $color)
            //->with('factor', $factor);
         }
      else{
         return view('wall.program')
            ->with('program', $program)
            ->with('mainRounds', $mainRounds)
            ->with('compressedProgram', $compressedProgram)
            ->with('rounds', null)
            ->with('roundDescriptions', "Brak rundy")
            ->with('roundAlternativeDescriptions', "")
            ->with('danceNames', null)
            ->with('couples', null)
            ->with('couplesNo', null)
            ->with('groupConst', null)
            ->with('times', null);
            //->with('color', $color)
            //->with('factor', $factor);
      }
   }

   public function logout(){
      Auth::logout();
      return redirect('wall/login')->with('flash_message', 'Wylogowano poprawnie');
   }
}
