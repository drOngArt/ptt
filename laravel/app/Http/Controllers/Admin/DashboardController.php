<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Competition;
use App\Http\Controllers\ManualResult;
use App\Http\Controllers\Judge;
use App\Http\Controllers\Dance;
use App\Http\Controllers\Couple;
use App\Http\Controllers\Club;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App; 
use App\Role;
use App\Round;
use App\User;
use App\Layout;
use Auth;
use Cache;
use DB;
use Hash;
use Illuminate\Support\Facades\Input;
use Session;
use Illuminate\Support\Str;
use Response;
use View;
use Config;
use Carbon\Carbon;

class DashboardController extends Controller {

   private $tournamentHelper;

   public function console_log($data, $add_script_tags = true) {
      $command = 'console.log('. json_encode($data).');';
      if ($add_script_tags) {
         $command = '<script>'. $command . '</script>';
      }
      echo $command;
   }

   private function loadTournamentData(){
      $this->tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));

      $judges = $this->tournamentHelper->getJudges();
      $this->updateJudgesDatabase($judges);
   }

   private function updateJudgesDatabase($judges){
      $judgeRole = Role::where('name', 'judge')->first();

      foreach ($judges as $judge) {
         $localJudge = User::where('username', '=', $judge->firstName.' '.$judge->lastName)->first();
         if($localJudge == null and ($judge->firstName != '' or $judge->lastName != '')){ //add judge to local database
            $newJudge = new User();
            $newJudge->username = $judge->firstName.' '.$judge->lastName;
            $newJudge->password = Hash::make(Str::random());
            $newJudge->firstName = $judge->firstName;
            $newJudge->lastName = $judge->lastName;
            $newJudge->judgeId = $judge->plId;
            $newJudge->save();
            $newJudge->attachRole($judgeRole);
            $newJudge->save();
         }
      }
   }

   public function __construct(){
      $this->loadTournamentData();

      $adminId = Auth::user()->id;
      View::share('adminId', $adminId);
      View::share('baseURI', '/ptt');
      View::share('tournamentName', $this->tournamentHelper->getName());
   }

   private function setJudgeStatus($judge) {
      $key = 'Status ' . $judge->firstName . ' ' . $judge->lastName . ',' . $judge->plId;
      $status = Cache::get($key, false);
      if($status !== false)
      {
         $judge->status = true;
         $judge->statusKey = $key;
         $judge->softwareVersion = @$status['softwareVersion'];
         $judge->batteryLevel = @$status['batteryLevel'];
      }
      else
         $judge->status = false;
   }

   public function showDashboard(){
      $judgeRole = Role::where('name', 'judge')->first();
      $judges = Role::find($judgeRole->id)->users()->get()->sortBy('lastName');

      foreach($judges as $judge) {
         $judge->isInProgram = false;
         $judge->plId = $judge->judgeId;
         $this->setJudgeStatus($judge);
      }
      $mainJudge = false;
      $scrutineers = [];
      $JudgesAll = $this->tournamentHelper->getJudges(0);
      
      foreach($JudgesAll as $judge){
         $judge->isInProgram = false;
      }
      
      $roundsFromDB = Round::where('closed', '=', 0)->get();
      foreach($roundsFromDB as $roundFromDB){
         $round = $this->tournamentHelper->getRoundWithType($roundFromDB->description, $roundFromDB->type);
         if($round !== false){
            if( $mainJudge == false)
               $mainJudge = $this->tournamentHelper->getMainJudge($round->roundId);
            $judgesForRound = $this->tournamentHelper->getJudges($round->roundId);
            $scrutineersForRound = $this->tournamentHelper->getScrutineers($round->roundId);
            foreach($judges as $judge){
               foreach($judgesForRound as $roundJudge){
                  if($judge->firstName == $roundJudge->firstName && $judge->lastName == $roundJudge->lastName)
                     $judge->isInProgram = true;
               }
            }
            foreach($JudgesAll as $judge){
               foreach($judgesForRound as $roundJudge){
                  if($judge->firstName == $roundJudge->firstName && $judge->lastName == $roundJudge->lastName)
                     $judge->isInProgram = true;
                  else if( $judge->isInProgram != true )
                     $judge->isInProgram = false;
               }
            }
            foreach($scrutineersForRound as $judge)
               $scrutineers = array_add($scrutineers,$judge->plId2,$judge);
         }
      }

      //remove main judge from list
      if( $mainJudge == false )//probably program isn't read or no structs closed
         $mainJudge = $this->tournamentHelper->getMainJudge(0);
      if( $mainJudge ){
         foreach($JudgesAll as $key=>$judge){
            if($judge->firstName == $mainJudge->firstName && $judge->lastName == $mainJudge->lastName)
               unset( $JudgesAll[$key] );
         }
      }
      usort($JudgesAll, function($a, $b) {
         if( $a->lastName == $b->lastName)
            return( $a->firstName > $b->firstName );
         else
            return( $a->lastName > $b->lastName );
      });

      $judges->sort(function($a, $b) {
         if( $a->lastName == $b->lastName)
            return( $a->firstName > $b->firstName );
         else
            return( $a->lastName > $b->lastName );
      });

      if( count($scrutineers) == 0 ) 
         $scrutineers = $this->tournamentHelper->getScrutineers(0);
      $isInProgram = false;
      $isntInProgram = false;
      foreach($judges as $judge) {
          if($judge->isInProgram == true)
              $isInProgram = true;
          else
              $isntInProgram = true;
      }
      $filterInProgram = false;
      if($isInProgram && $isntInProgram)
          $filterInProgram = true;

      return view('admin.dashboard')
           ->with('judges', $judges)
           ->with('judgestoPrint', $JudgesAll)
           ->with('mainJudge', $mainJudge)
           ->with('scrutineers', $scrutineers)
           ->with('filterInProgram', $filterInProgram);
   }

   public function logout(){
       Auth::logout();
       return redirect('admin/login')->with('flash_message', 'Wylogowano poprawnie');
   }

   public function showChangePasswordForm($userId,$flag=0){
      $user = User::find($userId);

      return view('admin.password')->with('user', $user)
         ->with('flag', $flag);
   }

    public function postChangePassword($userId,$flag=0){
      $user = User::find($userId);
      $password = Input::get('password');
      $user->password = Hash::make($password);
      $user->save();
      if($flag == 'true')
         return redirect('/admin');
      else
         return redirect('/admin/round');
    }

   private function convert_pl($first) {
      $second = [
        "\xc4\x85" => "\x61", "\xc4\x84" => "\x41",
        "\xc4\x87" => "\x63", "\xc4\x86" => "\x43",
        "\xc4\x98" => "\x45", "\xc4\x99" => "\x65",
        "\xc5\x81" => "\x4c", "\xc5\x82" => "\X6c",
        "\xc3\xb3" => "\x6f", "\xc3\x93" => "\x4f",
        "\xc5\x9b" => "\x73", "\xc5\x9a" => "\x53",
        "\xc5\xbc" => "\x7a", "\xc5\xbb" => "\x5a",
        "\xc5\xba" => "\x7a", "\xc5\xb9" => "\x5a",
        "\xc5\x84" => "\x6e", "\xc5\x83" => "\x4e"
      ];
      return strtr($first, $second);
   }

    public function savePasswordAll(){
      $judgeRole = Role::where('name', 'judge')->first();
      $judges = Role::find($judgeRole->id)->users()->get();
      $password = Input::get('myPass');
      foreach($judges as $judge) {
         $first = $this->convert_pl($judge->firstName);//change polish letter to base latin ex.: 'Ą' => 'A' 
         $last = $this->convert_pl($judge->lastName);
         $judge->password = Hash::make( mb_strtolower($first[0].$last[0].$password,'UTF-8'));
         $judge->save();
      }
      return redirect('/admin');
   }

   public function showTournamentChooser(){
      return view('admin.tournamentChooser');
   }

   private function resetProgramInLocalDB(){
      $BLOCK_TABLE_NAME = 'block';
      DB::table($BLOCK_TABLE_NAME)->truncate();
   }

   public function postTournamentChooser(){
      $chooseTournamentPath = 'admin/chooseTournament';
      $filePathOffset = 20;
      $tournamentDirectoryFile = Input::file('tournamentDirectoryFile');
      $tournamentDirectoryPath = Input::get('tournamentDirectoryPath');
      
     

      //reset judges
      $judgeRole = Role::where('name', 'judge')->first();
      $judges = Role::find($judgeRole->id)->users()->get();

      //reset program
      $this->resetProgramInLocalDB();

      foreach($judges as $judge){
         $judge->delete();
      }

      $tournamentHelper = Competition::create(Cache::get('tournamentDirectory'));

      $tournamentHelper->changeVotesFolder();
      
      $viewCacheFiles = glob(App::storagePath().'/framework/views/*');
      foreach($viewCacheFiles as $file) {
         if(is_file($file))
            unlink($file);
      }

      if(!empty($tournamentDirectoryPath)){
         if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            $tournamentDirectoryPath = @iconv( 'UTF-8', 'cp1250', $tournamentDirectoryPath);
         Cache::forever('tournamentDirectory', $tournamentDirectoryPath);
         return redirect('/admin');
      }
      else if(!empty($tournamentDirectoryFile)){
         if($tournamentDirectoryFile->getClientOriginalName() != 'TurniejDir.txt'){
            return redirect($chooseTournamentPath)->withErrors(['message' => 'Podany plik nie jest plikiem TurniejDir.txt']);
         }
         Cache::forever('tournamentDirectory', file_get_contents($tournamentDirectoryFile, null, null, $filePathOffset, filesize($tournamentDirectoryFile) - 22));
         return redirect('/admin');
      }
      else{
         return redirect($chooseTournamentPath)->withErrors(['message' => 'Wybierz ścieżkę.']);
      }
   }

   private function getCompressedProgram(){
      $mainRounds = Round::orderBy('id')->get();

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
            if($programRound->description[0] == 'F' || $programRound->description[0] == 'P') //probably final(Finał), show(Pokaz) or break(Przerwa)
               $programRound->isFinal = true;
            else
               $programRound->isFinal = false;
            $compressedProgram[] = $programRound;
         }
      }
      return $compressedProgram;
   }

   private function isBasicRoundInProgram($roundId, $program) {
      $round = $this->tournamentHelper->getRound(intval($roundId));
      if($round === false)
          return false;
      $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
      foreach($program as $programRound) {
         if(strpos($programRound->description, $name) !== false)
            return true;
      }
      return false;
   }

    private function isAdditionalRoundInProgram($roundId, $program) {
      $round = $this->tournamentHelper->getRound(intval($roundId));
      if($round === false)
         return false;
      $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
      foreach($program as $programRound){
         if(strpos($programRound->description, $name) !== false && strpos($programRound->description, $round->matchType) !== false)
            return true;
      }
      return false;
    }

   private function ISO88592_2_WIN1250($tekst){
      return strtr($tekst, "\xa1\xa6\xac\xb1\xb6\xbc", "\xa5\x8c\x8f\xb9\x9c\x9f");
   }

   public function saveProgram($fileName, $Program, $type){
      $tournamentDirectory = Cache::get('tournamentDirectory');
      $reportFile = @fopen($tournamentDirectory . '/' . trim($fileName) . '.csv', 'w');
      if($reportFile === false)
         return false;

      $content = 'Program_turnieju';
      foreach($Program as $round){
         $content = $content . "\r\n" . ';9'. $round->description;
         if( $round->isDance == 1 ){
            $content = $content .' (';
            if( $type === 'new' ){
               for($i = 0; $i< count($round->dances);$i++){
                  $content = $content . ' '. $round->dances[$i]['dance'];
               }
            }
            else{
               for($i = 0; $i< count($round->dances);$i++){
                  $content = $content . ' '. $round->dances[$i];
               }
            }
            $content = $content .' )';
         }
      }
      //conversion trick for windows-1250, first step to 8859-2
      $content = mb_convert_encoding($content, 'ISO-8859-2'); 
      //doesn't support windows-1250, 2-nd step needed additional conversion
      $content = $this->ISO88592_2_WIN1250($content);
      $length = strlen($content);
      $written = fwrite($reportFile, $content, $length);
      if($written === false || $written != $length)
         return false;
      return true;
   }

   public function saveCurrentProgram(){ 
      $compressedProgram = $this->getCompressedProgram();
      $fileName = Input::get('fileName');
      if( $this->saveProgram($fileName, $compressedProgram, 'new' ) == false)
         Session::flash('status', 'error');
      else
         Session::flash('status', 'success');
      return redirect('admin/program');
   }

   public function showProgram(){
      $program = Round::all();

      $mainRounds = Round::orderBy('id')->groupBy('description')->get();
      $allAdditionalRounds = $this->tournamentHelper->getAdditionalRounds();
      $additionalRounds = [];
      $times = [];
      $compressedProgram = $this->getCompressedProgram();
      $layoutData =  Layout::get();

      if( count($program) > 0 ) {
         if( $program[0]->closed == '1' )//first dance closed => probably program started, use current time
            $definedTime = Carbon::now('Europe/Warsaw');
         else
            $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);
      }
      else
         $definedTime = Carbon::createFromFormat('H:i', $layoutData[0]->startTime)->addMinutes($layoutData[0]->parameter1);

      $flag = 0;
      foreach( $compressedProgram as $index=>$programRound ) {
         $bBreak = false;
         if( ($pos = mb_strpos( mb_strtoupper($programRound->description,'UTF-8'),'PRZERWA')) !== false ) {
            $bBreak = true;
            $round = false;
         }
         else if( ($pos = mb_strpos( mb_strtoupper($programRound->description,'UTF-8'),'POKAZOWA')) !== false ) {
            $round = $this->tournamentHelper->getRound('Wstępna'.substr($programRound->description, $pos+8, strlen($programRound->description)-$pos-8));
            if( $round == false )
               $round = $this->tournamentHelper->getRound('Finał'.substr($programRound->description, $pos+8, strlen($programRound->description)-$pos-8));            
         }
         else
            $round = $this->tournamentHelper->getRound($programRound->description);
         $couples = 0;

         if( $round ) {
            $couples = $round->NumberOfCouples;
            if( $couples )
               $compressedProgram[$index]->couples = $couples;
            else
               $compressedProgram[$index]->couples = false;
         }
         else
            $compressedProgram[$index]->couples = false;

         $counter = 0;
         foreach( $programRound->dances as $dance ) {
            if( $bBreak && $dance['closed'] == '0') {
               $counter = $dance['dance'];
               break;
            }
            else if( $dance['closed'] == '0' )
               $counter += $programRound->groups;
            else
               $flag = 1;
         }
         if( $counter > 0 ) {
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
         else {
            $times[] = '';
         }
      }
      if( count($compressedProgram) > 0 )//exist rounds
         $times[] = $definedTime->addMinutes($layoutData[0]->parameter2)->Format('H:i');

      foreach($allAdditionalRounds as $round) {
         if($this->isBasicRoundInProgram($round->roundId, $compressedProgram) && !$this->isAdditionalRoundInProgram($round->roundId, $compressedProgram))
            $additionalRounds[] = $round;
      }

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      $PartsNo = [];
      $PartsStr = "BLOK - ";
      foreach($compressedProgram as $round){ 
         foreach($scheduleParts as $category){
            if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               if( !in_array($category->part,$PartsNo)){
                  $PartsNo[] = $category->part;
                  if( count($PartsNo) == 1 )
                     $PartsStr .= $category->part;
                  else
                     $PartsStr .= ', '.$category->part;
               }
            }
         }
      }

      $rounds = $this->tournamentHelper->getBaseRounds();
      $categoriesName = [];
      $roundNames = [];
      $additionalNames = [];
      if( $rounds ){
         foreach($rounds as $round){
            if( !in_array($round->categoryName.' '.$round->className.' '.$round->styleName, $categoriesName, true) ){
               $description = $round->categoryName.' '.$round->className.' '.$round->styleName.' ( ';
               for($i = 0; $i< count($round->dances);$i++){
                  $description = $description . $round->dances[$i] . ' ';
               }
               $description = $description . ')';
               $categoriesName[] = $description;
            }
         }
         $categoriesName = array_unique($categoriesName);
         asort($categoriesName);
         $categoriesNames = array_combine($categoriesName,$categoriesName );

         $roundNames[ '1/32 Finału' ] = '1/32 Finału';
         $roundNames[ '1/16 Finału' ] = '1/16 Finału';
         $roundNames[ '1/8 Finału' ] = '1/8 Finału';
         $roundNames[ '1/4 Finału' ] = '1/4 Finału';
         $roundNames[ '1/2 Finału' ] = '1/2 Finału';
         $roundNames[ 'Finał' ] = 'Finał';
         $roundNames[ 'Runda Pokazowa' ] = 'Runda Pokazowa';
         $roundNames[ 'sh_br' ] = 'Pokaz/Przerwa';
         $roundNames[ 'my' ] = 'Zdefiniuj własną:';

         $additionalNames[ ' ' ] = ' ';
         $additionalNames[ 'Dodatkowa' ] = 'Dodatkowa';
         $additionalNames[ 'Baraż' ] = 'Baraż';
      }
      else{//no rounds?/ impossible, maybe directory was changed
         return view('admin.tournamentChooser');
      }
      return view('admin.program')
            ->with('program', $program)
            ->with('compressedProgram', $compressedProgram)
            ->with('additionalRounds', $additionalRounds)
            ->with('roundNames', $roundNames)
            ->with('categoriesNames', $categoriesNames)
            ->with('additNames', $additionalNames)
            ->with('layout', $layoutData[0])
            ->with('times', $times)
            ->with('parts', $PartsStr);
   }

   public function newProgram(){
      $baseRounds = $this->tournamentHelper->getBaseRounds();
      $tournamentDirectory = Cache::get('tournamentDirectory');
      
      $scheduleParts = $this->tournamentHelper->getPartsCSV();

      foreach($baseRounds as $round) {
         $round->idx = 0;
         foreach($scheduleParts as $index=>$category){
            if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
               $round->className = 'H';
            $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            if( mb_strpos( mb_strtoupper($name,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               $round->positionW = $category->part;
               $round->idx = $index;
            }
         }
         if( !$round->positionW ) //undefined link to O block
            $round->positionW = '0';
         }
      if( $scheduleParts ){
         usort($baseRounds, function($a, $b) {
            if( $a->positionW == $b->positionW )
               return($a->idx > $b->idx);
            else
               return( $a->positionW > $b->positionW );
         });
      }
      return view('admin.newProgram')
         ->with('tournamentDirectory', $tournamentDirectory)
         ->with('baseRounds', $baseRounds);
   }  
   
   public function selectedCategories($type=0){
      $stDancesTable = Config::get('ptt.stdDances');
      $rounds = $this->tournamentHelper->getBaseRounds();
      $classOneRoundOnly = Config::get('ptt.classOneRoundOnly');
      $bg_colors = [ '#F1C40F', '#58D68D', '#DC7633', '#AED6F1', '#F0B27A', '#5DADE2', '#ccff66', '#ff33bb', '#E8DAEF', '#cc6699', 
                          '#EC7063', '#A569BD', '#1affff', '#ffff1a', '#D2B4DE', '#e60073', '#FEF5E7', '#1affa3', '#ff6600', '#1ac6ff' ];

      if( $rounds ){
         $categoriesName = [];
         $roundNames = [];
         $additionalNames = [];
         foreach($rounds as $round){
            if( !in_array($round->categoryName.' '.$round->className.' '.$round->styleName, $categoriesName, true) ){
               $description = $round->categoryName.' '.$round->className.' '.$round->styleName.' ( ';
               for($i = 0; $i< count($round->dances);$i++){
                  $description = $description . $round->dances[$i] . ' ';
               }
               $description = $description . ')';
               $categoriesName[] = $description;
            }
         }
         $categoriesName = array_unique($categoriesName);
         asort($categoriesName);
         $categoriesNames = array_combine($categoriesName,$categoriesName );

         $roundNames[ '1/32 Finału' ] = '1/32 Finału';
         $roundNames[ '1/16 Finału' ] = '1/16 Finału';
         $roundNames[ '1/8 Finału'  ] = '1/8 Finału';
         $roundNames[ '1/4 Finału'  ] = '1/4 Finału';
         $roundNames[ '1/2 Finału'  ] = '1/2 Finału';
         $roundNames[ 'Finał' ] = 'Finał';
         $roundNames[ 'Wstępna' ] = 'Wstępna';
         $roundNames[ 'Runda Pokazowa' ] = 'Runda Pokazowa';
         $roundNames[ 'Runda I' ] = 'Runda I';

         $additionalNames [ ' ' ] = ' ';
         $additionalNames [ 'Dodatkowa' ] = 'Dodatkowa';
         $additionalNames [ 'Baraż' ] = 'Baraż';
      }
      else{
         return redirect('admin/program');
      }
      
      if(empty($type)){
         $roundSelect = Input::get('selected');
         if( count($roundSelect) == 0 ) //empty
             return redirect('admin/program');

         $Program = [];
         $count = -1;
         foreach($roundSelect as $index){ 
            $count++;
            if($count>19)
               $count=0;
            $round = $this->tournamentHelper->getBaseRound(intval($index));
            $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            $round->description = $name;
            $round->isDance = "1"; //always is dance :)
            $round->bg_color = $bg_colors[$count];
            if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMBINACJA') !== false && 
                mb_strpos(mb_strtoupper(trim($round->roundName),'UTF-8'),'WSTĘPNA') !== false ){ //found Wstępna, add Runda Pokazowa
               if( count($round->dances) > 3 ){ //no make sence divide 3 dances
                  $new_round_la = clone $round;
                  $dances_st = [];
                  $dances_lt = [];
                  for($i = 0; $i< count($round->dances);$i++){
                     if( in_array(mb_strtoupper($round->dances[$i],'UTF-8'), $stDancesTable) )//standard dance
                        $dances_st[] = $round->dances[$i];
                     else
                        $dances_lt[] = $round->dances[$i];
                  }
                  $round->dances = $dances_st;
                  $new_round_la->dances = $dances_lt;
                  $name_st = $round->styleName . ' ST';
                  $name_lt = $round->styleName . ' LA';
                  $round->styleName = $name_st;
                  $new_round_la->styleName = $name_lt;
                  $round->description = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
                  $new_round_la->description = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $new_round_la->styleName;

                  $new_round_st_next = clone $round;
                  $new_round_la_next = clone $new_round_la;
                  $new_round_st_next->roundName = $new_round_la_next->roundName = 'Runda Pokazowa';
                  $new_round_st_next->description = 'Runda Pokazowa' . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
                  $new_round_la_next->description = 'Runda Pokazowa' . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $new_round_la->styleName;
                  $Program[] = $new_round_st_next;
                  $Program[] = $new_round_la_next;
                  $Program[] = $round;
                  $Program[] = $new_round_la;
               }
               else{
                  $new_round = clone $round;
                  $new_round->roundName = 'Runda Pokazowa';
                  $new_round->description = $new_round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
                  $Program[] = $new_round;
                  $Program[] = $round;
               }
            }
            elseif( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMBINACJA') !== false ){ //found kombination, divide to STD and LAT
               if( count($round->dances) > 3 ){ //more than 3 dances
                  $new_round_la = clone $round;
                  $dances_st = [];
                  $dances_lt = [];
                  for($i = 0; $i< count($round->dances);$i++){
                     if( in_array(mb_strtoupper($round->dances[$i],'UTF-8'), $stDancesTable) )//standard dance
                        $dances_st[] = $round->dances[$i];
                     else
                        $dances_lt[] = $round->dances[$i];
                  }
                  $round->dances = $dances_st;
                  $new_round_la->dances = $dances_lt;
                  $name_st = $round->styleName . ' ST';
                  $name_lt = $round->styleName . ' LA';
                  $round->styleName = $name_st;
                  $new_round_la->styleName = $name_lt;
                  $round->description = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
                  $new_round_la->description = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $new_round_la->styleName;
                  $Program[] = $round;
                  $Program[] = $new_round_la;
               }
               else{
                   $Program[] = $round;
               }
            }
            elseif( mb_strpos(mb_strtoupper(trim($round->roundName),'UTF-8'),'WSTĘPNA') !== false ){ //found Wstępna, add Runda Pokazowa
               $new_round = clone $round;
               $new_round->roundName = 'Runda Pokazowa';
               $new_round->description = $new_round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
               $Program[] = $new_round;
               $Program[] = $round;
            }
            else{
               $Program[] = $round;
            }
         }
    //$var = json_encode($Program,JSON_UNESCAPED_UNICODE);
    //echo "<script> console.log({$var})</script>";
         $ProgramCombination = [];
         $both = false;
         foreach($Program as $index => $round){
            if( $both == true ){
               unset($Program[$index]);
               $both = false;
               continue;
            }
    //echo "<script> console.log({$index})</script>";
    //$var = json_encode($round,JSON_UNESCAPED_UNICODE);
    //echo "<script> console.log({$var})</script>";
            if( mb_strpos($round->roundName,'1/') !== false && !in_array(mb_strtoupper($round->className,'UTF-8'), $classOneRoundOnly) ){ //add next rounds, up to final except Srebro, Brąz classes
               $round_no = intval(mb_substr($round->roundName,2,2));
               if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMBINACJA') !== false )
                  $both = true;
               $round_copy = clone $Program[$index];
               if( $both == true )
                  $round_copy2 = clone $Program[$index+1]; //should be next round with Latin
               unset($Program[$index]);
               $ProgramCombination[] = $round_copy;
               if( $both == true ) 
                  $ProgramCombination[] = $round_copy2;
               do{
                  $round_no = ($round_no/2);
                  $new_round = clone $round_copy;

                  if( $round_no == 1 )
                     $new_round->roundName = 'Finał';
                  else
                     $new_round->roundName = '1/'. $round_no .' Finału';
                  $name = $new_round->roundName . ' ' . $round_copy->categoryName . ' ' . $round_copy->className . ' ' . $round_copy->styleName;
                  $new_round->description = $name;
                  $ProgramCombination[] = $new_round;
                  if( $both == true ){
                     $new_round2 = clone $round_copy2;
                     if( $round_no == 1 )
                        $new_round2->roundName = 'Finał';
                     else
                        $new_round2->roundName = '1/'. $round_no .' Finału';
                     $name = $new_round2->roundName . ' ' . $round_copy2->categoryName . ' ' . $round_copy2->className . ' ' . $round_copy2->styleName;
                     $new_round2->description = $name;
                     $ProgramCombination[] = $new_round2;
                  }
               }while( $round_no != 1);
            }
         }
         // divide to two arrays - wstepna/pokazowa and rest
         $ProgramFinal = [];
         //$Program_LA = [];
         $ProgramPremilinary = [];
         foreach($Program as $index => $point){
            if( mb_strpos(mb_strtoupper($point->roundName),'WSTĘPNA') !== false || mb_strpos(mb_strtoupper($point->roundName),'POKAZ') !== false ) {
               $ProgramPremilinary[] = $point;
               unset($Program[$index]);
            }
            //else if( mb_strpos(mb_strtoupper($point->styleName),'LA') !== false ) 
            //   $Program_LA[] = $point;
            else
               $ProgramFinal[] = $point;
         }
           /* usort($Program_ST, function($a, $b) {
            if($a->roundName == $b->roundName){
               if($a->categoryName == $b->categoryName)
                  return( $a->className > $b->className );
               else
                  return( $a->categoryName > $b->categoryName );
            }
            else{
               if( $a->roundName == '1/8 Finału' && ($b->roundName == '1/4 Finału' || $b->roundName == '1/2 Finału') )
                  return( $a->roundName < $b->roundName );
               else if( $a->roundName == '1/4 Finału' && $b->roundName == '1/2 Finału' )
                  return( $a->roundName < $b->roundName );
               else if( $b->roundName == '1/8 Finału' && ($a->roundName == '1/4 Finału' || $a->roundName == '1/2 Finału') )
                  return( $a->roundName < $b->roundName );
               else if( $b->roundName == '1/4 Finału' && $a->roundName == '1/2 Finału' )
                  return( $a->roundName < $b->roundName );
               else
                  return( $a->roundName > $b->roundName );
            }
         });
         usort($Program_LA, function($a, $b) {
            if($a->roundName == $b->roundName){
               if($a->categoryName == $b->categoryName)
                  return( $a->className > $b->className );
               else
                  return( $a->categoryName > $b->categoryName );
            }
            else{
               if( $a->roundName == '1/8 Finału' && ($b->roundName == '1/4 Finału' || $b->roundName == '1/2 Finału') )
                  return( $a->roundName < $b->roundName );
               else if( $a->roundName == '1/4 Finału' && $b->roundName == '1/2 Finału' )
                  return( $a->roundName < $b->roundName );
               else if( $b->roundName == '1/8 Finału' && ($a->roundName == '1/4 Finału' || $a->roundName == '1/2 Finału') )
                  return( $a->roundName < $b->roundName );
               else if( $b->roundName == '1/4 Finału' && $a->roundName == '1/2 Finału' )
                  return( $a->roundName < $b->roundName );
               else
                  return( $a->roundName > $b->roundName );
            }
         });
         usort($ProgramPremilinary, function($a, $b) {
            if($a->roundName == $b->roundName){
               if($a->categoryName == $b->categoryName)
                  return( $a->className > $b->className );
               else
                  return( $a->categoryName > $b->categoryName );
            }
            else
               return( $a->roundName > $b->roundName );
         });
         unset($Program);
         $Program = array_merge( $ProgramPremilinary, $Program_ST );
         $Program = array_merge( $Program, $Program_LA);
         $data = array_values($Program);
         $Program = array_combine(array_keys($data), $data);*/
         usort($ProgramFinal, function($a, $b) {
            if($a->className == $b->className)
               return( $a->categoryName < $b->categoryName );
            else
               return( $a->className > $b->className );
         });
         unset($Program);
         $Program = array_merge( $ProgramPremilinary, $ProgramCombination );
         $Program = array_merge( $Program, $ProgramFinal);
         $data = array_values($Program);
         $Program = array_combine(array_keys($data), $data);

         //$Program = array_merge( $tempProgram, $Program);
         //$data = array_values($Program);
         //$Program = array_combine(array_keys($data), $data);
      }
      else{//another operation
         if( $type == 'saveFile' ){ //save file
            $Program  = Session::get('new_program');
            $fileName = Input::get('fileName');
            if( $this->saveProgram($fileName, $Program, 'old' ) == false)
               Session::flash('status', 'error');
            else
               Session::flash('status', 'success');
         }
         else{
            $program  = Session::get('new_program');
            if($type != 'nothing'){
               $roundsIds = Input::get('roundId');
               $Program = [];
               foreach($roundsIds as $id){
                  $Program[] = $program[$id];
               }
            }
            else
               $Program = $program;
         }
      }
      Session::put('new_program', $Program);
      return view('admin.programSet')
         ->with('program', $Program)
         ->with('roundNames', $roundNames)
         ->with('categoriesNames', $categoriesNames)
         ->with('additNames', $additionalNames);
   }

   public function postSelectedCategories($type=0){
      $added_all = [];
      if(empty($type)){
         return redirect('admin/selectedCategories/nothing');
      }
      else{//add round, break, show etc...
         $Program  = Session::get('new_program');
         if( $type == 'addRound' ){
            $roundName = Input::get('round');
            $category = Input::get('category');
            $additional = Input::get('additional');
            $added = clone reset($Program);
            $added->bg_color = '#7FFF00';
            if( (($pos = mb_strpos($category,'(')) !== false) ){ //found dances
               $added->description = $roundName.' '.trim(substr($category,0,$pos));
               foreach($Program as $round){
                  if( mb_strpos($round->description, $added->description, 0, 'UTF-8') !== false &&
                     mb_strpos($round->description, $additional, 0, 'UTF-8') !== false){
                     return redirect('admin/program/selectedCategories/nothing');
                  }
               }
               $added->roundName = $roundName;
               $dances = explode(' ',trim(substr($category, $pos, strlen($category)-$pos)," ()"));
               if( count($dances) > 0 ){
                  $cnt = count($dances);
                  unset($added->dances);
                  $added->dances = [];
                  for($i=0;$i < $cnt; $i++ ){
                     $added->dances[] = strlen($dances[$i]) < 4 ? $dances[$i] : substr($dances[$i],0,3);
                  }
               }
            }
            if( mb_strpos(mb_strtoupper(trim($added->description),'UTF-8'),'KOMBINACJA') !== false ){ //found kombination, divide to STD and LAT
               if( count($added->dances)%2 === 0 ){ //event number but odd don't divide:)
                  $new_round_la = clone $added;
                  $dances_st = [];
                  $dances_lt = [];
                  for($i = 0; $i< count($added->dances)/2;$i++){
                     $dances_st[] = $added->dances[$i];
                     $dances_lt[] = $added->dances[$i+count($added->dances)/2];
                  }
                  $added->dances = $dances_st;
                  $new_round_la->dances = $dances_lt;
                  $added->description = $added->description . ' ST';
                  $new_round_la->description = $new_round_la->description . ' LA';
                  if( $additional != ' ' ){
                      $new_round_la->matchType = $additional;
                      $new_round_la->description = $new_round_la->description. ' '.$additional;
                      $new_round_la->isAdditional = true;
                  }
                  $added_all[] = $new_round_la;
               }
            }
            if( $additional != ' ' ){
               $added->matchType = $additional;
               $added->description = $added->description. ' '.$additional;
               $added->isAdditional = true;
               
            }
            $added_all[] = $added;
         }
         elseif( $type == 'addShow' ){
            $showName = trim(Input::get('showName'));
            $showNameDance = trim(Input::get('showNameDance'));
            $added = clone reset($Program);
            $added->description = $showName;
            $added->roundName = $showName;
            $added->categoryName = $added->className = $added->styleName = $added->matchType = '';
            $added->isAdditional = false;
            $added->bg_color = '#00FFFF';
            foreach($Program as $round){
               if(mb_strpos($round->description, $added->description, 0, 'UTF-8') !== false )
                  return redirect('admin/program/selectedCategories/nothing');
            }
            if( !empty($showNameDance) ){
               $dances = explode(' ',$showNameDance);
               if( count($dances) > 0 ){
                  $cnt = count($dances);
                  unset($added->dances);
                  $added->dances = [];
                  for($i=0;$i < $cnt; $i++ ){
                     $added->dances[] = strlen($dances[$i]) < 4 ? $dances[$i] : substr($dances[$i],0,3);
                  }
               }
               else{
                  unset($added->dances);
                  $added->isDance = 0;
               }
            }
            else{
               unset($added->dances);
               $added->isDance = 0;
            }
            $added_all[] = $added;
         }
         elseif( $type == 'addBreak' ){
            $BreakName = Input::get('BreakName');
            $breakTime = Input::get('breakTime');
            $added = clone reset($Program);
            $added->description = $BreakName;
            $added->roundName = $BreakName;
            $added->categoryName = $added->className = $added->styleName = $added->matchType = '';
            $added->isAdditional = false;
            $added->bg_color = '#FFFF00';
            foreach($Program as $round){
               if(mb_strpos($round->description, $added->description, 0, 'UTF-8') !== false )
                  return redirect('admin/program/selectedCategories/nothing');
            }
            if(!empty($breakTime)){
               unset($added->dances);
               $added->dances = [];
               $added->dances[] = $breakTime;
            }
            else{
               unset($added->dances);
               $added->isDance = 0;
            }
            $added_all[] = $added;
         }
      }
      if( empty($added_all) == false ){
         foreach( $added_all as $point){
            array_push($Program, $point);
         }
      }
      return view('admin.programTemp')
            ->with('program', $Program)
            ->with('cmd', true);
   }
    
   public function editProgram($cmd=0){
      $additionalRounds = NULL;
      $layout = Layout::get();
        
      if($cmd){
         $program  = Session::get('new_program');
      }
      else{
         $program = $this->getCompressedProgram();
         foreach( $program as $index=>$programRound ){
            if( ($pos = mb_strpos( mb_strtoupper($programRound->description,'UTF-8'),'POKAZOWA')) !== false ){
               $round = $this->tournamentHelper->getRound('Wstępna'.substr($programRound->description, $pos+8, strlen($programRound->description)-$pos-8));
            if( $round == false )
               $round = $this->tournamentHelper->getRound('Finał'.substr($programRound->description, $pos+8, strlen($programRound->description)-$pos-8));            
            }
            else
               $round = $this->tournamentHelper->getRound($programRound->description);
            $couples = 0;
         
            if( $round ){
               $couples = $round->NumberOfCouples;
               if( $couples )
                  $program[$index]->couples = $couples;
               else
                  $program[$index]->couples = false;
            }
            else
               $program[$index]->couples = false;
         }
      }
      return view('admin.programEdit')
         ->with('program', $program)
         ->with('action', $cmd)
         ->with('additionalRounds', $additionalRounds)
         ->with('layout',$layout[0]);
   }
    
   public function linkProgram(){
      if(Input::file('program_add') == null)
         return redirect('admin/program');
      $parsedProgram = $this->getCompressedProgram();
      $programAdd = $this->tournamentHelper->parseScheduleFile(Input::file('program_add'));
      $all = Round::all();
      $maxId = 0;
      foreach($all as $oneDance){
         if($oneDance->id > $maxId)
            $maxId = $oneDance->id;
      }
      //verify if added rounds are not repeated
      $programAddnew = [];
      $found = 0;
      if( $parsedProgram != null && $programAdd != null ){
         foreach($programAdd as $Additional){
            $found = 0;
            foreach($parsedProgram as $existProgram){
               if( trim($Additional->description) == trim($existProgram->description) ){
                  $found = 1;
                  break;
               }
            }
            if( $found == 0 ){
               $Additional->id = $maxId+1;
               $maxId = $maxId+1;
               $programAddnew[] = $Additional;
            }
         }
      }
      return view('admin.programAdd')
         ->with('program', $parsedProgram)
         ->with('programAdd', $programAddnew);
   }

   public function postAdditionalRound(){
      $all = Round::all();
      $maxId = 0;
      $layout = Layout::get();
      
      foreach($all as $oneDance){
         if($oneDance->id > $maxId)
            $maxId = $oneDance->id;
      }
      $program = $this->getCompressedProgram();
      $additionalRounds = [];
      $additionalRoundId = Input::get('additionalRoundId');
      $round = $this->tournamentHelper->getRound(intval($additionalRoundId));
      $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
      foreach($program as $programRound){
         if(mb_strpos($programRound->description, $name, 0, 'UTF-8') === false)
             continue;
         if(mb_strpos($programRound->description, $round->matchType, 0, 'UTF-8') !== false)
             continue;
         if(mb_strpos($programRound->description, 'Dodatkowa', 0, 'UTF-8') !== false)
             continue;
         if(mb_strpos($programRound->description, 'Baraż', 0, 'UTF-8') !== false)
             continue;

         $newRound = clone $programRound;
         $newRound->description = $newRound->description . ' ' . $round->matchType;
         $newRound->id = $maxId+1;
         $maxId = $maxId+1;
         $exists = false;
         foreach($program as $programRound2){
            if(mb_strpos($programRound2->description, $name, 0, 'UTF-8') !== false
               && mb_strpos($programRound2->description, $round->matchType, 0, 'UTF-8') !== false) {
                  $exists = true;
                  break;
            }
         }
         if($exists == false)
            $additionalRounds[] = $newRound;
      }
      return view('admin.programEdit')
            ->with('program', $program)
            ->with('additionalRounds', $additionalRounds)
            ->with('layout',$layout[0]);
   }
   
   public function postAddedRound(){
      $all = Round::all();
      $maxId = 0;
      $layout = Layout::get();
      
      foreach($all as $oneDance){
         if($oneDance->id > $maxId)
            $maxId = $oneDance->id;
      }
      $program = $this->getCompressedProgram();
      $additionalRounds = [];
      $roundName = Input::get('round');
      $category = Input::get('category');
      $additional = Input::get('additional');
      if($roundName == 'my')
         $roundName = Input::get('myround');
      if($roundName == 'sh_br')
      {
         $roundName = Input::get('mybreakshow_name');
         $category = '( '.Input::get('mybreakshow_dance').' )';  
      }
      
      $added = clone reset($program);
      if( (($pos = mb_strpos($category,'(')) !== false) ){ //found dances
         $added->description = $roundName.' '.trim(substr($category,0,$pos));
         foreach($program as $round){
            if( mb_strpos($round->description, $added->description, 0, 'UTF-8') !== false &&
                mb_strpos($round->description, $additional, 0, 'UTF-8') !== false){
               return redirect('admin/program');
            }
         }
         $added->roundName = $roundName;
         $added->alternative_description = "";
         $added->id = $maxId + 1;
         $added->groups = 1;
         $maxId = $maxId + 1;
         $dances = explode(' ',trim(substr($category, $pos, strlen($category)-$pos)," ()"));
         unset($added->dances);
         $temp_dances = [];
         if( count($dances) > 0 ){
            $cnt = count($dances);
            $added->dance = $dances[0];
            for($i=0;$i < $cnt; $i++ ){
              $temp_dances[] = ['dance' => $dances[$i], 'closed' => '0', 'danceId' => $i+1, 'order' => ''];
            }
            $added->dances = $temp_dances;
         }
      }
      if( mb_strpos(mb_strtoupper(trim($added->description),'UTF-8'),'KOMBINACJA') !== false ){ //found kombination, divide to STD and LAT
         if( count($added->dances)%2 === 0 ){ //event number but odd don't divide:)
            $new_round_la = clone $added;
            $dances_st = [];
            $dances_lt = [];
            for($i = 0; $i< count($added->dances)/2;$i++){
               $dances_st[] = $added->dances[$i];
               $dances_lt[] = $added->dances[$i+count($added->dances)/2];
            }
            $added->groups = 1;
            $added->dances = $dances_st;
            $new_round_la->dances = $dances_lt;
            $added->description = $added->description . ' ST';
            $new_round_la->description = $new_round_la->description . ' LA';
            $new_round_la->id = $maxId + 1;
            if( $additional != ' ' ){
               $new_round_la->matchType = $additional;
               $new_round_la->description = $new_round_la->description. ' ' . $additional;
               $new_round_la->isAdditional = true;
            }
            $additionalRounds[] = $new_round_la;
         }
      }
      if( $additional != ' ' ){
         $added->matchType = $additional;
         $added->description = $added->description. ' '.$additional;
         $added->isAdditional = true;
      }
      $additionalRounds[] = $added;

      return view('admin.programEdit')
            ->with('program', $program)
            ->with('additionalRounds', $additionalRounds)
            ->with('layout',$layout[0]);
   }


   public function saveParameters(){
      $layout = Layout::get();
      $program = $this->getCompressedProgram();

      $durationRound = Input::get('intDurationElm');
      $durationFinal = Input::get('intDurationFin');
      $parameter1 = Input::get('intDurationStart');
      $parameter2 = Input::get('intDurationEnd');
      
      if( $durationRound )
         DB::update('update layout set durationRound = ?', [$durationRound]);
      if( $durationFinal )
         DB::update('update layout set durationFinal = ?', [$durationFinal]);
      if( $parameter1 )
         DB::update('update layout set parameter1 = ?', [$parameter1]);
      if( $parameter2 )
         DB::update('update layout set parameter2 = ?', [$parameter2]);
      
      return view('admin.programEdit')
         ->with('program', $program)
         ->with('additionalRounds', NULL)
         ->with('layout',$layout[0]);
   }
   
   public function postSelectProgram(){
      if(Input::file('program') == null)
         return redirect('admin/program');
      $parsedProgram = $this->tournamentHelper->parseScheduleFile(Input::file('program'));
      return view('admin.programTemp')
         ->with('program', $parsedProgram)
         ->with('cmd', false);
   }

   public function postFinalProgram(){
      $this->resetProgramInLocalDB();

      $roundsNames = Input::get('roundName');
      $roundsAlternativeNames = Input::get('roundAlternativeName');
      $roundsIds = Input::get('roundId');
      $roundIsDance = Input::get('isDance');
      $groupsIds = Input::get('groupId');
      $startTime = Input::get('stTime');
      $durationRound = Input::get('intDurationElm');
      $durationFinal = Input::get('intDurationFin');
      $order = [];
      $data = [];
      $error = -1;

      if( $startTime )
         DB::update('update layout set startTime = ?', [$startTime]);
      if( $durationRound )
         DB::update('update layout set durationRound = ?', [$durationRound]);
      if( $durationFinal )
         DB::update('update layout set durationFinal = ?', [$durationFinal]);

      for($i = 0; $i< count($roundsIds);$i++){
         if($roundIsDance[$i] == "1" && Input::get($roundsIds[$i] . 'DanceName') != null) {
            foreach (Input::get($roundsIds[$i] . 'DanceName') as $danceName) {
               $round = [];
               $round['description'] = trim($roundsNames[$i]);
               $round['alternative_description'] = trim($roundsAlternativeNames[$i]);
               $round['isDance'] = true;
               $round['dance'] = $danceName;
               $round['closed'] = filter_var(Input::get($roundsIds[$i].$danceName), FILTER_VALIDATE_BOOLEAN) == 1 ? true : false;
               if(mb_strpos($roundsNames[$i], 'Dodatkowa', 0, 'UTF-8') !== false)
                  $round['type'] = "Dodatkowa";
               else if(mb_strpos($roundsNames[$i], 'Baraż', 0, 'UTF-8') !== false)
                  $round['type'] = "Baraż";
               else
                  $round['type'] = "";

               $danceOrder = Input::get("order" . $roundsIds[$i] . $danceName);
               $order[] = $danceOrder == NULL ? "" : $danceOrder;
               $round['groups'] = "1";
               if( $groupsIds )
                  $round['groups'] = $groupsIds[$i];
               else if( ($roundData = $this->tournamentHelper->getRound( trim($roundsNames[$i]))) != false )
               {
                  $dance = $this->tournamentHelper->getDanceCouples($roundData->roundId, $danceName, $error);
                  if( $dance !== false )
                     $round['groups'] = count($dance->couples);
               }
               array_push($data, $round);
            }
         }
         else{
            $round = [];
            $round['description'] = $roundsNames[$i];
            $round['alternative_description'] = trim($roundsAlternativeNames[$i]);
            $round['isDance'] = false;
            $round['closed'] = true;
            $round['dance']= "";
            $round['type'] = "";
            $round['groups'] = "1";
            $order[] = "";
            array_push($data, $round);;
         }
      }

      foreach($order as $key=>$value)
         if($value == "")
            unset($order[$key]);
      asort($order);
      $final = [];
      foreach($data as $index=>$value) {
         if(!array_key_exists($index, $order)) {
            $final[] = $value;
         }
         else if(count($order) > 0 && min($order) != 0) {
            foreach($order as $key=>$number) {
               $final[] = $data[$key];
               $order[$key] = 0;
            }
         }
      }
      Round::insert($final);
      return redirect('admin/program');
   }

   private function getRequiredVotes($round, $danceSign) {
      $error = -1;
      $votesRequired = $round->votesRequired;
      if ($round->isFinal){
         $groups = $this->tournamentHelper->getDanceCouples($round->roundId, $danceSign, $error);
         if(count($groups->couples) > 0)
            $votesRequired = count($groups->couples[0]);
      }
      return $votesRequired;
   }

   private function calculateJudgesForRound($round, $dance){
      $judgesForRound = $this->tournamentHelper->getJudges($round->roundId);

      foreach($judgesForRound as $judge){
         $votes = $this->tournamentHelper->getVotes($round->roundId, $judge->sign, $dance);
         $votesCount = 0;

         foreach ($votes as $vote) {
            if ($vote->note == 'X' or is_numeric($vote->note)) {
               $votesCount++;
            }
         }

         if($votesCount >= $this->getRequiredVotes($round, $dance)){
            $judge->completed = true;
         }
         else{
            $judge->completed = false;
         }
         $this->setJudgeStatus($judge);
         $judge->votes = $votes;
      }
      return $judgesForRound;
   }

   public function showCurrentRound(){
      return $this->showRound(0);
   }

   public function showRound($roundIdFromDB){
      $prevRoundIdFromDB = 0;
      $currentRoundIdFromDB = 0;
      $nextRoundIdFromDB = 0;

      $roundFromDB = Round::where('closed', '=', 0)->first();
      if($roundFromDB != null)
         $currentRoundIdFromDB = $roundFromDB->id;

      if($roundIdFromDB == $currentRoundIdFromDB)
         $roundIdFromDB = 0;

      $roundsFromDB = Round::where('isDance', '=', 1)->get();
      if($roundsFromDB != null) {
         for($i = 0; $i < count($roundsFromDB); $i++) {
            if($roundsFromDB[$i]->id == $roundIdFromDB || ($roundIdFromDB == 0 && $roundsFromDB[$i]->id == $currentRoundIdFromDB)) {
               $roundFromDB = $roundsFromDB[$i];
               if($i > 0)
                  $prevRoundIdFromDB = $roundsFromDB[$i-1]->id;
               if($i < count($roundsFromDB) - 1)
                  $nextRoundIdFromDB = $roundsFromDB[$i+1]->id;
               break;
            }
         }
      }
      $roundsToUndo = Round::where('isDance', '=', 1)->where('closed', '=', 1)->get();
      $roundDescription = "";
      $roundAlternativeDescription = "";

      $round = false;
      $couplesNo = false;
      $groups = false;
      $votes = false;
      $error = -1;
      if($roundFromDB != null){
         $round = $this->tournamentHelper->getRoundWithType($roundFromDB->description, $roundFromDB->type);
         $roundDescription = $roundFromDB->description;
         $roundAlternativeDescription = $roundFromDB->alternative_description;
         if( $round ){
            $dance = $this->tournamentHelper->getDanceCouples($round->roundId, $roundFromDB->dance, $error);
            $coupleNames = $this->tournamentHelper->getCouples($round->baseRoundId);
            //dd($roundFromDB->dance, $error);
            if( $dance !== false && count($dance->couples) ) {
               $couplesNo = 0;
               foreach($dance->couples as $group){
                  $couplesNo += count($group);
               }
               foreach($coupleNames as $key=>$Name){
                  $found = false;
                  foreach($dance->couples as $group){
                     foreach($group as $couple){
                        if($couple->number == $Name->number && $round->baseRoundId == $Name->roundId ){
                           $found = true;
                           break;
                        }
                     }
                  }
                  if( $found == false)
                     unset($coupleNames[$key]);
               }
               $groups = count($dance->couples);
               $votes = $round->votesRequired;
            }
            else if( $error == 0 ){
               $round = false;
               $roundAlternativeDescription = 'Brak tańca "'.$roundFromDB->dance.'" w rundzie.';
            }
            else{
               $round = false;
               $roundAlternativeDescription = 'Nie ustalona liczba grup (wyszarzone przyciski "Typy" i "Karty") lub nie wygenerowany podział na grupy w tej rundzie.';
            }
         }
         else{
            $roundAlternativeDescription = 'Brak zamkniętej lub zdefiniowanej rundy "'.$roundDescription.'" w strukturze turnieju.';
         }
      }
      else{
         $roundDescription = 'Koniec programu lub nie pobrany następny.';
         $roundAlternativeDescription = 'Wczytaj nowy program albo .. się pakuj Mistrzu, znów się udało :))';
      }
      $roundsToClose = Round::distinct()->where('isDance', '=', '1')->where('closed', '=', '1')->groupBy('description')->orderBy('id')->get();
      if($round !== false){
         $judgeRole = Role::where('name', 'judge')->first();
         $judgesForRound = $this->calculateJudgesForRound($round, $roundFromDB->dance);
         foreach( $judgesForRound as $judge ){
            $judge->without_pass = false;
            if( strlen($judge->firstName) && strlen($judge->lastName) ){//no empty names
               $judges = Role::find($judgeRole->id)->users()->where('username', '=', $judge->firstName.' '.$judge->lastName)->get();
               if( count($judges) > 0 ){
                  $judge->id = $judges[0]->id;
                  $to_compare = User::where('username', '=', $judge->firstName.' '.$judge->lastName)->get();
                  if( $to_compare[0]->created_at == $to_compare[0]->updated_at )
                     $judge->without_pass = true;
               }
            }
         }
         return view('admin.round')
            ->with('round', $round)
            ->with('roundDescription', $roundDescription)
            ->with('roundAlternativeDescription', $roundAlternativeDescription)
            ->with('roundsToClose', $roundsToClose)
            ->with('roundsToUndo', $roundsToUndo)
            ->with('localRoundId', $roundFromDB->id)
            ->with('danceName', $roundFromDB->dance)
            ->with('judges', $judgesForRound)
            ->with('roundIdFromDB', $roundIdFromDB)
            ->with('prevRoundIdFromDB', $prevRoundIdFromDB)
            ->with('nextRoundIdFromDB', $nextRoundIdFromDB)
            ->with('couples', $couplesNo)
            ->with('groups', $groups)
            ->with('votes', $votes)
            ->with('dance', $dance)
            ->with('names', $coupleNames);
      }
      else{
         return view('admin.round')
            ->with('round', null)
            ->with('roundDescription', $roundDescription)
            ->with('roundAlternativeDescription', $roundAlternativeDescription)
            ->with('roundsToClose', $roundsToClose)
            ->with('roundsToUndo', $roundsToUndo)
            ->with('localRoundId', $roundFromDB != null?$roundFromDB->id:null)
            ->with('danceName', $roundFromDB != null?$roundFromDB->dance:null )
            ->with('judges', [])
            ->with('roundIdFromDB', $roundIdFromDB)
            ->with('prevRoundIdFromDB', $prevRoundIdFromDB)
            ->with('nextRoundIdFromDB', $nextRoundIdFromDB)
            ->with('couples', null )
            ->with('groups', false)
            ->with('votes', false)
            ->with('dance', false)
            ->with('names', false);
      }
   }

   public function postCloseRound(){
      $roundId = Input::get('roundToClose');
      $result = true;
      $roundFromDB = Round::find($roundId);
      $round = $this->tournamentHelper->getRoundWithType($roundFromDB->description, $roundFromDB->type);
      if($round !== false)
         $result = $this->tournamentHelper->saveVotesToDatabase($round->roundId);
      if( $result )
         return redirect('admin/round')->with('success', 'Dane do rundy: '.$roundFromDB->description.' zostały zapisane poprawnie.');
      else
         return redirect('admin/round')->with('alert', 'Dane do rundy: '.$roundFromDB->description.' nie zostały zapisane.');
   }

   public function isNewRound(){
      return $this->getRoundResult(0);
   }

   public function getRoundResult($roundIdFromDB){
      if($roundIdFromDB <= 0)
         $roundFromDB = Round::where('closed', '=', 0)->first();
      else {
         $roundFromDB = null;
         $roundsFromDB = Round::where('isDance', '=', 1)->get();
         if($roundsFromDB != null) {
            foreach($roundsFromDB as $round) {
               if($round->id == $roundIdFromDB) {
                  $roundFromDB = $round;
                  break;
               }
            }
         }
      }
      $round = $this->tournamentHelper->getRoundWithType($roundFromDB->description, $roundFromDB->type);
      $judgesVotedNumber = 0;
      $judgesForRound = $this->calculateJudgesForRound($round, $roundFromDB->dance);
      $judgeRole = Role::where('name', 'judge')->first();
      foreach($judgesForRound as $judge){
         if($judge->completed == true){
            $judgesVotedNumber++;
         }
         $judge->without_pass = false;
         if( strlen($judge->firstName) && strlen($judge->lastName) ){//no empty names
            $judges = Role::find($judgeRole->id)->users()->where('username', '=', $judge->firstName.' '.$judge->lastName)->get();
            $judge->id = $judges[0]->id;
            $to_compare = User::where('username', '=', $judge->firstName.' '.$judge->lastName)->get();
            if( $to_compare[0]->created_at == $to_compare[0]->updated_at )
               $judge->without_pass = true;
         }
      }
      $judgesInRoundNumber = count($judgesForRound);
      if($judgesVotedNumber == $judgesInRoundNumber){ //all judges voted
         $roundFromDB->closed = true;
         $roundFromDB->save();
         return Response::json(["error" => "false", "newRound" => "true", "judges" => $judgesForRound]);
      }
      else{
         return Response::json(["error" => "false", "newRound" => "false", "judges" => $judgesForRound]);
      }
   }

   public function forceCloseDance($roundId){
      $roundFromDB = Round::find($roundId);
      $roundFromDB->closed = true;
      $roundFromDB->save();
      return redirect('admin/round');
   }

   public function undoRound(){
      $roundId = Input::get('roundToUndo');
      $judgeSign = Input::get('judgeToUndo');
      if($judgeSign == null)
         $judgeSign = "";

      $roundFromDB = Round::find($roundId);

      $round = $this->tournamentHelper->getRoundWithType($roundFromDB->description, $roundFromDB->type);

      if($round != false)
         $this->tournamentHelper->clearVotes($round->roundId, $roundFromDB->dance, $judgeSign);
      $roundFromDB->closed = false;
      $roundFromDB->save();
      return redirect('admin/round');
   }

   public function showHelp(){
      $rounds = $this->tournamentHelper->getRounds();
      return view('admin.help')
         ->with('rounds', $rounds);
   }

    
   public function showUtils($userId){
      $user = User::find($userId);
      $xmlVersion = simplexml_load_file('version.xml');
      $votes = $this->tournamentHelper->checkVotesFile();
      $rounds = $this->tournamentHelper->getRounds();
      $tournamentDirectory = iconv( 'CP1250', 'UTF-8', Cache::get('tournamentDirectory'));

      return view('admin.utils')
         ->with('user', $user)
         ->with('version', $xmlVersion)
         ->with('tournamentDirectory', $tournamentDirectory)
         ->with('votes', $votes)
         ->with('eventId', $this->tournamentHelper->getEventId())
         ->with('rounds', $rounds);
    }

    public function showReport(){
      $baseRounds = $this->tournamentHelper->getBaseRounds();
      $isManual = [];
      $classToModify = Config::get('ptt.classModifyResult');
      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      foreach($classToModify as $idx=>$class){
         $classToModify[$idx] = mb_strtoupper($class,'UTF-8');
      }

      foreach($baseRounds as $round) {
         $round->idx = 0;
         foreach($scheduleParts as $index=>$category){
            if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
               $round->className = 'H';
            $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            if( mb_strpos( mb_strtoupper($name,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               $round->positionW = $category->part;
               $round->idx = $index;
            }
         }
         if( !$round->positionW ) //undefined link to O block
            $round->positionW = '0';
      }
      if( $scheduleParts ){
         usort($baseRounds, function($a, $b) {
            if( $a->positionW == $b->positionW )
               return($a->idx > $b->idx);
            else
               return( $a->positionW > $b->positionW );
         });
      }
      foreach($baseRounds as $round) {
         if(in_array(mb_strtoupper($round->className,'UTF-8'), $classToModify))
            $isManual[] = true;
         else
            $isManual[] = false;
      }
      $result = $this->tournamentHelper->getEventId();
      if( empty( $result ) )
         return view('admin.report')
            ->with('baseRounds', $baseRounds)
            ->with('isManual', $isManual)
            ->with('eventId', 'Brak numeru imprezy w programie PTT. Wczytaj prawidłowy plik Baza.csv.')
            ->with('listyCSV', false );
      elseif( !$this->tournamentHelper->existListyCSV() )
         return view('admin.report')
            ->with('baseRounds', $baseRounds)
            ->with('isManual', $isManual)
            ->with('eventId', false )
            ->with('listyCSV', 'Brak pliku Listy_'.$result.'.csv w katalogu turnieju lub jest uszkodzony albo nieprawidłowy format.');
      else
         return view('admin.report')
            ->with('baseRounds', $baseRounds)
            ->with('isManual', $isManual)
            ->with('listyCSV', false )
            ->with('eventId', false );
   }

    private static function comparePosition($a, $b) {
      if($a->resultPosition == $b->resultPosition)
         return $a->number > $b->number ? 1 : -1; 
      if(intval($a->resultPosition) > intval($b->resultPosition))
         return 1;
      return -1;
   }

   public function showReportSet($roundId){
      $round = $this->tournamentHelper->getBaseRound(intval($roundId));
      $couples = $this->tournamentHelper->getCouples($round->roundId);

      usort($couples, [$this, "comparePosition"]);
      $positionsRange3 = Config::get('ptt.PositionRange_3');
      $positionsRange4 = Config::get('ptt.PositionRange_4');
      $positionswithHonour = Config::get('ptt.PositionRange_1withHonour');
      foreach($positionsRange3 as $idx=>$pos){
         $positionsRange3[$idx] = mb_strtoupper($pos,'UTF-8');
      }
      foreach($positionsRange4 as $idx=>$pos){
         $positionsRange4[$idx] = mb_strtoupper($pos,'UTF-8');
      }

      if( in_array( mb_strtoupper($round->className,'UTF-8' ), $positionsRange4 ) )
         $numberOfPositions = 4;
      else if( in_array(mb_strtoupper($round->className,'UTF-8'), $positionsRange3) )
         $numberOfPositions = 3;
      else
         $numberOfPositions = 2;

      return view('admin.reportSet')
         ->with('numberOfPositions', $numberOfPositions)
         ->with('withHonour', in_array(mb_strtoupper($round->className,'UTF-8'), $positionswithHonour))
         ->with('round', $round)
         ->with('couples', $couples);
   }

   public function setReport() {
      $numbers = Input::get('coupleNumber');
      $roundId = intval(Input::get('roundId'));
      $results = [];
      $table = 0;
      foreach($numbers as $number) {
         if(strncmp($number, 'position', 8) === 0)
            $table = intval($number[8]); 
         else {
            $result = new ManualResult();
            $result->roundId = $roundId;
            $result->coupleNumber = $number;
            $result->position = $table;
            $results[] = $result;
         }
      }
      $this->tournamentHelper->setManualResults($results);

      return redirect('admin/report');
   }

    public function generateReport() {
        $rounds = [];
        //$baseRounds = Input::get('roundId');
        $baseRounds = Input::old('roundId');
        if($baseRounds != null) {
            foreach($baseRounds as $round) {
                if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
                    $rounds[] = $round;
            }
        }
        if($this->tournamentHelper->createReportFile($rounds))
            return redirect()->back()->with('status', 'success');
        else
            return redirect()->back()->with('status', 'error');
    }
    
    public function reportRoundData(){
        
      $rounds = [];
      $baseRounds = Input::old('roundId');
        if($baseRounds != null) {
            foreach($baseRounds as $round) {
                if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
                    $rounds[] = $round;
            }
        }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $Program = [];
      $program_base = $this->getCompressedProgram();
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;

         $round->description = $name;
         $couples = $this->tournamentHelper->getCouples(intval($index));
         if( count($couples) > 0 ) //only defined rounds with selected couples
            $Program[] = $round;
      }

      foreach($Program as $index => $round){
         if( mb_strpos($round->roundName,'1/') !== false ){ //add next rounds, without final
            $round_no = intval(mb_substr($round->roundName,2,2));
            $round_copy = clone $round;
            unset($Program[$index]);
            $Program[] = $round_copy;
            //maybe redance for this round
            $name = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            foreach($program_base as $programRound){
               if(mb_strpos($programRound->description, $name, 0, 'UTF-8') === false)
                  continue;
               if(mb_strpos($programRound->description, 'Baraż', 0, 'UTF-8') !== false){
                  $round_redance = clone $round;
                  $round_redance->description = $round_redance->description.' Baraż';
                  $round_redance->baseNumberOfCouples = 0;
                  $Program[] = $round_redance;
                  break;
               }
            }
            do{
               $round_no = ($round_no/2);
               $new_round = clone $round_copy;
               if( $round_no == 1 )
                  break;//$new_round->roundName = 'Finał';
               else
                  $new_round->roundName = '1/'. $round_no .' Finału';
               $name = $new_round->roundName . ' ' . $round_copy->categoryName . ' ' . $round_copy->className . ' ' . $round_copy->styleName;
               $new_round->description = $name;
               $new_round->baseNumberOfCouples = 0;
               $Program[] = $new_round;
            }while( $round_no != 1);
         }
      }
      $data = array_values($Program);
      $Program = array_combine(array_keys($data), $data);
      return view('admin.reportRoundData')
            ->with('program', $Program);
    }

    public function reportCouples(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }
      $Program = [];
      $Couples = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $couples = $this->tournamentHelper->getCouples($round->baseRoundId);
         usort($couples, function($a, $b) {
            return( intval($a->number) > intval($b->number) );
         });
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Kombinacja";
         }
         $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $round->description = $name;
         $Program[] = $round;
         $Couples[] = $couples;
      }

      $data = array_values($Program);
      $Program = array_combine(array_keys($data), $data);
      return view('admin.reportCouples')
            ->with('program', $Program)
            ->with('couples', $Couples);
    }

    public function reportClubs(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      $PartsNo = [];
      $PartsStr = "BLOKU ";

      $clubs = [];
      foreach($rounds as $index){
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         if( $round ){
            $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            foreach($scheduleParts as $category){
               if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                  if( !in_array($category->part,$PartsNo)){
                     $PartsNo[] = $category->part;
                     if( count($PartsNo) == 1 )
                        $PartsStr .= $category->part;
                     else
                        $PartsStr .= ', '.$category->part;
                  }
               }
            }
            $couples = $this->tournamentHelper->getCouples($round->baseRoundId);
            if(count($couples) == 0 )
               continue;
            else{
               foreach($couples as $couple){
                  if( count($clubs) == 0 ){
                     $new_club = new Club();
                     $new_club->club = $couple->club;
                     $new_club->country = $couple->country;
                     $clubs[] = $new_club;
                     continue;
                  }
                  $found = false;
                  foreach( $clubs as $club){
                     if( mb_strtoupper($couple->club,'UTF-8') == mb_strtoupper($club->club,'UTF-8')){
                        if( $couple->country == '' )
                           $found = true;
                        elseif( $club->country == ''){
                           $club->country = $couple->country;
                           $found = true;
                        }
                        elseif( $club->country != $couple->country )//two clubs from different countries??
                           break;
                        else{
                           $found = true;
                           break;
                        }
                     }
                  }
                  if( $found == false){
                        $new_club = new Club();
                        $new_club->club = $couple->club;
                        $new_club->country = $couple->country;
                        $clubs[] = $new_club;
                  }
               }
            }
         } //if
      } //foreach
      asort($clubs);
      return view('admin.reportClubs')
            ->with('clubs', $clubs)
            ->with('parts', $PartsStr);
   }

    public function reportOpenClubs(){
      $baseRounds = Input::old('roundId');
      $roundsFromPTT = $this->tournamentHelper->getRounds();

      $rounds = [];
      if($baseRounds != null) {
         foreach($baseRounds as $index) {
            if(filter_var(Input::old($index), FILTER_VALIDATE_BOOLEAN) == 1){
               $round = $this->tournamentHelper->getBaseRound(intval($index));
               $rounds[] = $round->baseRoundId;
            }
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }
      if( count($roundsFromPTT) == 0 ){
         return redirect('admin/report');
      }

      $clubs = [];
      foreach( $roundsFromPTT as $ptt ){
         if( $ptt->isClosed == 0 ){
            $found = false;
            foreach($rounds as $baseId){
               if( $ptt->baseRoundId == $baseId ){
                  $found = true;
                  break;
               }
            }
            if( $found == true ){
               $coupleNames = $this->tournamentHelper->getCouplesInRound( $ptt );
               if( count($coupleNames) > 0 ){
                  foreach($coupleNames as $couple){
                     $found = false;
                     foreach( $clubs as $club){
                        if( mb_strtoupper($couple->club,'UTF-8') == mb_strtoupper($club->club,'UTF-8') ){
                           if( $couple->country == '' )
                              $found = true;
                           elseif( $club->country == ''){
                              $club->country = $couple->country;
                              $found = true;
                           }
                           elseif( $club->country != $couple->country )//two clubs from different countries??
                              break;
                           else{
                              $found = true;
                              break;
                           }
                        }
                     }
                     if( $found == false){
                        $new_club = new Club();
                        $new_club->club = $couple->club;
                        $new_club->country = $couple->country;
                        $clubs[] = $new_club;
                     }
                  }
               }
            }
         }
      }
      asort($clubs);
      return view('admin.reportClubsOpen')
            ->with('clubs', $clubs);
   }


    public function reportLists(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      $categories = [];
      $lists = [];
      $Couples = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $key = array_search($round->categoryName.' '.$round->className, $categories, true);
         if( $key == false ){//not found
               $description = $round->categoryName.' '.$round->className;
               $categories[$index] = $description;
               $lists[$index] = 0;
         }
         else {
            $lists[$key] = intval($index);
         }
      }
      $tempArr = [];
      $cpl_1 = [];
      $cpl_2 = [];
      $pages=0;
      foreach($lists as $index=>$add_style){
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         if($add_style != 0){
            $order = false;
            $round1 = $this->tournamentHelper->getBaseRound(intval($add_style));
            $round1->description = $round1->categoryName . ' ' . $round1->className . ' ' . $round1->styleName;
            //try set standard as first
            if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'ST') !== false ){
               $cpl_1 = $this->tournamentHelper->getCouples($round->baseRoundId);
               $cpl_2 = $this->tournamentHelper->getCouples($round1->baseRoundId);
               $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName . ' / ' . $round1->styleName;
            }
            else{
               $order = true;
               $cpl_1 = $this->tournamentHelper->getCouples($round1->baseRoundId);
               $cpl_2 = $this->tournamentHelper->getCouples($round->baseRoundId);
               $name = $round->categoryName . ' ' . $round->className . ' ' . $round1->styleName . ' / ' . $round->styleName;
            }
            if( count($cpl_1) == 0 && count($cpl_2) == 0 )
               continue;
            //set standard as '1'
            foreach($cpl_1 as $style){
               $style->marker = '1';
            }
            //set latin as '2'
            foreach($cpl_2 as $style){
               $style->marker = '2';
            }
            $PartsStr = ' [ BLOK ';
            $both = '';
            if( $order == false ){
               foreach($scheduleParts as $category){
                  if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                     $PartsStr .= $category->part;
                     $both = $category->part;
                  }
               }
               foreach($scheduleParts as $category){
                  if( mb_strpos( mb_strtoupper($round1->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                    if( $both != $category->part)
                       $PartsStr .= ','.$category->part.' ]';
                    else
                       $PartsStr .= ' ]';
                  }
               }
            }else{
               foreach($scheduleParts as $category){
                  if( mb_strpos( mb_strtoupper($round1->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                     $PartsStr .= $category->part;
                     $both = $category->part;
                  }
               }
               foreach($scheduleParts as $category){
                  if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                    if( $both != $category->part)
                       $PartsStr .= ','.$category->part.' ]';
                    else
                       $PartsStr .= ' ]';
                  }
               }
            }
            $Couples[$name] = array_merge($cpl_1,$cpl_2);
            usort($Couples[$name], function($a, $b) {
               return( intval($a->number) > intval($b->number) );
            });
            unset( $tempArr );
            $tempArr = [];
            foreach($Couples[$name] as $index=>$couple){
               if( !in_array($couple->plIdA.$couple->plIdB, $tempArr) ){
                  $tempArr[] = $couple->plIdA.$couple->plIdB;
               }
               else{//found the same Id + Id, mark as both styles and remove second
                  foreach($Couples[$name] as $cpl){
                     if($cpl->plIdA == $couple->plIdA && $cpl->plIdB == $couple->plIdB){
                        if( $cpl->marker == '1' ){
                           $cpl->number2 = $couple->number;
                        }
                        else{
                           $cpl->number2 = $cpl->number;
                           $cpl->number = $couple->number;
                        }
                        $cpl->marker = '3';
                        if( $cpl->lastNameA == $couple->lastNameA && $cpl->lastNameB == $couple->lastNameB && $cpl->club != $couple->club)
                           $cpl->country = $couple->club;
                     }
                  }
                  unset($Couples[$name][$index]); //delete repeated couple
               }
            }
            //dd($Couples[$name]);
            /*usort($Couples[$name], function($a, $b) {
               if( !is_null($a->number) && !is_null($a->number2) && !is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number) > intval($b->number) || intval($a->number2) > intval($b->number2) );
               else if( !is_null($a->number) && !is_null($a->number2) && !is_null($b->number) && is_null($b->number2) )
                  return( intval($a->number) > intval($b->number) );
               else if( !is_null($a->number) && !is_null($a->number2) && is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number2) );
               else if( !is_null($a->number) && is_null($a->number2) && !is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number) > intval($b->number) );
               else if( is_null($a->number) && !is_null($a->number2) && !is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number2) );
               else if( is_null($a->number) && !is_null($a->number2) && is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number) );
               else if( is_null($a->number) && !is_null($a->number2) && is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number) );
               else if( is_null($a->number) && !is_null($a->number2) && !is_null($b->number) && is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number) );
               else if( !is_null($a->number) && is_null($a->number2) && is_null($b->number) && !is_null($b->number2) )
                  return( intval($a->number2) > intval($b->number) );
            });*/
            //dd($Couples[$name]);
            $Couples[$name] = array_values($Couples[$name]);
            $Couples[$name][0]->NoCpl1 = count($cpl_1);
            $Couples[$name][0]->NoCpl2 = count($cpl_2);
            $Couples[$name][0]->section = $PartsStr;
            $cpl_new = new Couple();
            $cpl_new->lastNameA = $cpl_new->firstNameA = "";
            $cpl_new->lastNameB = $cpl_new->firstNameB = "";
            $cpl_new->club = $cpl_new->country = $cpl_new->number = "";
            $cpl_new->marker = $Couples[$name][0]->marker;
            //add two empty raws 
            $Couples[$name][] = $cpl_new;
            $Couples[$name][] = $cpl_new;
         }
         else{
            $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            $cpl_1 = $this->tournamentHelper->getCouples($round->baseRoundId);
            if(count($cpl_1) == 0 )
               continue;
            $PartsStr = ' [ BLOK ';
            foreach($scheduleParts as $category){
               if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
                  $PartsStr .= $category->part;
               }
            }
            $Couples[$name] = $cpl_1;
            $cpl_new = new Couple();
            $cpl_new->lastNameA = $cpl_new->firstNameA = "";
            $cpl_new->lastNameB = $cpl_new->firstNameB = "";
            $cpl_new->club = $cpl_new->country = $cpl_new->number = "";
            $cpl_new->marker = $Couples[$name][0]->marker;
            //add two empty raws 
            $Couples[$name][0]->NoCpl1 = count($cpl_1);
            $Couples[$name][0]->section = $PartsStr.' ]';
            $Couples[$name][] = $cpl_new;
            $Couples[$name][] = $cpl_new;
         }
      }
      return view('admin.reportLists')
            ->with('couples', $Couples)
            ->with('pages', $pages);
   }

    public function reportCouplesConflict(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $categories = [];
      $lists = [];
      $Couples = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $key = array_search($round->categoryName.' '.$round->className, $categories, true);
         if( $key == false ){//not found
               $description = $round->categoryName.' '.$round->className;
               $categories[$index] = $description;
               $lists[$index] = 0;
         }
         else {
            $lists[$key] = intval($index);
         }
      }
      $tempArr = [];
      $cpl_1 = [];
      $cpl_2 = [];
      $allCpls = [];
      foreach($lists as $index=>$add_style){
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         if($add_style != 0){
            $round1 = $this->tournamentHelper->getBaseRound(intval($add_style));
            $round1->description = $round1->categoryName . ' ' . $round1->className . ' ' . $round1->styleName;
            //try set standard as first
            if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'ST') !== false ){
               $cpl_1 = $this->tournamentHelper->getCouples($round->baseRoundId);
               $cpl_2 = $this->tournamentHelper->getCouples($round1->baseRoundId);
               $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName . ' , ' . $round1->styleName;
            }
            else{
               $cpl_1 = $this->tournamentHelper->getCouples($round1->baseRoundId);
               $cpl_2 = $this->tournamentHelper->getCouples($round->baseRoundId);
               $name = $round->categoryName . ' ' . $round->className . ' ' . $round1->styleName . ' , ' . $round->styleName;
            }
            if( count($cpl_1) == 0 && count($cpl_2) == 0 )
               continue;
            //set standard as '1'
            foreach($cpl_1 as $style){
               $style->marker = '1';
            }
            //set latin as '2'
            foreach($cpl_2 as $style){
               $style->marker = '2';
            }
            $Couples[$name] = array_merge($cpl_1,$cpl_2);
            usort($Couples[$name], function($a, $b) {
               return( intval($a->number) > intval($b->number) );
            });
            unset( $tempArr );
            $tempArr = [];
            foreach($Couples[$name] as $index=>$couple){
               if( !in_array($couple->number, $tempArr) ){
                  $tempArr[] = $couple->number;
               }
               else{//found the same number, mark as both styles and remove second
                  foreach($Couples[$name] as $cpl){
                     if($cpl->number == $couple->number){
                        $cpl->marker = '3'; //both styles
                        if( $cpl->lastNameA == $couple->lastNameA && $cpl->lastNameB == $couple->lastNameB && $cpl->club != $couple->club)
                           $cpl->country = $couple->club;
                     }
                  }
                  unset($Couples[$name][$index]); //delete repeated number
               }
            }
            $Couples[$name] = array_values($Couples[$name]);
            foreach( $Couples[$name] as $couple){
               if($couple->marker == '1')
                  $couple->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
               elseif($couple->marker == '2')
                  $couple->description = $round->categoryName . ' ' . $round->className . ' ' . $round1->styleName;
               else
                  $couple->description = $name;
               $allCpls[] = $couple;
            }
         }
         else{
            $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            $cpl_1 = $this->tournamentHelper->getCouples($round->baseRoundId);
            if(count($cpl_1) == 0 )
               continue;
            $Couples[$name] = $cpl_1;
            foreach( $Couples[$name] as $couple){
               $couple->description = $name;
               $allCpls[] = $couple;
            }
         }
      }

      //try fnnd couples with different styles
      $conflict = $allCpls;
      foreach( $conflict as $index=>$remove){
         $first=0;
         $found=false;
         foreach( $allCpls as $couple ){
            if( $remove->number == $couple->number ){
               if( $first == 0 )
                  $first++;
               else
                  $found = true; //found in another style
            }
         }
         if( $found == false )
            unset( $conflict[$index] ); //remove from list
      }
      usort($conflict, function($a, $b) {
         if( $a->number >= $b->number )
            return( 1 );
         else
            return( -1 );
      });
      unset($Couples);
      $Couples = [];
      $temp = [];
      foreach( $conflict as $couple ) {
         if( in_array( $couple->number, $temp ) ){
            if( mb_strpos(mb_strtoupper(trim($Couples[ $couple->number ]),'UTF-8'),'LAT') !== false  && 
                mb_strpos(mb_strtoupper(trim($couple->description),'UTF-8'),'ST') !== false )
               $Couples[ $couple->number ] = $couple->description.' / '.$Couples[ $couple->number ];
            else
               $Couples[ $couple->number ] .= ' / '.$couple->description;
         }
         else{
            $temp[] = $couple->number;
            $Couples = array_add( $Couples, $couple->number , $couple->description );
         }
      }
      if( count($Couples) == 0 ) //no couples
         return redirect('admin/report')->with('conflict', 'Brak par tańczących w róznych stylach !!');

      return view('admin.reportCouplesBr')
            ->with('couples', $Couples);
   }

   public function reportListsRange(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $Program = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Komb";
         }
         if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
            $round->className = 'H';
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $Program = array_add( $Program, $round->baseRoundId, $round );
      }

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      $start = 1000;
      $finish = 1;
      foreach($Program as $round){
         $round->idx = 0;
         foreach($scheduleParts as $index=>$category){
            if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               $round->positionW = $category->part;
               $round->idx = $index;
            }
         }
         if( !$round->positionW ) //undefined link to O block
             $round->positionW = '0';
         if( $round->startNo < $start )
            $start = $round->startNo;
         if( $round->endNo > $finish )
            $finish = $round->endNo;
      }
      if( $scheduleParts ){
         usort($Program, function($a, $b) {
            if( $a->positionW == $b->positionW )
               return($a->idx > $b->idx);
            else
               return( $a->positionW > $b->positionW );
         });
      }

      $couples = $this->tournamentHelper->getCouplesCSV();
      //count no of couples for each category
      foreach($Program as $category){
         $category->baseNumberOfCouples = 0;
         foreach($couples as $couple){
            if( mb_strpos( mb_strtoupper($couple->roundId,'UTF-8'), mb_strtoupper($category->description,'UTF-8')) !== false )
               $category->baseNumberOfCouples += 1;
         }
      }
      return view('admin.reportRanges')
            ->with('lists', $Program)
            ->with('start', $start)
            ->with('finish', $finish)
            ->with('eventId', $this->tournamentHelper->getEventId());
   }

   public function postRanges(){
      $range_start = Input::get('main_start_no'); //start number
      $range_end = Input::get('main_end_no'); //end number
      $lack = Input::get('lack_no'); //list of lack numbers
      $blocks = Input::get('blockId'); //list of parts
      $block_no = Input::get('block_no'); //list of parts
      $categories = Input::get('roundName'); // rounds name list 
      $roundIds = Input::get('roundId'); // rounds Id list 
      $start_no = Input::get('start_no'); // list of starts numbers
      $number_same = Input::get('agree');
      $free_places = Input::get('free_places');

      if( !is_numeric($range_start) )
         $range_start = 1; //default
      if( !is_numeric($range_end) )
         $range_end = 200; //default

      $Program = [];
      foreach($roundIds as $index=>$roundId){ 
         $round = $this->tournamentHelper->getBaseRound(intval($roundId));
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Komb";
         }
         if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
            $round->className = 'H';
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $round->nGroupsW = 1000;
         $round->nDancesW = $round->endNo;
         if( is_numeric($start_no[$index]) ){ //setting up start value
               $round->nGroupsW = $start_no[$index];
         }
         if( $round->nDancesW > $range_end )
            $round->nDancesW = $range_end;
         $Program = array_add( $Program, $round->baseRoundId, $round );
      }

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      foreach($Program as $round) {
         $round->idx = 0;
         foreach($scheduleParts as $index=>$category){
            if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               $round->positionW = $category->part;
               $round->idx = $index;
            }
         }
         if( !$round->positionW )
            $round->positionW = '0';
      }

      if( $scheduleParts ){
         usort($Program, function($a, $b) {
            if( $a->positionW == $b->positionW )
               if( $a->nGroupsW == $b->nGroupsW && $a->nGroupsW != null && $b->nGroupsW != null )
                  return( $a->idx > $b->idx );
               else
                  return( $a->nGroupsW > $b->nGroupsW );
            else
               return( $a->positionW > $b->positionW );
         });
      }

      foreach($Program as $round) {
         if( $round->nGroupsW == 1000 )
            $round->nGroupsW = $range_start;
         //maybe should change start according to block start
         if( ($key = array_search( $round->positionW, $blocks )) !== false ){
            if( is_numeric($block_no[$key]) ){ 
               if( $block_no[$key] >  $round->nGroupsW )
                  $round->nGroupsW = $block_no[$key];
            }
         }
      }

      $lists = $this->tournamentHelper->getCouplesCSV();
      if( !$lists )
         Session::flash('status', 'error');

      $start = false;
      $end = false;
      $parts = explode(',',$lack);
      $table_no = [];
      $not_allowed = [];
      foreach($parts as $part){
         $not_allowed[] = trim($part);
      }
      foreach($blocks as $index=>$block){
         $create_table = false;
         if( $index == 0) //for first block always create
            $create_table = true;
         $start = $range_start;
         $end = $range_end;
         if( is_numeric($block_no[$index]) ){//if set up new range, create new table
            $start = intval($block_no[$index])>$range_start?intval($block_no[$index]):$range_start;
            $create_table = true;
         }

         if( $create_table ){//build new table with allowed number
            unset( $table_no );
            $table_no = [];
            for( $idx = $start; $idx <= $end; $idx++ ){
               if( in_array($idx,$not_allowed) )
                  continue;
               else
                  $table_no[] = $idx;
            }
         }
         $wrap = false;
         foreach($Program as $round) {
            if( $round->positionW != $block )
               continue;
            if( $wrap == true )//start again 
               $round->nGroupsW = $range_start;
            $found = false;
            $categories = [];
            foreach($lists as $couple){
               if( mb_strpos(mb_strtoupper($couple->roundId,'UTF-8'),mb_strtoupper($round->description,'UTF-8')) !== false ){
                  if( $couple->number != 0 ){
                     $categories[] = $couple;
                     continue;
                  }
                  while( true ){
                     if( ($key = array_search($round->nGroupsW, $table_no )) === false ){
                        if( $round->nGroupsW < $round->nDancesW )
                           $round->nGroupsW += 1;
                        else
                           $round->nGroupsW = ($round->startNo > $range_start ? $round->startNo : $range_start);
                        if( count($table_no) == 0 )//no free number
                           break;
                        continue;
                     }
                     $found = true;
                     $couple->number = $round->nGroupsW;
                     $categories[] = $couple;
                     //find this couple in another category in the same part of competition
                     foreach($lists as $pair){
                        if( $number_same == 'yes' || ($number_same != 'yes' && $pair->section == $block ) ){
                           if( $pair->plIdA == $couple->plIdA &&
                               $pair->plIdB == $couple->plIdB &&
                               $pair->number != $couple->number )
                               $pair->number = $couple->number;
                        }
                        else
                           continue;
                     }
                     if( $round->nGroupsW < $round->nDancesW ){
                        $round->nGroupsW += 1;
                        unset($table_no[$key]); //remove busy number from list
                     }
                     else{
                        $round->nGroupsW = ($round->startNo > $range_start ? $round->startNo : $range_start);
                        $wrap = true;
                        unset( $table_no );
                        $table_no = [];
                        for( $idx = $range_start; $idx <= $end; $idx++ ){
                           if( in_array($idx,$not_allowed) )
                              continue;
                           else
                              $table_no[] = $idx;
                        }
                     }
                     break;
                  }
               }
            }
            if( $found == true && mb_strpos(mb_strtoupper($round->styleName,'UTF-8'),'ST') === false){ //reserved two numbers for each category but no ST
               for($free_no=0;$free_no<$free_places;$free_no++){
                  if( ($key = array_search($round->nGroupsW, $table_no )) !== false )
                     unset($table_no[$key]);
                  if( $round->nGroupsW < $round->nDancesW ){
                     $round->nGroupsW += 1;
                  }
                  else{
                     $round->nGroupsW = ($round->startNo > $range_start ? $round->startNo : $range_start);
                     $wrap = true;
                     unset( $table_no );
                     $table_no = [];
                     for( $idx = $range_start; $idx <= $end; $idx++ ){
                        if( in_array($idx,$not_allowed) )
                           continue;
                        else
                           $table_no[] = $idx;
                     }
                     break;
                  }
               }
            }
            if( count($categories) )
               $this->tournamentHelper->SaveCouples2CSV( $categories, $round );
            unset($categories);
         }
      }
      return redirect('admin/report');
   }

   public function reportResults() {
      $rounds = [];
      $baseRounds = Input::old('roundId');

      $classToModify = Config::get('ptt.classModifyResult');
      $positionsRange = Config::get('ptt.PositionRange_3');
      $positionswithHonour = Config::get('ptt.PositionRange_1withHonour');
      
      foreach($classToModify as $idx=>$class){
         $classToModify[$idx] = mb_strtoupper($class,'UTF-8');
      }
      foreach($positionsRange as $idx=>$pos){
         $positionsRange[$idx] = mb_strtoupper($pos,'UTF-8');
      }
      foreach($positionswithHonour as $idx=>$honour){
         $positionswithHonour[$idx] = mb_strtoupper($honour,'UTF-8');
      }
      
      if($baseRounds != null) {
         foreach($baseRounds as $index) {
            if(filter_var(Input::old($index), FILTER_VALIDATE_BOOLEAN) == 1){
               $round = $this->tournamentHelper->getBaseRound(intval($index));
               if( in_array(mb_strtoupper($round->className,'UTF-8'), $classToModify) )//only 'special' classes to modify
                  $rounds[] = $index;
            }
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $Couples = [];
      $Numbers = [];
      $couple = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Kombinacja";
         }

         $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $couple = $this->tournamentHelper->getCouples($round->baseRoundId);
         $manual = false;
         if(count($couple) == 0 )
            continue;
         
         foreach($couple as $index=>$cpl){ 
            $remove = false;
            if( $cpl->manualPosition == 0 )
               $remove = true;
            else{ //modified manually
               if( in_array( mb_strtoupper($round->className,'UTF-8'), $positionswithHonour) )
                  $cpl->manualPosition = ($cpl->manualPosition == 2 ? 'I' : 'I z wyróżnieniem');
               else
                  $cpl->manualPosition = ($cpl->manualPosition == 4 ? 'IV':($cpl->manualPosition == 3 ? 'III' : ($cpl->manualPosition == 2 ? 'II' : 'I')));
            }
            if( $remove )
               unset($couple[$index]);
         }
         usort($couple, function($a, $b) {
            if( $a->manualPosition == $b->manualPosition )
               return( $a->number > $b->number );
            else{
               if( $a->manualPosition == 'I z wyróżnieniem' )
                  return 1;
               else if( $b->manualPosition == 'I z wyróżnieniem' )
                  return -1;
               return( $a->manualPosition < $b->manualPosition );
            }
         });
         $Couples[$name] = $couple;
         $Numbers[$name] = $round->baseNumberOfCouples;
      }

      return view('admin.reportResults')
            ->with('couples', $Couples)
            ->with('Numbers', $Numbers);
   }
    
   public function reportResultsShort() {
      $rounds = [];
      $baseRounds = Input::old('roundId');

      $classToModify = Config::get('ptt.classModifyResult');
      $positionsRange = Config::get('ptt.PositionRange_3');
      $positionswithHonour = Config::get('ptt.PositionRange_1withHonour');
      
      foreach($classToModify as $idx=>$class){
         $classToModify[$idx] = mb_strtoupper($class,'UTF-8');
      }
      foreach($positionsRange as $idx=>$pos){
         $positionsRange[$idx] = mb_strtoupper($pos,'UTF-8');
      }
      foreach($positionswithHonour as $idx=>$honour){
         $positionswithHonour[$idx] = mb_strtoupper($honour,'UTF-8');
      }
      
      if($baseRounds != null) {
         foreach($baseRounds as $index) {
            if(filter_var(Input::old($index), FILTER_VALIDATE_BOOLEAN) == 1){
               $round = $this->tournamentHelper->getBaseRound(intval($index));
               if( in_array(mb_strtoupper($round->className,'UTF-8'), $classToModify) )//only 'special' classes to modify
                  $rounds[] = $index;
            }
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $Couples = [];
      $Numbers = [];
      $couple = [];
      foreach($rounds as $index){ 
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Kombinacja";
         }
         $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $couple = $this->tournamentHelper->getCouples($round->baseRoundId);
         $manual = false;
         if(count($couple) == 0 )
            continue;
         
         foreach($couple as $index=>$cpl){ 
            $remove = false;
            if( $cpl->manualPosition == 0 )
               $remove = true;
            else{ //modified manually
               if( in_array(mb_strtoupper($round->className,'UTF-8'), $positionswithHonour) )
                  $cpl->manualPosition = ($cpl->manualPosition == 2 ? 'I' : 'I z wyróżnieniem');
               else
                  $cpl->manualPosition = ($cpl->manualPosition == 4 ? 'IV':($cpl->manualPosition == 3 ? 'III' : ($cpl->manualPosition == 2 ? 'II' : 'I')));
            }
            if( $remove )
               unset($couple[$index]);
         }
         usort($couple, function($a, $b) {
            if( $a->manualPosition == $b->manualPosition )
               return( $a->number > $b->number );
            else{
               if( $a->manualPosition == 'I z wyróżnieniem' )
                  return 1;
               else if( $b->manualPosition == 'I z wyróżnieniem' )
                  return -1;
               return( $a->manualPosition < $b->manualPosition );
            }
         });   
         $idx = false;
         $complete = [];
         foreach($couple as $position){
            if( $idx != $position->manualPosition ){
               $idx = $position->manualPosition;
               $complete[$position->manualPosition] = $position->number;
            }
            else
               $complete[$position->manualPosition] = $complete[$position->manualPosition].', '.$position->number;
         }         
         $Couples[$name] = $complete;
         $Numbers[$name] = $round->baseNumberOfCouples;
         unset($complete);
      }
      return view('admin.reportResultsShort')
            ->with('couples', $Couples)
            ->with('Numbers', $Numbers);
   }

      private function convert_dance($shortName) {
         $replaceDance = Config::get('ptt.replaceDance');
      return strtr($shortName, $replaceDance);
   }

   public function reportTrainee(){
      $baseRounds = Input::old('roundId');
      $roundsFromDB = Round::where('closed', '=', 0)->get();

      $rounds = [];
      if($baseRounds != null) {
         foreach($baseRounds as $index) {
            if(filter_var(Input::old($index), FILTER_VALIDATE_BOOLEAN) == 1){
               $round = $this->tournamentHelper->getBaseRound(intval($index));
               $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
               $rounds[] = $round;
            }
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/report');
      }

      $round2print = [];
      $danceNames = [];
      $couples = [];
      $couplesNo = [];
      $print = false;
      $error = -1;
      for($pos=0; $pos < count($roundsFromDB);$pos++)
      {
         $round2print[$pos] = false;
         $danceNames[$pos] = false;
         $couples[$pos] = false;
         $couplesNo[$pos] = false;
         $votesNo[$pos] =  false;
         if($roundsFromDB != null && (count($roundsFromDB) > $pos ) )
         {
            $found = false;
            foreach($rounds as $round){
               if( mb_strpos( $roundsFromDB[$pos]->description,$round->description) !== false ){
                  $found = true;
               }
            }
            if( $found == true ){
               $round2print[$pos] = $this->tournamentHelper->getRoundWithType($roundsFromDB[$pos]->description, $roundsFromDB[$pos]->type);
               if($round2print[$pos] !== false) {
                  $dance = $this->tournamentHelper->getDanceCouples($round2print[$pos]->roundId, $roundsFromDB[$pos]->dance, $error);
                  if($dance !== false && count($dance->couples)) {
                     //$danceNames[$pos] = $this->convert_dance($roundsFromDB[$pos]->dance);
                     $danceNames[$pos] = $roundsFromDB[$pos]->dance;
                     $group_over18 = false;
                     $range = 18;
                     foreach($dance->couples as $index=>$group){
                        asort($dance->couples[$index]);
                        $couplesNo[$pos] += count($dance->couples[$index]);
                        foreach($group as $couple){
                           if( $couple->number > 99 )
                              $range = 15;
                        }
                        if( count($dance->couples[$index]) > $range ) //more than 15 couples in group
                           $group_over18 = true;

                     }
                     if( $group_over18 == true ){
                        $new_dance = new Dance();
                        $add = 0;
                        foreach($dance->couples as $index=>$group){
                           if(count($dance->couples[$index]) > $range){
                              for( $idx=0; $idx < $range; $idx++ ){
                                 $new_dance->couples[$index][] = $group[$idx];
                              }
                              $add++;
                              for( $idx=0; $idx < count($dance->couples[$index])-$range; $idx++ ){
                                 $new_dance->couples[$index.'_'][] = $group[$range+$idx];
                              }
                           }
                           else
                              $new_dance->couples[$index+$add] = $dance->couples[$index]; 
                        }
                        $couples[$pos] = $new_dance->couples;
                     }
                     else
                        $couples[$pos] = $dance->couples;
                     $print = true;
                  }
               }
            }
         }
      }
      if( $print == false ){
         return redirect('admin/report');
      }

      return view('admin.reportTrainee')
               ->with('rounds', $round2print)
               ->with('danceNames', $danceNames)
               ->with('couples', $couples)
               ->with('couplesNo', $couplesNo);
   }


   public function postReport(){
      if( Input::has('rounds') ){
         return redirect('admin/reportRoundData')->withInput();
      }
      else if( Input::has('couples') ){
         return redirect('admin/reportCouples')->withInput();
      }
      else if( Input::has('lists') ){
         return redirect('admin/reportLists')->withInput();
      }
      else if( Input::has('couplesBr') ){
         return redirect('admin/reportCouplesConflict')->withInput();
      }
      else if( Input::has('ranges') ){
         return redirect('admin/reportListsRange')->withInput();
      }
      else if( Input::has('clubs') ){
         return redirect('admin/reportClubs')->withInput();
      }
      else if( Input::has('clubsOpen') ){
         return redirect('admin/reportOpenClubs')->withInput();
      }
      else if( Input::has('results_f') ){
         return redirect('admin/reportResults')->withInput();
      }
      else if( Input::has('results_s') ){
         return redirect('admin/reportResultsShort')->withInput();
      }      
      else if( Input::has('trainee') ){
         return redirect('admin/reportTrainee')->withInput();
      }
      else{
         return redirect('admin/generateReport')->withInput();
      }
   }

   public function showPanel(){
      $baseRounds = $this->tournamentHelper->getBaseRounds();
      $isManual = [];
      
      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      foreach($baseRounds as $round) {
         $round->idx = 0;
         foreach($scheduleParts as $index=>$category){
            if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
               $round->className = 'H';
            $name = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
            if( mb_strpos( mb_strtoupper($name,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               $round->positionW = $category->part;
               $round->idx = $index;
            }
         }
         if( !$round->positionW ) //undefined link to O block
            $round->positionW = '0';
      }
      if( $scheduleParts ){
         usort($baseRounds, function($a, $b) {
            if( $a->positionW == $b->positionW )
               return($a->idx > $b->idx);
            else
               return( $a->positionW > $b->positionW );
         });
      }
      return view('admin.panel')
         ->with('baseRounds', $baseRounds);
   }

   public function panelSet(){
      $rounds = [];
      $baseRounds = Input::old('roundId');
      if($baseRounds != null) {
         foreach($baseRounds as $round) {
            if(filter_var(Input::old($round), FILTER_VALIDATE_BOOLEAN) == 1)
               $rounds[] = $round;
         }
      }
      if( count($rounds) == 0 ){
         return redirect('admin/panel');
      }
      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      $Program = [];
      $PartsNo = [];
      $PartsStr = "BLOK - ";
      foreach($rounds as $index){
         $round = $this->tournamentHelper->getBaseRound(intval($index));
         if( mb_strpos( mb_strtoupper($round->className,'UTF-8'), "H.") !== false )
            $round->className = 'H';
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         foreach($scheduleParts as $category){
            if( mb_strpos( mb_strtoupper($round->description,'UTF-8'), mb_strtoupper($category->name,'UTF-8')) !== false ){
               if( !in_array($category->part,$PartsNo)){
                  $PartsNo[] = $category->part;
                  if( count($PartsNo) == 1 )
                     $PartsStr .= $category->part;
                  else
                     $PartsStr .= ', '.$category->part;
               }
            }
         }
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'KOMB') !== false ){
            $round->styleName = "Komb";
         }
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'STAND') !== false ){
            $round->styleName = "Standard";
         }
         if( mb_strpos(mb_strtoupper(trim($round->styleName),'UTF-8'),'LATIN') !== false ){
            $round->styleName = "Latin";
         }
         $round->description = $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
         $round->judgesNo = $this->tournamentHelper->getJudgesNo($round->baseRoundId);
         $Program = array_add( $Program, $round->baseRoundId, $round );
         //find part of torurnment
      }
      $Judges = [];
      $JudgesRound = [];
      $Judges = $this->tournamentHelper->getJudgesCSV(); //read from CSV file
      if( count($Judges) == 0 ){ //maybe file no exists or empty, bad format
         $mainJudge = $this->tournamentHelper->getMainJudge(0);
         if( $mainJudge ){
            $mainJudge->sign = '#';
            if( is_numeric($mainJudge->plId2) )
               $Judges = array_add( $Judges, $mainJudge->plId2, $mainJudge );
            else if( is_numeric($mainJudge->plId) )
               $Judges = array_add( $Judges, $mainJudge->plId, $mainJudge );
            else{
               $mainJudge->plId2 = $mainJudge->lastName.';'.$mainJudge->firstName.';'.$mainJudge->city.';'.$mainJudge->country;
               $Judges = array_add( $Judges, $mainJudge->plId2, $mainJudge );
            }
         }
         Session::flash('status', 'error');
      }
      $JudgesRound = $this->tournamentHelper->getJudges(0); //read from PTT rounds
      //add judges from ptt program to csv listed
      foreach($JudgesRound as $judgeDB){ 
         $yes = true;
         foreach($Judges as $judge){
            if( $judgeDB->firstName == $judge->firstName && $judgeDB->lastName == $judge->lastName ){
               $yes = false;
               break;
            }
         }
         if( $yes ){
            $new_judge = new Judge();
            $new_judge->plId = $judgeDB->plId2;
            $new_judge->firstName = $judgeDB->firstName;
            $new_judge->lastName = $judgeDB->lastName;
            $new_judge->city = $judgeDB->city;
            $new_judge->country = $judgeDB->country;
            if( strlen($new_judge->country) == 0 )
               $new_judge->country = 'Polska';
            $new_judge->category = $judgeDB->category;
            $new_judge->sign = $judgeDB->sign;
            if($new_judge->plId == null || $new_judge->plId == '')
               $new_judge->plId = $new_judge->lastName.';'.$new_judge->firstName.';'.$new_judge->city.';'.$new_judge->country;
            $Judges = array_add($Judges, $new_judge->plId, $new_judge);
         }
      }
      $Scrutineers = [];
      $ScrutineersforRound = [];
      $Scrutineers = $this->tournamentHelper->getScrutineersCSV();
      //add scrutineers from ptt program to csv listed
      $ScrutineersforRound = $this->tournamentHelper->getScrutineers(0);
      
      if( count($ScrutineersforRound)>0 ){//add to all
         foreach($ScrutineersforRound as $scrforR){
            $yes = true;
            foreach($Scrutineers as $scr){
               if( $scrforR->firstName == $scr->firstName && $scrforR->lastName == $scr->lastName ){
                  $yes = false;
                  break;
               }
            }
            if( $yes ){ //new, add to list
               if($scrforR->plId == '' && $scrforR->plId2 == '') //without plId - maybe manual write, not form base
                  $scrforR->plId2 = $scrforR->lastName.';'.$scrforR->firstName.';'.$scrforR->city.';'.$scrforR->country;
               $Scrutineers = array_add($Scrutineers,$scrforR->plId2,$scrforR);
            }
         }
      }

      foreach($Scrutineers as $scr){
         $idx = 0;
         $tSign = " ";
         foreach($Program as $round){
            $tSign[$idx] = $this->tournamentHelper->getBaseJudgeSign( $scr->firstName,$scr->lastName, $round->baseRoundId );
            $idx = $idx + 1;
         }
         $scr->sign = $tSign;
      }

      $JudgesList = []; //first should be main judge
      if( count($Judges) > 0 ){
         reset($Judges);
         if(current($Judges)->sign != '#')
            $JudgesList = array_add($JudgesList, ' ', ' ' ); //first element empty if not main judge
         else
            $JudgesList = array_add($JudgesList, current($Judges)->plId , current($Judges)->lastName.' '.current($Judges)->firstName);
      }

      foreach($Judges as $judge){
         $idx = 0;
         if( !is_numeric($judge->sign) && is_numeric($judge->plId) )//remove judges without plId (not in Baza.csv)
            $JudgesList = array_add($JudgesList, $judge->plId , $judge->lastName.' '.$judge->firstName );

         foreach($Program as $round){   
            $judge->sign[$idx] = $this->tournamentHelper->getBaseJudgeSign($judge->firstName,$judge->lastName, $round->baseRoundId );
            $idx = $idx + 1;
         }
      }

      $JudgesList = array_add($JudgesList, '000000' , 'Wprowadź: ' );
      $JudgesBaza = [];
      $JudgesBaza = $this->tournamentHelper->getJudgesDB(); 
      
      usort($JudgesBaza, function($a, $b) {
         if( $a->lastName == $b->lastName)
            return( $a->firstName > $b->firstName );
         else
            return( $a->lastName > $b->lastName );
      });
      //Session::put('program_judge', $Program);
      return view('admin.panelTable')
            ->with('program', $Program)
            ->with('judges', $Judges)
            ->with('scrutineers',$Scrutineers)
            ->with('judgelist', $JudgesList)
            ->with('eventId', $this->tournamentHelper->getEventId())
            ->with('parts',$PartsStr);
            //->with('judgeDB', $JudgesBaza);
   }
   
   public function panelSave(){
      $roundsId = Input::old('roundBaseId');
      $roundNames = Input::old('roundName');
      $judgesId = Input::old('judgeId');
      $judgesNo = Input::old('judgeNo');
      $judgeNames = Input::old('judgeName');
      $scrId = Input::old('scrId');
      $scrNames = Input::old('scrName');
      $judgeMainId = Input::old('MainJudge');
      $judgeMainLast = Input::old('my_main_judge_l');
      $judgeMainFirst = Input::old('my_main_judge_f');
      $judgeMainCity = Input::old('my_main_judge_c');

      $scheduleParts = $this->tournamentHelper->getPartsCSV();
      for($i = 0; $i< count($roundsId);$i++){
         $judge_set = []; //create new
         $scr_set = [];
         if( isset($judgeMainId) && $judgeMainId != '000000' && $judgeMainId != '' )
            $judge_set[] = $judgeMainId;
         else if( $judgeMainLast && $judgeMainFirst )
            $judge_set[] = $judgeMainLast.';'.$judgeMainFirst.';'.$judgeMainCity.';';
         else
            $judge_set[] = $judgesId[0]; //first judge by default

         foreach( $judgesId as $id ){
            if( filter_var(Input::old($roundsId[$i].'-'.$id), FILTER_VALIDATE_BOOLEAN) == 1 ) //checked
               $judge_set[] = $id;
         }

         if( count($scrId) ){
            //find scrutineers also
            foreach( $scrId as $id ){
               if( filter_var(Input::old('s'.$roundsId[$i].'-'.$id), FILTER_VALIDATE_BOOLEAN) == 1 ) //checked
                  $scr_set[] = $id;
            }
         }
         if( count($judge_set) > 1 )
         {  
            $part = '';
            if( $scheduleParts ){
               foreach($scheduleParts as $category){
                  if( mb_strpos( mb_strtoupper($category->name,'UTF-8'), mb_strtoupper($roundNames[$i],'UTF-8')) !== false )
                     $part = $category->part;
               }
            }
            $this->tournamentHelper->SaveJudge2CSV( $roundNames[$i], $part, $judge_set, $judgesNo[$i], $scr_set );
         }
         unset($judge_set); //clear all table
         unset($scr_set);
      }
      return redirect()->back()->with('status', 'success');
   }

   public function autocomplete(){
      $term = Input::get('term');
      $results = [];   
      $JudgesDB = [];
      $JudgesDB = $this->tournamentHelper->getJudgesDB();
      usort($JudgesDB, function($a, $b) {
      if( $a->lastName == $b->lastName)
         return( $a->firstName > $b->firstName );
      else
         return( $a->lastName > $b->lastName );
      });
      foreach ($JudgesDB as $judge){
         if( (strpos(strtolower($judge->lastName[0]), strtolower($term[0])) !== false) )
            $results[] = [ 'id' => $judge->plId, 'value' => $judge->lastName.', '.$judge->firstName.', '.$judge->city.', '.$judge->plId ];
      }
      return response()->json($results);
   }

   public function postPanel(){
      if( Input::has('zestaw') ){
         return redirect('admin/panelSet')->withInput();
      }
      if( Input::has('save') ){
         return redirect('admin/panelSave')->withInput();
      }
      return redirect('admin/panelSet');
   }
}