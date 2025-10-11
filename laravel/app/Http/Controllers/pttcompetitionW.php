<?php namespace App\Http\Controllers;

class DatabaseDbfFile
{
   public function open($folder, $sub, $file, $position = 0, $towrite = false) {
      $filename = $folder;
      if(strlen($sub) > 0)
         $filename = $filename . '/' . $sub;
      $filename = $filename . '/' . $file;
      if($position > 0)
         $filename = $filename . $position;
      $filename = $filename . '.DBF';
      $this->openFile($filename, $towrite);
   }

   public function openFile($filename, $towrite = false) {
      $this->file = false;
      $this->nRecords = 0;
      $this->seekAtRecord = self::LAST_RECORD_INDEX;

      if(!file_exists($filename))
         return;
      if($towrite)
         $this->file = @fopen($filename, 'rb+');
      else
         $this->file = @fopen($filename, 'rb');
      if($this->file == false )
         return;
      $dbfSignature = $this->readByte();
      if($dbfSignature != self::DBF_SIGNATURE)
      {
         $this->close();
         return;
      }
      $this->readWordLE();
      $this->readByte();
      $nRecords = $this->readDwordLE();
      $this->headerSize = $this->readWordLE();
      $this->recordSize = $this->readWordLE() - 1;
      $this->codePage = 852;

      if(@fseek($this->file, $this->headerSize) != 0)
         return;
      for($i = 0; $i < $nRecords; $i++)
      {
         $ch = @fread($this->file, 1);
         if(ord($ch) == self::RECORD_VALID)
            $this->nRecords++;
         @fseek($this->file, $this->recordSize, SEEK_CUR);
      }
   }

   public function close() {
      if($this->file == false )
         return;
      @fclose($this->file);
      $this->file = false;
      $this->nRecords = 0;
      $this->seekAtRecord = self::LAST_RECORD_INDEX;
   }

   public function numberOfRecords() {
      return $this->nRecords;
   }

   public function sizeOfRecord() {
      return $this->recordSize;
   }

   public function selectRecord($index) {
      if($this->isError())
         return false; 
      if($index < 0 || $index >= $this->nRecords)
         return false;
      $this->seekToRecord($index);
      $this->record = fread($this->file, $this->recordSize);
      return true;
   }

   public function storeRecord($index, $offset, $length) {
      if($this->isError())
         return false; 
      if($index < 0 || $index >= $this->nRecords)
         return false;
      $this->seekToRecord($index);
      @fseek($this->file, $offset, SEEK_CUR);
      $part = substr($this->record, $offset, $length);
      $written = @fwrite($this->file, $part, $length);
      if($written === false || $written != $length)
         return false;
      @fseek($this->file, $this->recordSize - $offset - $length, SEEK_CUR);
      return true;
   }

   public function readStringAt($offset, $length) {
      $string = '';
      for($i = 0; $i < $length; $i++)
      {
         $byte = ord($this->record[$offset+$i]);
         if($byte < 0x20)
            break;
         $string = $string . chr($byte); 
      }
      $string = rtrim($string);
      return $string;
   }

   public function readBoolAt($offset) {
      $bool = substr($this->record, $offset, 1);
      if(strcmp($bool, '1') == 0 || strcmp($bool, 'Y') == 0 || strcmp($bool, 'y') == 0 || strcmp($bool, 'T') == 0 || strcmp($bool, 't') == 0) 
         return true;
      else
         return false;
   }

   public function readIntAt($offset, $length) {
      $int = substr($this->record, $offset, $length);
      return @intval($int);
   }

   public function writeStringAt($string, $offset, $length) {
      $stringLength = strlen($string);
      for($i = 0; $i < $length; $i++)
      {
         if($i < $stringLength)
            $this->record[$offset+$i] = $string[$i];
         else
            $this->record[$offset+$i] = chr(0x20);
      }
   }

   public function codePage() {
      return $this->codePage;
   }

   public function isError() {
      if($this->file == false )
         return true;
      return false;
   }

   //---------------------------------------------------

    function __construct() {
       $this->codePage = 852;
    }

    function __destruct() {
       $this->close();
    }

   private function readByte() {
      $byte = fread($this->file, 1);
      $arr = unpack("C", $byte);
      $val = $arr[1];
      return $val;
   }

   private function readWordLE() {
      $word = fread($this->file, 2);
      $arr = unpack("v", $word);
      $val = $arr[1];
      return $val;
   }

   private function readDwordLE() {
      $dword = fread($this->file, 4);
      $arr = unpack("V", $dword);
      $val = $arr[1];
      return $val;
   }

   private function seekToRecord($index) {
      if($index < $this->seekAtRecord)
      {
         $this->seekAtRecord = self::LAST_RECORD_INDEX;
         $fileOffset = $this->headerSize;
         if(fseek($this->file, $fileOffset) != 0)
            return;
         $this->seekAtRecord = 0;
      }

      $recordHead = $this->readByte();
      while($recordHead != self::RECORD_VALID || $this->seekAtRecord < $index)
      {
         if($recordHead == self::RECORD_VALID)
            $this->seekAtRecord++;
         if(fseek($this->file, $this->recordSize, SEEK_CUR) != 0)
         {
            $this->seekAtRecord = self::LAST_RECORD_INDEX;
            return;
         }
         $recordHead = $this->readByte();
      }
      $this->seekAtRecord++;
   }

   private $file;
   private $headerSize;
   private $recordSize;
   private $record;
   private $seekAtRecord;
   private $nRecords;
   private $codePage;

   const DBF_SIGNATURE = 0x03;
   const LAST_RECORD_INDEX = 9999999;
   const RECORD_VALID = 0x20;
}

class ScheduleRecord
{
   public $order;
   public $line;
   public $isDance;
}

class DanceMapItem
{
   public $class;
   public $style;
   public $dances = array();
}

class MapName
{
   public $id;
   public $category;
   public $class;
   public $style;
   public $round;
   public $inUse;

   function __construct() {
      $this->inUse = false;
    }

   public function set($id, $category, $class, $style, $round) {
      $this->id = $id;
      $this->category = $category;
      $this->class = $class;
      $this->style = $style;
      $this->round = $round;
   }
}

class MapFile
{
    function __construct($name) {
      $this->filename = $name;
      $this->autoId = 1;
      $this->modified = false;
      $this->haveNames = false;
    }

    public function getName($id) {
       $this->read();
       foreach($this->names as $name) {
         if($name->id == $id)
            return $name;
      }
      return false;
    }

    public function getIdOfCategory($category) {
       return $this->getId($category, '', '', '', 0);
    }

    public function getIdOfRound($round, $position) {
      return $this->getId($round->categoryName, $round->className, $round->styleName, $round->roundName, $position);
   }

   public function read() {
      if($this->haveNames)
         return $this->names;
      $this->names = array();
      $this->haveNames = true;
      $this->modified = false;
      if(!file_exists($this->filename))
         return false;
      $file = fopen($this->filename, 'r');
      if($file === false )
         return false;

      while(true)
      {
         $line = fgets($file);
         if($line === false)
            break;
         $line = CPConverter::convert($line, 'CP1250', 'UTF-8');

         $parts = explode(self::SEPARATOR, $line);
         if(!$parts || count($parts) < 5)
            break;
         $map = new MapName();
         $map->id = intval($parts[0]);
         $map->category = $parts[1];
         $map->class = $parts[2];
         $map->style = $parts[3];
         $map->round = $parts[4];
         $map->inUse = false;
         $this->names[] = $map;
         if($map->id >= $this->autoId)
             $this->autoId = $map->id + 1;
      }
      fclose($file);
      return $this->names;
   }

   public function write() {
      if(!$this->modified)
         return;
      if(file_exists($this->filename))   
         @unlink($this->filename);
      $file = @fopen($this->filename, 'w+');
      if($file === false )
         return;
      foreach($this->names as $map) 
      {
         $line = strval($map->id) . self::SEPARATOR . $map->category . self::SEPARATOR . $map->class . self::SEPARATOR . $map->style . self::SEPARATOR . $map->round . self::SEPARATOR;
         if(!$this->writeLine($file, $line))
            break;
      }
      fclose($file);
      $this->modified = false;
   }

   public function preUpdate() {
       foreach($this->names as $name) 
          $name->inUse = false;
   }

   public function postUpdate() {
      $this->updateRemoved();
   }

   //---------------------

   private function getId($category, $class, $style, $round, $position) {
      $this->read();
       foreach($this->names as $name) {
         if($name->category == $category && $name->class == $class 
            && $name->style == $style && $name->round == $round)
         {
            $name->inUse = true;
            return $name->id;
         }
      }
      $newId = $this->append($category, $class, $style, $round, $position);
      return $newId;
   }

   private function append($category, $class, $style, $round, $position) {
      $map = new MapName();
      $map->set($this->autoId, $category, $class, $style, $round);
      $map->inUse = true;

      $newPosition = $position;
      $isInserted = false;
      $playoff = mb_strpos($map->round, self::ROUND_TYPE_PLAYOFF, 0, 'UTF-8');
      if($playoff !== false)
         $newPosition = 2;

       foreach($this->names as $i=>$name) 
       {
         if($name->category == $map->category && $name->class == $map->class && $name->style == $map->style)
         {
            if($playoff !== false)
            {
               if(strncmp($name->round, $map->round, $playoff) == 0)
               {
                  array_splice($this->names, $i + 1, 0, array($map));
                  $isInserted = true;
                  break;
               }
               $newPosition += 1;
            }
            else
            {
               array_splice($this->names, $i + $newPosition-1, 0, array($map));
               $isInserted = true;
               break;
            }
         }
      }
      if($isInserted)
      {
         foreach($this->names as $i=>$name) 
          {
            if($name->category == $map->category && $name->class == $map->class && $name->style == $map->style)
            {
               $newPosition -= 1;
               if($newPosition <= 0)
               {
                  if($i+1 >= count($this->names))
                  {
                     $this->names[$i]->id = $this->autoId;
                  }
                  else
                  {
                     $nextName = $this->names[$i+1];
                     if($nextName->category == $map->category && $nextName->class == $map->class && $nextName->style == $map->style)
                     {
                        $this->names[$i]->id = $this->names[$i+1]->id;
                     }
                     else
                     {
                        $this->names[$i]->id = $this->autoId;
                     }
                  }
               }
            }
         }
      }
      else //not inserted, add at the end
         $this->names[] = $map;

      $this->autoId += 1;
      $this->modified = true;
      return $map->id;
   }

   private function updateRemoved() {
       foreach($this->names as $i=>$name) 
       {
          if($name->class == '' && $name->style == '')
             $name->inUse = true;
          if($name->inUse == false)
          {
             $name->inUse = true;
             $index = $i+1;
             while($index < count($this->names))
             {
                $map = $this->names[$index];
                if($name->category != $map->category || $name->class != $map->class || $name->style != $map->style)
                   break;
                if($map->inUse)
                   $name->inUse = false;
                $index += 1;
             }
          }
       }
       foreach($this->names as $i=>$name) 
       {
          if($name->inUse == false)
          {
             $this->modified = true;   
             $id = $name->id;
             $index = $i+1;
             while($index < count($this->names))
             {
                $map = $this->names[$index];
                if($name->category != $map->category || $name->class != $map->class || $name->style != $map->style)
                   break;
                $temp = $map->id;
                $map->id = $id;
                $id = $temp;
                $index += 1;
             }
          }
      }
      //save to remove in foreach loop according to php docs
      foreach($this->names as $i=>$name) 
       {
          if($name->inUse == false)
             unset($this->names[$i]);
       }      

   }

   private function writeLine($file, $line) {
      $line = CPConverter::convert($line, 'UTF-8', 'CP1250');
      $line = $line . "\n";
      $length = strlen($line);
      @fseek($file, 0, SEEK_END);
      $written = fwrite($file, $line, $length);
      if($written === false || $written != $length)
         return false;
      return true;
   }


   private   $names = array();
   private $filename;
   private $modified;
   private $autoId;
   private $haveNames;
   const SEPARATOR = '"';
   const ROUND_TYPE_PLAYOFF = 'Baraż';
}

class CompetitionW
{
   public function connect($folder) {
      $this->folder = str_replace('\\', '/', $folder);
      while(substr($this->folder, -1) == '/')
         $this->folder = substr($this->folder, 0, strlen($this->folder) - 1);
      $this->lockname = $this->folder . '/' . self::DB_LOCK;
      $this->votesname = $this->folder . '/' . self::DB_VOTES;
      $this->mapFile = new MapFile($this->folder . '/' . self::DB_MAP);
   }

   public function getLastError() {
      return $this->lastError;
   }
   
   public function getName() {
      $this->readProperty();
      return $this->eventName;    
   }

   public function getEventId() {
      $this->readProperty();
      return $this->eventId;    
   }

   public function getJudges($roundId = 0) {
      $this->readJudges();
      $arr = array();
      foreach($this->judges as $judge)
      {
         if($roundId === 0)
         {
            $exists = false;
            foreach($arr as $already)
            {
               if($judge->firstName == $already->firstName && 
                  $judge->lastName == $already->lastName &&
                  $judge->plId == $already->plId)
               {
                  $exists = true;
                  break;
               }
            }
            if(!$exists)
               $arr[] = $judge;
         }
         else if($this->getCategoryOfRoundId($judge->roundId) == $this->getCategoryOfRoundId($roundId))
         {
            $arr[] = $judge;
         }
      }
      return $arr;
   }

   public function getJudgeSign($firstName, $lastName, $plId, $roundId) {
      $this->readJudges();
      $plIdToCheck = $plId;
      foreach($this->judges as $judge)
      {
         if(!isset($plId) || $plId == '')
            $plIdToCheck = $judge->plId;

         if($judge->firstName == $firstName && 
            $judge->lastName == $lastName &&
            $judge->plId == $plIdToCheck &&
            $this->getCategoryOfRoundId($judge->roundId) == $this->getCategoryOfRoundId($roundId))
         {
            return $judge->sign;
         }
      }      
   }

   public function getRounds() {
      $this->readRounds();
      return $this->rounds;
   }

   public function getBaseRounds() {
      $empty = array();
      return $empty;
   }

   public function getAdditionalRounds() {
      $this->getRounds();
      $arr = array();
      foreach($this->rounds as $round)
      {
         if($round->isAdditional)
            $arr[] = $round;
      }
      return $arr;
   }

   public function getRound($description) {
      $this->getRounds();
      if(is_string($description))
      {
         if(strpos($description, self::ROUND_TYPE_ADDITIONAL) !== false)
            return $this->findRoundByDescription($description, self::ROUND_TYPE_ADDITIONAL);
         else
            return $this->findRoundByDescription($description, self::ROUND_TYPE_BASIC);
      }
      else
      {
         foreach($this->rounds as $round)
         {
            if($round->roundId == $description)
               return $round;
         }
      }
      return false;
   }

   public function getRoundWithType($description, $matchType) {
      $this->getRounds();
      if($matchType == '')
         $matchType = self::ROUND_TYPE_BASIC;
      return $this->findRoundByDescription($description, $matchType);
   }

   public function getDanceCouples($roundId, $danceSignature, &$error) {
      $this->getRounds();
      $this->readDances($roundId);

      $dance = new Dance();
      $dance->roundId = $roundId;
      $dance->signature = $danceSignature;

      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber === false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE){
         if( $danceNumber < 1 )
            $error = 0;
         return false;
      }

      if(count($this->dances) < 1)
         return $dance;

      $groupMaxNumber = 1;
      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId)
         {
            if($record->groupNumberArray[$danceNumber-1] > $groupMaxNumber)
               $groupMaxNumber = $record->groupNumberArray[$danceNumber-1];
         }
      }
      if($groupMaxNumber > 100) //sentinel
         $groupMaxNumber = 100;

      $groups = array();
      for($i = 0; $i < $groupMaxNumber; $i++)
      {
         $group = array();
         $groups[] = $group;
      }

      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId)
         {
            $couple = new Couple();
            $couple->number = $record->coupleNumber;
            $groupNumber = $record->groupNumberArray[$danceNumber-1];
            if($groupNumber >= 1 && $groupNumber <= $groupMaxNumber)
               $groups[$groupNumber-1][] = $couple;
         }
      }

      for($i = 0; $i < $groupMaxNumber; $i++)
      {
         if(count($groups[$i]) < 1)
            break;
         $dance->couples[] = $groups[$i];
      }

      return $dance;
   }

   public function parseSchedule($scheduleText) {
      $schedule = array();
      if(strncmp($scheduleText, self::SCHEDULE_HEADER, strlen(self::SCHEDULE_HEADER)) != 0)
         return $schedule;
      $state = 0; //0->start of line, 1-start of item
      $name = '';
      $triple = 0;
      $item = new ScheduledRound();
      $scheduleText = $scheduleText . ';';  //end sentinel
      for($i = strlen(self::SCHEDULE_HEADER); $i < strlen($scheduleText); $i++)
      {
         $ch = $scheduleText[$i];
         if(ord($ch) < 0x20)
         {
            if($state == 2)
               $ch = ';';
            else
               continue;
         }
         if($state == 0 && $ch == ';') //start of line, new item
         {
            $state = 1; //item to read
            continue;
         }
         if($state == 1) //start of item "<magic number>1/2 final..."
         {
            if(ord($ch) >= 0x30 && ord($ch) <= 0x39)
               {
               $name = '';
               $item = new ScheduledRound();
               $state = 2;
               }
            continue;
         }
         if($state == 2) //round description "1/2 final ...("
         {
            if($ch == '(') //dances follow
            {
               $item->isDance = true;
               $item->description = $this->convert($name);
               $name = '';
               $triple = 0;
               $state = 3;
            }
            else if($ch == ';') //end, no dance
            {
               $item->description = $this->convert($name);
               if(strlen($item->description) > 0)
                  $schedule[] = $item;
               $state = 0; //new line
               $i--; //look again for separator
            }
            else
               $name = $name . $ch;
            continue;
         }
         if($state == 3) //dances
         {
            if(ord($ch) <= 0x20 || $ch == ')' || $ch == ';') //separator or end
            {
               if(strlen($name) > 0) //there was a name of dance
               {
                  $item->dances[] = $name;
                  $name = '';
               }
               if($ch == ')' || $ch == ';') //end
               {
                  if(strlen($item->description) > 0)
                     $schedule[] = $item;
                  if($ch == ';')
                     $state = 1; //next item
                  else
                     $state = 0; //next line
               }
               $triple = 0;
            }
            else
            {
               $name = $name . $ch;
               $triple = $triple+1;
               if($triple >= 3)
               {
                  if(strlen($name) > 0) //there was a name of dance
                     {
                     $item->dances[] = $name;
                     $name = '';
                     }
                  $triple = 0;
               }
            }
         }
      }
      return $schedule;
   }

   public function parseScheduleW($scheduleFile) {
      $schedule = array();
      $scheduleRecords = $this->readSchedule($scheduleFile);
      sort($scheduleRecords);
      $this->readDanceMap();
      foreach($scheduleRecords as $record)
      {
         $class = mb_substr($record->line, 0, 15, 'UTF-8');
         $style = mb_substr($record->line, 16, 15, 'UTF-8');
         $name = mb_substr($record->line, mb_strlen($record->line, 'UTF-8')-12, 12, 'UTF-8');
         $class = trim(rtrim($class));
         $style = trim(rtrim($style));
         $name = trim(rtrim($name));

         $item = new ScheduledRound();
         if($record->line[0] != '.')
         {
            $map = $this->getDanceMap($class, $style);
            if($map !== false)
            {
               $item->isDance = true;
               $item->description = $name . ' ' . $class . ' ' . $style;
               $item->dances = $map->dances;
            }
            else
            {
               $item->isDance = false;
               $item->description = $record->line;
            }
         }
         else
         {
            $item->isDance = false;
            $item->description = $record->line;
         }
         $schedule[] = $item;
      }
      return $schedule;
   }

   public function parseScheduleFile($scheduleFile) {
      $schedule = array();
      if(!file_exists($scheduleFile))
         return $schedule;
      $file = fopen($scheduleFile, 'r');
      $scheduleText = fread($file, strlen(self::SCHEDULE_HEADER));
      fclose($file);
      if(strncmp($scheduleText, self::SCHEDULE_HEADER, strlen(self::SCHEDULE_HEADER)) == 0)
         return $this->parseSchedule(file_get_contents($scheduleFile));
      else
         return $this->parseScheduleW($scheduleFile);
   }

   public function setVotes($roundId, $danceSignature, $judgeSign, $votes) {
      $this->getRounds();
      $this->readDances($roundId);

      if(count($this->dances) < 1)
         return false;

      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE)
         return false;

      $round = $this->getRound($roundId);
      $isFinal = $round->isFinal;

      if($round->isClosed)
      {
         return false;
      }

      ksort($votes);
      $this->collectVotes($roundId, $danceSignature, $judgeSign, $votes);

      return true;
   }

   public function clearVotes($roundId, $danceSignature, $judgeSign) {
      $this->getRounds();
      $this->readDances($roundId, true);

      if(count($this->dances) < 1)
         return false;

      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE)
      {
         $this->dropDances();
         return false;
      }

      $round = $this->getRound($roundId);

      if($round->isClosed)
      {
         $this->dropDances();
         return false;
      }

      $couples = array();
      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId)
         {
            if($judgeSign != '')
            {
               $vote = new Vote();
               $vote->note = '';
               $vote->rmark = false;
               $vote->judgeSign = $judgeSign;
               $dbResults = $record->notesArray[$danceNumber-1];
               $result = $this->unpackDanceResults($dbResults);
               $result->setVote($vote);
               $dbResults = $this->packDanceResults($result);
               $record->notesArray[$danceNumber-1] = $dbResults->notes;
               $record->sumArray[$danceNumber-1] = $dbResults->sum;
               $this->writeDanceResult($round, $record, $danceNumber);
            }
            else
            {
               $record->notesArray[$danceNumber-1] = '';
               $record->sumArray[$danceNumber-1] = 0;
               $this->writeDanceResult($round, $record, $danceNumber);
            }
            $couples[] = $record->coupleNumber;
         }
      }

      $this->dropDances();

      $voteArray = array();
      $voteValue = array('note' => '', 'rmark' => 'false');
      foreach($couples as $couple) 
         $voteArray[$couple] = $voteValue;

      $judges = $this->getJudges($roundId);
      foreach($judges as $judge) 
      {
         if($judgeSign == '' || $judgeSign == $judge->sign) 
            $this->collectVotes($roundId, $danceSignature, $judge->sign, $voteArray);
      }

      return true;
   }

   public function getVotes($roundId, $judgeSign, $danceSignature) {
      $collectionFile = new CollectionFile($this->votesname);

      $lock = new LockFile($this->lockname);
      $lock->acquire();
      $votes = $collectionFile->get($roundId);
      $lock->release();

      $results = array();

      if(!$votes)
         return $results;

      foreach($votes as $savedVote)
      {
         if($savedVote->judgeSign == $judgeSign && $savedVote->danceSignature == $danceSignature)
         {
            $vote = new Vote();
            $vote->judgeSign = $savedVote->judgeSign;
            $vote->note = $savedVote->note;
            $vote->rmark = $savedVote->rmark;
            $results[$savedVote->coupleNumber] = $vote;
         }
      }
      return $results;
   }

   public function getSavedVotes($roundId, $judgeSign, $danceSignature) {
      $this->getRounds();
      $this->readDances($roundId);

      $results = array();
      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE)
         return false;

      if(count($this->dances) < 1)
         return $results;

      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId)
         {
            if(count($record->notesArray) <= $danceNumber-1)
               continue;
            $result = $this->unpackDanceResults($record->notesArray[$danceNumber-1]);
            $result->coupleNumber = $record->coupleNumber;
            $vote = $result->getVote($judgeSign);
            if($vote !== false)
               $results[$result->coupleNumber] = $vote;
         }
      }
      return $results;
   }

   public function getCoupleVotes($roundId, $coupleNumber, $danceSignature) {
      $collectionFile = new CollectionFile($this->votesname);

      $lock = new LockFile($this->lockname);
      $lock->acquire();
      $votes = $collectionFile->get($roundId);
      $lock->release();

      $results = array();

      if(!$votes)
         return $results;

      foreach($votes as $savedVote)
      {
         if($savedVote->coupleNumber == $coupleNumber && $savedVote->danceSignature == $danceSignature)
         {
            $vote = new Vote();
            $vote->judgeSign = $savedVote->judgeSign;
            $vote->note = $savedVote->note;
            $vote->rmark = $savedVote->rmark;
            $results[$savedVote->judgeSign] = $vote;
         }
      }
      return $results;
   }

   public function getSavedCoupleVotes($roundId, $coupleNumber, $danceSignature) {
      $this->getRounds();
      $this->readDances($roundId);

      $results = array();
      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE)
         return false;

      if(count($this->dances) < 1)
         return $results;

      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId && $record->coupleNumber == $coupleNumber)
         {
            if(count($record->notesArray) <= $danceNumber-1)
               continue;
            $result = $this->unpackDanceResults($record->notesArray[$danceNumber-1]);
            for($i = 0; $i < self::FIELD_SIZE_DANCES_VOTES; $i++)
            {
               $judgeSign = chr(ord('A')+$i);
               $vote = $result->getVote($judgeSign);
               if($vote !== false)
                  $results[$judgeSign] = $vote;
            }
         }
      }
      return $results;
   }

   public function saveVotesToDatabase($roundId) {
      $collectionFile = new CollectionFile($this->votesname);

      $lock = new LockFile($this->lockname);
      $lock->acquire();

      $votes = $collectionFile->get($roundId);

      $lock->release();

      if(!$votes)
         return false;

      $result = true;

      set_time_limit(300);

      $this->readDances($roundId, true);

      foreach($votes as $vote)
      {
         $voteArray = array();
         $voteValue = array('note' => $vote->note, 'rmark' => $vote->rmark == 'R' ? 'true' : 'false');
         $voteArray[$vote->coupleNumber] = $voteValue;
         if($this->saveVotes($roundId, $vote->danceSignature, $vote->judgeSign, $voteArray) == false)
            $result = false;
      }
      $this->dropDances();

      return $result;
   }

   public function checkVotesFile() {
      $collectionFile = new CollectionFile($this->votesname);

      $lock = new LockFile($this->lockname);
      $lock->acquire();
      $result = $collectionFile->check();
      $lock->release();
      return $result;
   }

   public function changeVotesFolder() {
      $collectionFile = new CollectionFile($this->votesname);
      $lock = new LockFile($this->lockname);
      $lock->acquire();
      $collectionFile->changeFolder();
      $lock->release();
   }

   public function setManualResults($results) {
      return;
   }
   public function createReportFile($rounds) {
      return false;
   }

   //temporary public for test purposes
   public function getDanceRecords($roundId) {
      $this->readDances($roundId);
      return $this->dances;
   }


   //---------------------------------------------------

    function __construct() {
       $this->lastError = 0;
       $this->haveProperty = false;
       $this->haveJudges = false;
       $this->haveDancesOfRound = 0;
       $this->haveGroupsOfRound = 0;
       $this->haveMapnames = false;
       $this->mapFile = null;
       $this->judges = array();
    }

    private function convert($text, $cp = 1250) {
       if($cp == 1252)
          $cp = 1250;
       $cpName = 'CP' . $cp;
       return CPConverter::convert($text, $cpName, 'UTF-8');
    }

   private function readProperty() {
      if($this->haveProperty)
         return;
      $db = new DatabaseDbfFile();
      $db->open($this->folder, self::DB_SYSTEM, self::DB_COMPETITION);
      if($db->isError())
      {
          $this->lastError = self::ERROR_FILE;
         return;
      }
      if(!$db->selectRecord(0))
      {
          $this->lastError = self::ERROR_RECORD;
         return;
      }
      $this->eventName = $db->readStringAt(self::FIELD_COMPETITION_NAME, self::FIELD_SIZE_COMPETITION_NAME);
      if($db->selectRecord(1))
      {
         $name2 = $db->readStringAt(self::FIELD_COMPETITION_NAME, self::FIELD_SIZE_COMPETITION_NAME);
         if(strlen($name2) > 0)
            $this->eventName = $this->eventName . ' ' . $name2;
      }
      if($db->selectRecord(2))
      {
         $name2 = $db->readStringAt(self::FIELD_COMPETITION_NAME, self::FIELD_SIZE_COMPETITION_NAME);
         if(strlen($name2) > 0)
            $this->eventName = $this->eventName . ' ' . $name2;
      }
      $this->eventName = trim($this->eventName);
      $this->eventId = '';
      if($db->selectRecord(3))
      {
         $this->eventId = $db->readStringAt(self::FIELD_COMPETITION_NAME, self::FIELD_SIZE_COMPETITION_NAME);
      }
      $this->eventId = trim($this->eventId);
      $db->close();

      $this->eventName = $this->convert($this->eventName, $db->codePage());
      $this->eventId = $this->convert($this->eventId, $db->codePage());

      $this->haveProperty = true;
   }

   private function readCategories() {
      if($this->haveCategories)
         return;
      if(!file_exists($this->folder))
         return;

      if($handleDir = opendir($this->folder))
      {
         while(($file = readdir($handleDir)) !== false) 
         {
            $dir = $this->folder . '/' . $file;
            if($file != "." && $file != ".." && is_dir($dir)) 
            {
               $fileRounds = $dir . '/' . self::DB_BASE_ROUNDS . '.DBF';
               if(file_exists($fileRounds))
               {
                  $this->categories[] = $file;
               }
            }
         }
         closedir($handleDir);
         $this->haveCategories = true;
      }
   }

   private function readJudges() {
      if($this->haveJudges)
         return;
      $this->readCategories();
      if(count($this->categories) == 0)
         return;

      $this->mapFile->read();
      for($catIndex = 0; $catIndex < count($this->categories); $catIndex++)
      {
         $category = $this->categories[$catIndex];
         $db = new DatabaseDbfFile();
         $db->open($this->folder, $category, self::DB_JUDGES);
         if($db->isError())
         {
             $this->lastError = self::ERROR_FILE;
            return;
         }
         //skip first -> main judge
         for($i = 1; $i < $db->numberOfRecords() && $i <= self::MAX_JUDGES; $i++)
         {
            if(!$db->selectRecord($i))
            {
                $this->lastError = self::ERROR_RECORD;
               return;
            }
            $judge = new Judge();
            $judge->sign = chr(ord('A') + $i - 1);
            $judge->firstName = $db->readStringAt(self::FIELD_JUDGES_FIRST_NAME, self::FIELD_SIZE_JUDGES_FIRST_NAME);
            $judge->lastName = $db->readStringAt(self::FIELD_JUDGES_LAST_NAME, self::FIELD_SIZE_JUDGES_LAST_NAME);
            if(strlen($judge->firstName) == 0 && strlen($judge->lastName) == 0)
               continue;
            $judge->plId = '';
            $judge->dbId = '';
            $judge->roundId = $this->mapFile->getIdOfCategory($category);
            $this->judges[] = $judge;
         }
         $db->close();
      }
   
      foreach($this->judges as $judge)
      {
         $judge->firstName = trim($judge->firstName);
         $judge->lastName = trim($judge->lastName);
         $judge->firstName = $this->convert($judge->firstName, $db->codePage());
         $judge->lastName = $this->convert($judge->lastName, $db->codePage());
      }
      $this->mapFile->write();
      $this->haveJudges = true;
   }

   private function readRounds() {
      if($this->haveRounds)
         return;
      $this->readCategories();
      if(count($this->categories) == 0)
         return;

      $this->mapFile->read();
      $this->mapFile->preUpdate();
      for($catIndex = 0; $catIndex < count($this->categories); $catIndex++)
      {
         $category = $this->categories[$catIndex];
         $db = new DatabaseDbfFile();
         $db->open($this->folder, $category, self::DB_ROUNDS);
         if($db->isError())
         {
             $this->lastError = self::ERROR_FILE;
            return;
         }
         $lastClassName = '';
         $lastStyleName = '';
         $position = 0;
         for($i = 0; $i < $db->numberOfRecords(); $i++)
         {
            if(!$db->selectRecord($i))
            {
                $this->lastError = self::ERROR_RECORD;
               return;
            }
            $round = new Round();
            $round->categoryName = $category;
            $round->className = $db->readStringAt(self::FIELD_ROUNDS_CLASS, self::FIELD_SIZE_ROUNDS_CLASS);
            $round->styleName = $db->readStringAt(self::FIELD_ROUNDS_STYLE, self::FIELD_SIZE_ROUNDS_STYLE);
            $round->roundName = $db->readStringAt(self::FIELD_ROUNDS_NAME, self::FIELD_SIZE_ROUNDS_NAME);

            $round->className = trim($round->className);
            $round->styleName = trim($round->styleName);
            $round->roundName = trim($round->roundName);            

            $round->roundName = $this->convert($round->roundName, $db->codePage());
            $round->categoryName = $this->convert($round->categoryName, $db->codePage());
            $round->className = $this->convert($round->className, $db->codePage());
            $round->styleName = $this->convert($round->styleName, $db->codePage());

            if($round->className != $lastClassName || $round->styleName != $lastStyleName)
            {
               $lastClassName = $round->className;
               $lastStyleName = $round->styleName;
               $position = 1;
            }
            else
               $position += 1;

            $round->roundId = $this->mapFile->getIdOfRound($round, $position);

            $round->positionW = $i+1;
            $round->nGroupsW = $db->readIntAt(self::FIELD_ROUNDS_N_GROUPS, self::FIELD_SIZE_ROUNDS_N_GROUPS);
            $round->nDancesW = $db->readIntAt(self::FIELD_ROUNDS_N_DANCES, self::FIELD_SIZE_ROUNDS_N_DANCES);

            $round->matchType = self::ROUND_TYPE_BASIC;
            $round->isAdditional = false;

            if(mb_strpos($round->roundName, self::ROUND_TYPE_PLAYOFF, 0, 'UTF-8') !== false)
                   $round->matchType = self::ROUND_TYPE_PLAYOFF;

            if(strncmp($round->roundName, 'Fina', 4) == 0)
               $round->isFinal = true;
            else
               $round->isFinal = false;
            $round->isClosed = $db->readBoolAt(self::FIELD_ROUNDS_CLOSED);
            $nCouples = $db->readIntAt(self::FIELD_ROUNDS_N_COUPLES, self::FIELD_SIZE_ROUNDS_N_COUPLES);
            $round->votesRequired = $db->readIntAt(self::FIELD_ROUNDS_N_VOTES, self::FIELD_SIZE_ROUNDS_N_VOTES);
            if($round->isFinal && $nCouples != $round->votesRequired)
               $round->votesRequired = $nCouples;
            
            $round->nDancesW = $db->readIntAt(self::FIELD_ROUNDS_N_DANCES, self::FIELD_SIZE_ROUNDS_N_DANCES);
            for($j = 0; $j < $round->nDancesW; $j++)
            {
               $danceSignature = $db->readStringAt(self::FIELD_ROUNDS_FIRST_DANCE + $j * self::FIELD_SIZE_ROUNDS_DANCE_SHIFT, self::FIELD_SIZE_ROUNDS_DANCE);
               if(strlen($danceSignature) > 0)
                  $round->dances[] = $danceSignature;
            }
            $this->rounds[] = $round;
         }
         $db->close();
      }
      $this->mapFile->postUpdate();
      $this->mapFile->write();
      $this->haveRounds = true;
   }

   private function splitGroup($group) {
       $split = array();
      for($i = 0; $i < (strlen($group)+2)/3; $i++)
      {
         $number = substr($group, $i * 3, 3);
         if(strlen($number) == 0 || $number[0] == '=')
            continue;
         $number = trim($number);
         $number = rtrim($number);
         $split[] = $number;
      }
      return $split;
   }

   private function readGroups($roundId) {
      if($roundId == 0)
         return;
      if($this->haveGroupsOfRound == $roundId)
         return;

      $this->groups = NULL;
      $this->haveGroupsOfRound = 0;

      $round = $this->getRound($roundId);
      if($round === false)
         return;

      $category = $this->getCategoryOfRoundId($round->roundId);

      $db = new DatabaseDbfFile();
      $db->open($this->folder, $category, self::DB_GROUPS, $round->positionW);
      if($db->isError())
      {
          $this->lastError = self::ERROR_FILE;
         return;
      }

      $recordSize = $db->sizeOfRecord();
      for($i = 0; $i < $db->numberOfRecords(); $i++)
      {
         if(!$db->selectRecord($i))
         {
             $this->lastError = self::ERROR_RECORD;
            return;
         }

         $groupString = $db->readStringAt(self::FIELD_GROUPS_GROUPS, $recordSize);
         $group = $this->splitGroup($groupString);
         $this->groups[] = $group;
      }
      $db->close();
   
      $this->haveGroupsOfRound = $roundId;
   }

   private function dropDances() {
         $this->dances = NULL;
         $this->haveDancesOfRound = 0;
   }

   private function readDances($roundId, $allCouples = false) {
      if($roundId == 0)
         return;
      //if($this->haveDancesOfRound == $roundId && !$allCouples)
      //   return;

      $this->dropDances();

      $round = $this->getRound($roundId);
      if($round === false)
         return;

      $category = $this->getCategoryOfRoundId($round->roundId);

      $db = new DatabaseDbfFile();
      $db->open($this->folder, $category, self::DB_DANCES, $round->positionW);
      if($db->isError())
      {
          $this->lastError = self::ERROR_FILE;
         return;
      }
      for($i = 0; $i < $db->numberOfRecords(); $i++)
      {
         if(!$db->selectRecord($i))
         {
             $this->lastError = self::ERROR_RECORD;
            return;
         }
         $excluded = $db->readStringAt(self::FIELD_DANCES_EXCLUDED, self::FIELD_SIZE_DANCES_EXCLUDED);
         if(!$allCouples && ($excluded == '-' || $excluded == '#'))
            continue;
      
         $dance = new DanceRecord();
         $dance->roundId = $roundId;
         $dance->coupleNumber = $db->readStringAt(self::FIELD_DANCES_COUPLE_NUMBER, self::FIELD_SIZE_DANCES_COUPLE_NUMBER);
         $dance->coupleNumber = trim($dance->coupleNumber);
         $dance->excluded = $excluded;
         if($round->nGroupsW > 1)
            $groupNumber = $this->getGroupNumber($roundId, $dance->coupleNumber);
         else
            $groupNumber = 1;
         for($n = 0; $n < $round->nDancesW; $n++)
         {
            $dance->notesArray[] = $db->readStringAt(self::FIELD_DANCES_VOTES_1 + $n * self::FIELD_DANCES_OFFSET, self::FIELD_SIZE_DANCES_VOTES);   
            $dance->groupNumberArray[] = $groupNumber;
         }
         $this->dances[] = $dance;
      }
      $db->close();
   
      $this->haveDancesOfRound = $roundId;
   }

   private function writeDanceResult($round, $record, $danceNumber) {
      $db = new DatabaseDbfFile();
      $db->open($this->folder, $round->categoryName, self::DB_DANCES, $round->positionW, true); //open to write
      if($db->isError())
      {
          $this->lastError = self::ERROR_FILE;
         return false;
      }
      $written = false;
      for($i = 0; $i < $db->numberOfRecords(); $i++)
      {
         if(!$db->selectRecord($i))
         {
             $this->lastError = self::ERROR_RECORD;
            return false;
         }
         if($record->coupleNumber != trim($db->readStringAt(self::FIELD_DANCES_COUPLE_NUMBER, self::FIELD_SIZE_DANCES_COUPLE_NUMBER)))
            continue;
         $offset = self::FIELD_DANCES_VOTES_1 + ($danceNumber - 1) * self::FIELD_DANCES_OFFSET;
         $length = self::FIELD_SIZE_DANCES_VOTES;
         $db->writeStringAt($record->notesArray[$danceNumber-1], $offset, $length);
         $written = $db->storeRecord($i, $offset, $length);
         
         break;   
      }
      $db->close();

      if($written == false)
         $this->dropDances();

      return $written;
   }

   private function readSchedule($scheduleFile) {
       $schedule = array();
      $db = new DatabaseDbfFile();
      $db->openFile($scheduleFile);
      if($db->isError())
      {
          $this->lastError = self::ERROR_FILE;
         return $schedule;
      }

      for($i = 0; $i < $db->numberOfRecords(); $i++)
      {
         if(!$db->selectRecord($i))
         {
             $this->lastError = self::ERROR_RECORD;
            return $schedule;
         }
         $item = new ScheduleRecord();
         $item->order = $db->readIntAt(self::FIELD_SCHEDULE_ORDER, self::FIELD_SIZE_SCHEDULE_ORDER);
         $item->line = $db->readStringAt(self::FIELD_SCHEDULE_ITEM, self::FIELD_SIZE_SCHEDULE_ITEM);
         $item->line = $this->convert($item->line, $db->codePage());
         $item->isDance = false;
         $schedule[] = $item;
      }
      $db->close();
      return $schedule;
   }

   private function readDanceMap() {
      $this->dancemap = NULL;
      $this->readCategories();
      if(count($this->categories) == 0)
         return;

      for($catIndex = 0; $catIndex < count($this->categories); $catIndex++)
      {
         $category = $this->categories[$catIndex];
         $db = new DatabaseDbfFile();
         $db->open($this->folder, $category, self::DB_DANCEMAP);
         if($db->isError())
         {
             $this->lastError = self::ERROR_FILE;
            return;
         }
         for($i = 0; $i < $db->numberOfRecords(); $i++)
         {
            if(!$db->selectRecord($i))
            {
                $this->lastError = self::ERROR_RECORD;
               return;
            }
            $map = new DanceMapItem();
            $map->class = $db->readStringAt(self::FIELD_DANCEMAP_CLASS, self::FIELD_SIZE_DANCEMAP_CLASS);
            $map->style = $db->readStringAt(self::FIELD_DANCEMAP_STYLE, self::FIELD_SIZE_DANCEMAP_STYLE);

            $map->class = trim($map->class);
            $map->style = trim($map->style);
            $map->class = $this->convert($map->class, $db->codePage());
            $map->style = $this->convert($map->style, $db->codePage());

            for($j = 0; $j < self::MAX_DANCES; $j++)
            {
               $danceSignature = $db->readStringAt(self::FIELD_DANCEMAP_FIRST_DANCE + $j * self::FIELD_SIZE_DANCEMAP_DANCE_SHIFT, self::FIELD_SIZE_DANCEMAP_DANCE);
               if(strlen($danceSignature) > 0)
                  $map->dances[] = $danceSignature;
            }
            $this->dancemap[] = $map;
         }
         $db->close();
      }
   }

   private function getDanceMap($class, $style) {
      foreach($this->dancemap as $map) 
      {
         if($map->class == $class && $map->style == $style)
            return $map;
      }
      return false;
   }

   private function getCategoryOfRoundId($roundId) {
      if($roundId == 0)
         return '';
      $name = $this->mapFile->getName($roundId);
      if($name === false)
         return '';

      return $name->category;
   }

   private function getGroupNumber($roundId, $coupleNumber) {
      $this->readGroups($roundId);
      for($i = 0; $i < count($this->groups); $i++)
      {
         for($j = 0; $j < count($this->groups[$i]); $j++)
         {
            if($coupleNumber == $this->groups[$i][$j])
            {
               return $i+1;
            }
         }
      }
      return 0;
   }

   private function findRoundByDescription($description, $matchType) {
      $this->readRounds();
      if(count($this->rounds) < 1)
         return false;
      foreach($this->rounds as $round)
      {
         $info = $this->createRoundDescription($round);
         $pos = strpos($description, $info);
         if($pos !== false && strcmp($round->matchType, $matchType) == 0)
         {
            return $round;
         }
      }
      return false;
   }

   private function createRoundDescription($round) {
      //$info = $round->roundName . ' ' . $round->categoryName . ' ' . $round->className . ' ' . $round->styleName;
      $info = $round->roundName . ' ' . $round->className . ' ' . $round->styleName;
      return $info;
   }

   private function getDanceNumberInRound($roundId, $danceSignature) {
      $round = $this->getRound($roundId);
      if($round === false)
         return false;

      for($i = 0; $i < count($round->dances); $i++)
      {
         if(strcmp($round->dances[$i], $danceSignature) == 0)
            return ($i+1);
      }
      return 0;//false;
   }

   private function getDanceRecord($roundId, $coupleNumber) {
      $this->readDances($roundId);
      foreach($this->dances as $dance)
      {
         if($dance->roundId == $roundId && $dance->coupleNumber == $coupleNumber)
            return $dance;
      }
      return false;
   }

   private function unpackDanceResults($dbResult) {
      $result = new DanceResult();
      for($i = 0; $i < strlen($dbResult); $i++)
      {
         $dbNote = substr($dbResult, $i, 1);
         $vote = new Vote();
         $vote->judgeSign = chr(ord('A') + $i);
         $vote->note = ' ';
         $vote->rmark = false;
         if($dbNote >= '0' && $dbNote <= '9')
            $vote->note = $dbNote;
         if($dbNote == 'x' || $dbNote == 'X' || $dbNote == 'R')
            $vote->note = 'X';
         if($dbNote == 'r' || $dbNote == 'R')
            $vote->rmark =  true;
         if($dbNote >= 'A' && $dbNote <= 'I')
         {
            $vote->note = chr(ord('1') + ord($dbNote) - ord('A'));
            $vote->rmark =  true;
         }
         $result->setVote($vote);
      }
      return $result;
   }

   private function packDanceResults($result) {
      $dbResults = '';
      $dbResult = '';
      $sumVotes = 0;
      for($i = 0; $i < self::FIELD_SIZE_DANCES_VOTES; $i++)
      {
         $judgeSign = chr(ord('A')+$i);
         $vote = $result->getVote($judgeSign);
         if($vote === false)
            $dbResult = ' ';
         else
         {
            if($vote->note == 'X' || $vote->note == 'x')
            {
               if($vote->rmark)
                  $dbResult = 'R';
               else
                  $dbResult = 'X';
               $sumVotes += 1;
            }
            if($vote->note == ' ' || $vote->note == '')
            {
               if($vote->rmark)
                  $dbResult = 'r';
               else
                  $dbResult = ' ';
            }
            if($vote->note >= '1' && $vote->note <= '9')
            {
               if($vote->rmark)
                  $dbResult = chr(ord('A') + ord($vote->note) - ord('1'));
               else
                  $dbResult = chr(ord($vote->note)); //to be sure of 1 char
               $sumVotes += intval($vote->note);
            }
         }
         $dbResults = $dbResults . $dbResult;
      }
      $dbResults = rtrim($dbResults);
      $res = new DanceDatabaseResult();
      $res->notes = $dbResults;
      $res->sum = $sumVotes;
      return $res;
   }

   // array: ['judge']=>'A-W' ['note']=>''/'X'/'1-n' ['rmark']=>true/false
   private function unpackVotes($dbVotes) {
      $arr = array();
      for($i = 0; $i < strlen($dbVotes); $i++)
      {
         $note = substr($dbVotes, $i, 1);
         $rmark = false;
         if($note < '0' || $note > '9')
         {
            if($note == ' ')
               $note = '';
            else if($note == 'X' || $note == 'x')
               $note = 'X';
            else
            {
               $rmark = true;
               if($note == 'r' || $note == 'R')
                  $note = '';
               else
                  $note = chr(ord('1') + ord($note) - ord('A'));
            }
         }
         $vote = array('judge' => chr(ord('A') + $i), 'note' => $note, 'rmark' => $rmark);
         $arr[] = $vote;
      }
      return $arr;
   }

   private function collectVotes($roundId, $danceSignature, $judgeSign, $votes) {
      $collectionFile = new CollectionFile($this->votesname);

      $lock = new LockFile($this->lockname);
      $lock->acquire();
      $collectionFile->append($roundId, $danceSignature, $judgeSign, $votes);
      $lock->release();
   }

   private function saveVotes($roundId, $danceSignature, $judgeSign, $votes) {
      $this->getRounds();
      $this->readDances($roundId);

      if(count($this->dances) < 1)
         return false;

      $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
      if($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE)
         return false;


      $round = $this->getRound($roundId);
      $isFinal = $round->isFinal;

      if($round->isClosed)
      {
         return false;
      }

      foreach($this->dances as $record)
      {
         if($record->roundId == $roundId)
         {
            if(!array_key_exists($record->coupleNumber, $votes))
               continue;
            $newVote = $votes[$record->coupleNumber];
            $vote = new Vote();
            $vote->note = $newVote['note'];
            $vote->rmark = $newVote['rmark'] == 'true' ? true : false;

            $vote->judgeSign = $judgeSign;
            $dbResults = $record->notesArray[$danceNumber-1];
            $result = $this->unpackDanceResults($dbResults);
            $result->setVote($vote);
            $dbResults = $this->packDanceResults($result);
            $record->notesArray[$danceNumber-1] = $dbResults->notes;
            $record->sumArray[$danceNumber-1] = $dbResults->sum;
            $this->writeDanceResult($round, $record, $danceNumber);
         }
      }

      return true;
   }


   private $folder;
   private $lockname;
   private $votesname;
   private $lastError;
   private $haveProperty;
   private $haveCategories;
   private $haveJudges;
   private $haveRounds;
   private $haveDancesOfRound;
   private $haveGroupsOfRound;
   private $eventName;
   private $eventId;
   private $mapFile;
   private $categories = array();
   private $judges = array();
   private $rounds = array();
   private $dances = array();
   private $groups = array();
   private $dancemap = array();

   const ERROR_FILE = 1;
   const ERROR_RECORD = 2;

   const DB_SIZE_OF_FLOAT = 8;
   const DB_SIZE_OF_BOOL = 1;
   const MAX_JUDGES = 26;
   const MAX_DANCES = 20;

   const SCHEDULE_HEADER = 'Program_turnieju';
   const ROUND_TYPE_BASIC = 'Eliminacje';
   const ROUND_TYPE_ADDITIONAL = 'Dodatkowa';
   const ROUND_TYPE_PLAYOFF = 'Baraż';

   const DB_LOCK = 'SYSTEM/lock.tmp';
   const DB_VOTES = 'SYSTEM/votes.sav';
   const DB_MAP = 'SYSTEM/votesmap.dat';
   const DB_SYSTEM = 'SYSTEM';
   const DB_COMPETITION = 'ORGANTUR';
   const DB_JUDGES = 'KSEDZ';
   const DB_BASE_ROUNDS = 'KLSTURN';
   const DB_ROUNDS = 'RNDTURN';
   const DB_DANCES = 'RNDTR';
   const DB_DANCEMAP = 'KLSTURN';
   const DB_GROUPS = 'GRRND';
   const FIELD_COMPETITION_NAME = 0;
   const FIELD_JUDGES_LAST_NAME = 0;
   const FIELD_JUDGES_FIRST_NAME = 20;
   const FIELD_ROUNDS_CLASS = 0;
   const FIELD_ROUNDS_STYLE = 15;
   const FIELD_ROUNDS_NAME = 30;
   const FIELD_ROUNDS_N_COUPLES = 102;
   const FIELD_ROUNDS_CLOSED = 120;
   const FIELD_ROUNDS_N_VOTES = 105;
   const FIELD_ROUNDS_N_GROUPS = 111;
   const FIELD_ROUNDS_N_DANCES = 113;
   const FIELD_ROUNDS_FIRST_DANCE = 42;
   const FIELD_DANCES_COUPLE_NUMBER = 0;
   const FIELD_DANCES_VOTES_1 = 3;
   const FIELD_DANCES_OFFSET = 31;
   const FIELD_DANCES_EXCLUDED = 636;
   const FIELD_DANCES_MAX_DANCE = 20;
   const FIELD_GROUPS_GROUPS = 0; 
   const FIELD_SCHEDULE_ORDER = 0; 
   const FIELD_SCHEDULE_ITEM = 3; 
   const FIELD_DANCEMAP_CLASS = 0; 
   const FIELD_DANCEMAP_STYLE = 15;
   const FIELD_DANCEMAP_FIRST_DANCE = 41;
   const FIELD_SIZE_COMPETITION_NAME = 60;
   const FIELD_SIZE_JUDGES_FIRST_NAME = 15;
   const FIELD_SIZE_JUDGES_LAST_NAME = 20;
   const FIELD_SIZE_ROUNDS_CLASS = 15;
   const FIELD_SIZE_ROUNDS_STYLE = 15;
   const FIELD_SIZE_ROUNDS_NAME = 12;
   const FIELD_SIZE_ROUNDS_N_COUPLES = 3;
   const FIELD_SIZE_ROUNDS_N_VOTES = 3;
   const FIELD_SIZE_ROUNDS_N_GROUPS = 2;
   const FIELD_SIZE_ROUNDS_N_DANCES = 2;
   const FIELD_SIZE_ROUNDS_DANCE = 2;
   const FIELD_SIZE_ROUNDS_DANCE_SHIFT = 3;
   const FIELD_SIZE_DANCES_COUPLE_NUMBER = 3;
   const FIELD_SIZE_DANCES_VOTES = 26;
   const FIELD_SIZE_DANCES_EXCLUDED = 1;
   const FIELD_SIZE_SCHEDULE_ORDER = 3; 
   const FIELD_SIZE_SCHEDULE_ITEM = 50; 
   const FIELD_SIZE_DANCEMAP_CLASS = 15; 
   const FIELD_SIZE_DANCEMAP_STYLE = 15;
   const FIELD_SIZE_DANCEMAP_DANCE = 2;
   const FIELD_SIZE_DANCEMAP_DANCE_SHIFT = 2;
}


?>
