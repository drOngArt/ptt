<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Support\Arr;

class DatabaseBlock
{
    public function isRecord($index)
    {
        if ($this->numberOfRecords < 1) {
            return false;
        }
        if ($index < $this->firstRecord || $index >= $this->firstRecord + $this->numberOfRecords) {
            return false;
        }

        return true;
    }

    public function clear()
    {
        $this->firstRecord = 0;
        $this->numberOfRecords = 0;
        $this->nextBlock = $this->firstBlock;
        $this->number = 0;
    }

    public function move($addDataSize, $next, $recordSize)
    {
        $this->number = $this->nextBlock;
        $this->firstRecord += $this->numberOfRecords;
        $this->numberOfRecords = 1 + $addDataSize / $recordSize;
        $this->nextBlock = $next;
    }

    public $numberOfRecords;

    public $firstRecord;

    public $nextBlock;

    public $number;

    public $firstBlock;

    public $size;

    // ---------------------------------------------------

    public function __construct()
    {
        $this->size = 0;
        $this->firstBlock = 1;
        $this->clear();
    }
}

class LockFile
{
    public function __construct($name)
    {
        $this->file = false;
        $this->filename = $name;
    }

    public function __destruct()
    {
        $this->release();
    }

    public function acquire()
    {
        if (! $this->filename) {
            return;
        }
        for ($i = 0; $i < 30; $i++) { // 3 sec
            $this->file = @fopen($this->filename, 'x');
            if (! $this->file) {
                usleep(100000);
            } // 100ms
            else {
                break;
            }
        }
    }

    public function release()
    {
        if (! $this->file) {

        } else {
            fclose($this->file);
        }
        @unlink($this->filename);
        $this->file = false;
    }

    private $file;

    private $filename;
}

class DatabaseFile
{
    public function open($folder, $file, $towrite = false)
    {
        $this->file = false;
        $this->nRecords = 0;

        $filename = $folder.'/'.$file.'.DB';
        if (! file_exists($filename)) {
            return;
        }
        if ($towrite) {
            $this->file = fopen($filename, 'rb+');
        } else {
            $this->file = fopen($filename, 'rb');
        }
        if ($this->file == false) {
            return;
        }
        $this->recordSize = $this->readWordLE();
        $this->headerSize = $this->readWordLE();
        $this->readByte();
        $this->block->size = $this->readByte() * 0x400;
        $this->nRecords = $this->readDwordLE();
        $this->readDwordLE();
        $this->block->firstBlock = $this->readWordLE();
        $this->block->clear();

        $this->codePage = 1250;
        if (fseek($this->file, 0x6A) == 0) {
            $cp = $this->readWordLE();
            if ($cp == 0x0354) {
                $this->codePage = 852;
            }
        }
    }

    public function close()
    {
        if ($this->file == false) {
            return;
        }
        fclose($this->file);
        $this->file = false;
        $this->nRecords = 0;
    }

    public function numberOfRecords()
    {
        return $this->nRecords;
    }

    public function selectRecord($index)
    {
        if ($this->isError()) {
            return false;
        }
        if ($index < 0 || $index >= $this->nRecords) {
            return false;
        }
        $this->seekToRecord($index);
        $this->record = fread($this->file, $this->recordSize);

        return true;
    }

    public function storeRecord($index, $offset, $length)
    {
        if ($this->isError()) {
            return false;
        }
        if ($index < 0 || $index >= $this->nRecords) {
            return false;
        }
        $this->seekToRecord($index);
        fseek($this->file, $offset, SEEK_CUR);
        $part = substr($this->record, $offset, $length);
        $written = fwrite($this->file, $part, $length);
        if ($written === false || $written != $length) {
            return false;
        }

        return true;
    }

    public function readStringAt($offset, $length)
    {
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $byte = ord($this->record[$offset + $i]);
            if ($byte < 0x20) {
                break;
            }
            $string = $string.chr($byte);
        }

        return $string;
    }

    public function readBoolAt($offset)
    {
        $bool = substr($this->record, $offset, 1);
        if ((ord($bool[0]) & 0x7F) != 0) {
            return 1;
        } else {
            return 0;
        }
    }

    public function readIntAt($offset)
    {
        $int = substr($this->record, $offset, 2);
        $val = (ord($int[0]) & 0x7F) * 0x100;
        $val += ord($int[1]);

        return $val;
    }

    public function readLongAt($offset)
    {
        $long = substr($this->record, $offset, 4);
        $val = (ord($long[0]) & 0x7F) * 0x1000000;
        $val += ord($long[1]) * 0x10000;
        $val += ord($long[2]) * 0x100;
        $val += ord($long[3]);

        return $val;
    }

    public function readFloatAt($offset)
    {
        $val = substr($this->record, $offset, 8);
        $val[0] = chr(ord($val[0]) & 0x7F);
        $len = self::DB_SIZE_OF_FLOAT;
        for ($i = 0; $i < $len / 2; $i++) {
            $tmp = ord($val[$i]);
            $val[$i] = $val[$len - 1 - $i];
            $val[$len - 1 - $i] = chr($tmp);
        }
        $arr = unpack('d', $val);
        $float = $arr[1];

        return $float;
    }

    public function writeStringAt($string, $offset, $length)
    {
        $stringLength = strlen($string);
        for ($i = 0; $i < $length; $i++) {
            if ($i < $stringLength) {
                $this->record[$offset + $i] = $string[$i];
            } else {
                $this->record[$offset + $i] = chr(0);
            }
        }
    }

    public function writeFloatAt($number, $offset)
    {
        $float = floatval($number);
        $val = pack('d', $float);
        $len = self::DB_SIZE_OF_FLOAT;
        for ($i = 0; $i < $len / 2; $i++) {
            $tmp = ord($val[$i]);
            $val[$i] = $val[$len - 1 - $i];
            $val[$len - 1 - $i] = chr($tmp);
        }
        $val[0] = chr(ord($val[0]) | 0x80);
        for ($i = 0; $i < self::DB_SIZE_OF_FLOAT; $i++) {
            $this->record[$offset + $i] = chr(ord($val[$i]));
        }
    }

    public function codePage()
    {
        return $this->codePage;
    }

    public function isError()
    {
        if ($this->file == false) {
            return true;
        }

        return false;
    }

    // ---------------------------------------------------

    public function __construct()
    {
        $this->codePage = 1250;
        $this->block = new DatabaseBlock;
    }

    public function __destruct()
    {
        $this->close();
    }

    private function readByte()
    {
        $byte = fread($this->file, 1);
        $arr = unpack('C', $byte);
        $val = $arr[1];

        return $val;
    }

    private function readWordLE()
    {
        $word = fread($this->file, 2);
        $arr = unpack('v', $word);
        $val = $arr[1];

        return $val;
    }

    private function readDwordLE()
    {
        $dword = fread($this->file, 4);
        $arr = unpack('V', $dword);
        $val = $arr[1];

        return $val;
    }

    private function seekToRecord($index)
    {
        if (! $this->block->isRecord($index) && $index < $this->block->firstRecord) {
            $this->block->clear();
        }
        while (! $this->block->isRecord($index)) {
            if ($this->block->nextBlock == 0) {
                $this->block->clear();

                return;
            }

            $fileOffset = $this->headerSize + ($this->block->nextBlock - 1) * $this->block->size;
            if (fseek($this->file, $fileOffset) != 0) {
                $this->block->clear();

                return;
            }
            $next = $this->readWordLE();
            $this->readWordLE();
            $addDataSize = $this->readWordLE();
            $this->block->move($addDataSize, $next, $this->recordSize);
        }
        $recordOffset = $this->headerSize + ($this->block->number - 1) * $this->block->size + self::RECORD_DATA_HEAD;
        $recordOffset += ($index - $this->block->firstRecord) * $this->recordSize;
        fseek($this->file, $recordOffset);
    }

    private $file;

    private $headerSize;

    private $recordSize;

    private $record;

    private $nRecords;

    private $block;

    private $codePage;

    const RECORD_DATA_HEAD = 6;

    const DB_SIZE_OF_FLOAT = 8;
}

class CPConverter
{
    public static function convert($text, $cpFrom, $cpTo)
    {
        if (strlen($text) == 0) {
            return $text;
        }
        if ($cpFrom == 'CP852') {
            $text = self::convert852To1250($text);
            $cpFrom = 'CP1250';
        }
        $converted = @iconv($cpFrom, $cpTo, $text);
        if (! $converted || strlen($converted) == 0) {
            $converted = $text;
        }

        return $converted;
    }

    private static function convert852To1250($text)
    {
        $dict = [chr(0xA5) => chr(0xB9), chr(0x86) => chr(0xE6), chr(0xA9) => chr(0xEA),
            chr(0x88) => chr(0xB3), chr(0xE4) => chr(0xF1), chr(0xA2) => chr(0xF3),
            chr(0x98) => chr(0x9C), chr(0xAB) => chr(0x9F), chr(0xBE) => chr(0xBF),
            chr(0xA4) => chr(0xA5), chr(0x8F) => chr(0xC6), chr(0xA8) => chr(0xCA),
            chr(0x9D) => chr(0xA3), chr(0xE3) => chr(0xD1), chr(0xE0) => chr(0xD3),
            chr(0x97) => chr(0x8C), chr(0x8D) => chr(0x8F), chr(0xBD) => chr(0xAF)];

        return strtr($text, $dict);
    }
}

class CollectedVote
{
    public $roundId;

    public $danceSignature;

    public $judgeSign;

    public $coupleNumber;

    public $note;

    public $rmark;
}

class CollectionFile
{
    public function __construct($name)
    {
        $this->filename = $name;
        $parts = explode('/', $name);
        if (count($parts) > 1) {
            $this->backupFilename = App::storagePath().'/logs/'.$parts[count($parts) - 1];
        } else {
            $this->backupFilename = App::storagePath().'/logs/'.'unknown.sav';
        }
    }

    public function changeFolder()
    {
        if (! file_exists($this->backupFilename)) {
            return;
        }
        $backupName = substr($this->backupFilename, 0, strlen($this->backupFilename) - 4).'_'.date('Y-m-d_His', filemtime($this->backupFilename)).'.sav';
        @rename($this->backupFilename, $backupName);
    }

    public function append($roundId, $danceSignature, $judgeSign, $votes)
    {
        return appendDescription($roundId, $danceSignature, $judgeSign, $votes, false, false);
    }

    public function appendDescription($roundId, $danceSignature, $judgeSign, $votes, $description, $matchType)
    {
        if (! file_exists($this->filename)) {
            $this->file = @fopen($this->filename, 'w+');
        } else {
            $this->file = @fopen($this->filename, 'r+');
        }
        if ($this->file === false) {
            return false;
        }

        if (! file_exists($this->backupFilename)) {
            $this->backupFile = @fopen($this->backupFilename, 'w+');
            if ($this->backupFile !== false) {
                $line = '$ Backup: '.$this->filename."\n";
                @fwrite($this->backupFile, $line, strlen($line));
                @fwrite($this->backupFile, '#00000000', 9);
            }
        } else {
            $this->backupFile = @fopen($this->backupFilename, 'r+');
        }

        if ($description !== false) {
            $line = '@  ^'.strval($roundId).'^'.$description.'^'.$matchType.'^';
            $this->write($line);
        }

        $line = strval($roundId).','.$danceSignature.','.$judgeSign.',';
        foreach ($votes as $key => $value) {
            $mark = $value['rmark'] == 'true' ? 'R' : '';
            $line = $line.$key.','.$value['note'].','.$mark.',';
        }
        $line = $line.date('Y-m-d H:i:s');
        $this->write($line);
        fclose($this->file);
        fclose($this->backupFile);
    }

    public function get($roundId)
    {
        if (! file_exists($this->filename)) {
            return false;
        }
        $this->file = fopen($this->filename, 'r');
        if ($this->file === false) {
            return false;
        }

        $votes = [];
        while (true) {
            $line = fgets($this->file);
            if ($line === false || strlen($line) < 6) {
                break;
            }
            if ($line[0] == '$') { // comment
                continue;
            }
            if ($line[0] == '@') { // description
                continue;
            }
            if ($line[0] == '#') { // crc
                break;
            }

            $parts = explode(',', $line);
            if (! $parts || count($parts) < 3) {
                break;
            }
            if ($parts[0] != $roundId) {
                continue;
            }

            $additionalParts = 3;
            if (strstr($line, ':') !== false) {
                $additionalParts = 4;
            }

            for ($i = 0; $i < (count($parts) - $additionalParts - 1) / 3; $i++) {
                $index = 3 + $i * 3;
                $vote = new CollectedVote;
                $vote->roundId = $roundId;
                $vote->danceSignature = $parts[1];
                $vote->judgeSign = $parts[2];
                $vote->coupleNumber = $parts[$index + 0];
                $vote->note = $parts[$index + 1];
                if (strlen($parts[$index + 2]) > 0 && $parts[$index + 2][0] == 'R') {
                    $vote->rmark = true;
                } else {
                    $vote->rmark = false;
                }
                $votes[] = $vote;
            }
        }
        fclose($this->file);

        return $votes;
    }

    public function getDescription($roundId, $description, $matchType)
    {
        if (! file_exists($this->filename)) {
            return false;
        }
        $this->file = fopen($this->filename, 'r');
        if ($this->file === false) {
            return false;
        }

        $prevRoundId = $roundId;
        while (true) {
            $line = fgets($this->file);
            if ($line === false || strlen($line) < 6) {
                break;
            }
            if ($line[0] == '#') { // crc
                break;
            }

            if ($line[0] != '@') { // description
                continue;
            }

            $parts = explode('^', $line);
            if (! $parts || count($parts) < 4) {
                continue;
            }
            if ($parts[2] != $description || $parts[3] != $matchType) {
                continue;
            }
            if ($parts[1] != $roundId) {
                $prevRoundId = $parts[1];
            }
        }
        fclose($this->file);

        return $this->get($prevRoundId);
    }

    public function check()
    {
        if (! file_exists($this->filename)) {
            return false;
        }
        $this->file = fopen($this->filename, 'r');
        if ($this->file === false) {
            return false;
        }

        $result = false;
        $crc = 0;
        while (true) {
            $line = fgets($this->file);
            $line = trim($line, "\n\r");
            if ($line === false || strlen($line) < 6) {
                break;
            }
            if ($line[0] == '$') { // comment
                continue;
            }
            if ($line[0] == '#') { // crc
                if (strlen($line) != 9) {
                    break;
                }
                $fileCrc = substr($line, 1, 8);
                $crcString = sprintf('%016x', $crc);
                $crcString = substr($crcString, 8, 8);
                if ($fileCrc == $crcString) {
                    $result = true;
                }
                break;
            }
            $lineCrc = hexdec(hash('crc32', $line, false));
            $crc += $lineCrc;
        }
        fclose($this->file);

        return $result;
    }

    private function write($line)
    {
        $crc = hexdec(hash('crc32', $line, false));
        @fseek($this->file, -8, SEEK_END);
        $oldCrc = @fread($this->file, 8);
        if ($oldCrc) {
            $crc += hexdec($oldCrc);
        }
        $line = $line."\n";
        if ($this->backupFile !== false) {
            @fseek($this->backupFile, -9, SEEK_END);
            @fwrite($this->backupFile, $line, strlen($line));
            @fwrite($this->backupFile, '#00000000', 9);
        }
        $line = $line.'#';
        $length = strlen($line);
        @fseek($this->file, -9, SEEK_END);
        $written = fwrite($this->file, $line, $length);
        if ($written === false || $written != $length) {
            return false;
        }
        $crcString = sprintf('%016x', $crc);
        $crcString = substr($crcString, 8, 8);
        $written = fwrite($this->file, $crcString, 8);
        if ($written === false || $written != $length) {
            return false;
        }

        return true;
    }

    private $filename;

    private $backupFilename;

    private $file;

    private $backupFile;
}

class ReportFile
{
    public function __construct($name)
    {
        $this->filename = $name;
        $this->file = false;
    }

    public function create()
    {
        if ($this->file === false) {
            $this->file = @fopen($this->filename, 'w');
        }
        if ($this->file === false) {
            return false;
        }
        $line = '';

        return true;
    }

    public function set($text)
    {
        $this->line = '';
        if ($this->file === false) {
            return false;
        }
        if (strpos($text, ' ') === false) {
            $this->line = $text;
        } else {
            $this->line = '"'.$text.'"';
        }

        return true;
    }

    public function append($text)
    {
        if ($this->file === false) {
            return false;
        }
        $this->line = $this->line.';';
        if (strpos($text, ' ') === false) {
            $this->line = $this->line.$text;
        } else {
            $this->line = $this->line.'"'.$text.'"';
        }

        return true;
    }

    public function writeLine($text)
    {
        $this->set($text);

        return $this->write();
    }

    public function write()
    {
        $this->line = CPConverter::convert($this->line, 'UTF-8', 'CP1250');
        $this->line = $this->line."\n";
        $length = strlen($this->line);
        $written = fwrite($this->file, $this->line, $length);
        $this->line = '';
        if ($written === false || $written != $length) {
            return false;
        }

        return true;
    }

    public function close()
    {
        if ($this->file === false) {
            return;
        }
        fclose($this->file);
        $this->file = false;
    }

    private $filename;

    private $file;

    private $line;
}

class FileCSV
{
    public function __construct($name)
    {
        $this->filename = $name;
        $this->file = false;
    }

    public function create()
    {
        if ($this->file === false) {
            $this->file = @fopen($this->filename, 'w');
        }
        if ($this->file === false) {
            return false;
        }
        $line = '';

        return true;
    }

    public function set($text)
    {
        $this->line = '';
        if ($this->file === false) {
            return false;
        }
        if (strpos($text, ' ') === false) {
            $this->line = $text;
        } else {
            $this->line = '"'.$text.'"';
        }

        return true;
    }

    public function append($text)
    {
        if ($this->file === false) {
            return false;
        }
        $this->line = $this->line.';';
        if (strpos($text, ' ') === false) {
            $this->line = $this->line.$text;
        } else {
            $this->line = $this->line.'"'.$text.'"';
        }

        return true;
    }

    public function writeLine($text)
    {
        $this->set($text);

        return $this->write();
    }

    public function write()
    {
        $this->line = CPConverter::convert($this->line, 'UTF-8', 'CP1250');
        $this->line = $this->line."\r\n";
        $length = strlen($this->line);
        $written = fwrite($this->file, $this->line, $length);
        $this->line = '';
        if ($written === false || $written != $length) {
            return false;
        }

        return true;
    }

    public function close()
    {
        if ($this->file === false) {
            return;
        }
        fclose($this->file);
        $this->file = false;
    }

    public $filename;

    private $file;

    public $line;
}

class ManualResult
{
    public $roundId;

    public $coupleNumber;

    public $position;
}

class ManualResults
{
    public function __construct($name)
    {
        $this->filename = $name;
    }

    public function read()
    {
        if (! file_exists($this->filename)) {
            return false;
        }
        $file = @fopen($this->filename, 'r');
        if ($file === false) {
            return false;
        }

        $this->results = [];
        while (true) {
            $line = fgets($file);
            if ($line === false) {
                break;
            }

            $parts = explode(',', $line);
            if (! $parts || count($parts) < 3) {
                break;
            }

            $result = new ManualResult;
            $result->roundId = intval($parts[0]);
            $result->coupleNumber = trim($parts[1]);
            $result->position = intval($parts[2]);
            $this->results[] = $result;
        }
        fclose($file);

        return true;
    }

    public function write()
    {
        $file = @fopen($this->filename, 'w');
        if ($file === false) {
            return false;
        }
        $success = true;

        foreach ($this->results as $result) {
            if ($result->position == 0) {
                continue;
            }
            $line = strval($result->roundId).','.$result->coupleNumber.','.strval($result->position)."\r\n";
            $length = strlen($line);
            $written = fwrite($file, $line, $length);
            if ($written === false || $written != $length) {
                $success = false;
                break;
            }
        }
        fclose($file);

        return $success;
    }

    public $results = [];

    private $filename;
}

class Judge
{
    public $firstName;

    public $lastName;

    public $plId;

    public $plId2;

    public $dbId;

    public $roundId;

    public $sign;

    public $city;

    public $country;

    public $category;
}

class SchedulePart
{
    public $name;

    public $part;
}

class Club
{
    public $club;

    public $country;
}

class JudgeDB
{
    public $plId;

    public $firstName;

    public $lastName;

    public $city;

    public $country;

    public $categoryJ;

    public $categoryS;

    public $sign;
}

class Round
{
    public $roundId;

    public $baseRoundId;

    public $roundName;

    public $categoryName;

    public $className;

    public $styleName;

    public $matchType;

    public $dances = [];

    public $votesRequired;

    public $isTop;

    public $isFinal;

    public $isAdditional;

    public $isClosed;

    public $baseNumberOfCouples;

    public $NumberOfCouples;

    public $startNo;

    public $endNo;

    public $competitionDanceType;

    public $positionW; // special W

    public $nGroupsW; // special W

    public $nDancesW; // special W

    public string $description = '';

    public $isDance;

    public $bg_color;
}

class Couple
{
    public $number;

    public $number2; // latin

    public $roundId;

    public $plIdA;

    public $plIdB;

    public $firstNameA;

    public $lastNameA;

    public $firstNameB;

    public $lastNameB;

    public $club;

    public $country;

    public $resultPosition;

    public $resultPoints;

    public $resultPodium;

    public $manualPosition;

    public $marker;

    public $section;
}

class Dance
{
    public $roundId;

    public $signature;

    public $couples = [];
}

class DanceRecord
{
    public $dbId;

    public $roundId;

    public $coupleId;

    public $coupleNumber;

    public $excluded;

    public $groupNumberArray = [];

    public $notesArray = [];

    public $sumArray = [];
}

class ScheduledRound
{
    public function __construct()
    {
        $this->description = '';
        $this->isDance = false;
        $this->dances = [];
    }

    public $description;

    public $isDance;

    public $dances = [];
}

class Vote
{
    public function __construct()
    {
        $this->judgeSign = 'X';
        $this->note = '';
        $this->rmark = false;
    }

    public $judgeSign;

    public $note;

    public $rmark;
}

class DanceResult
{
    public function getVote($judgeSign)
    {
        foreach ($this->votes as $v) {
            if ($v->judgeSign == $judgeSign) {
                return $v;
            }
        }

        return false;
    }

    public function setVote($vote)
    {
        foreach ($this->votes as $v) {
            if ($v->judgeSign == $vote->judgeSign) {
                $v->note = $vote->note;
                $v->rmark = $vote->rmark;

                return;
            }
        }
        $this->votes[] = $vote;
    }

    public function getNumberOfVotes()
    {
        return count($this->votes);
    }

    public $coupleNumber;

    private $votes = [];
}

class DanceDatabaseResult
{
    public $notes;

    public $sum;

    public $rank;
}

class CompetitionPTT
{
    public function connect($folder)
    {
        $this->folder = str_replace('\\', '/', $folder);
        while (substr($this->folder, -1) == '/') {
            $this->folder = substr($this->folder, 0, strlen($this->folder) - 1);
        }
        $this->lockname = $this->folder.'/'.self::DB_LOCK;
        $this->votesname = $this->folder.'/'.self::DB_VOTES;
        $this->reportname = $this->folder.'/'.self::DB_REPORT;
        $this->manualResultsName = $this->folder.'/'.self::DB_MANUAL_RESULTS;
        $this->listycsvname = $this->folder.'/'.self::DB_LISTY_CSV.$this->getEventId().'.csv';
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getName()
    {
        $this->readProperty();

        return $this->eventName;
    }

    public function getEventId()
    {
        $this->readProperty();

        return $this->eventId;
    }

    public function getJudges($roundId = 0)
    {
        $this->readJudges();
        $arr = [];

        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1 || $judge->sign[0] < 'A' || $judge->sign[0] > 'Z') {
                continue;
            }
            if ($roundId == 0) {
                $exists = false;
                foreach ($arr as $already) {
                    if (($judge->firstName == $already->firstName &&
                       $judge->lastName == $already->lastName &&
                       $judge->plId == $already->plId) ||
                       ($judge->firstName == '' || $judge->lastName == '')) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    if ($judge->firstName != '' && $judge->lastName != '') {
                        $arr[] = $judge;
                    }
                }
            } elseif ($judge->roundId == $this->getBaseRoundId($roundId)) {
                $arr[] = $judge;
            }
        }

        return $arr;
    }

    public function getMainJudge($roundId = 0)
    {
        $this->readJudges();

        foreach ($this->judges as $judge) {
            if ($judge->sign != 'Główny') {
                continue;
            }
            if ($roundId == 0) {
                if ($judge->firstName != '' && $judge->lastName != '') {
                    return $judge;
                }
            } elseif ($judge->roundId == $this->getBaseRoundId($roundId)) {
                return $judge;
            }
        }

        return false;
    }

    public function getScrutineers($roundId = 0)
    {
        $this->readJudges();
        $arr = [];
        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1 || $judge->sign[0] < 1 || $judge->sign[0] > 9) {
                continue;
            }
            if ($roundId == 0) {
                $exists = false;
                foreach ($arr as $already) {
                    if (($judge->firstName == $already->firstName &&
                       $judge->lastName == $already->lastName &&
                       $judge->plId == $already->plId) ||
                       ($judge->firstName == '' || $judge->lastName == '')) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    if ($judge->firstName != '' && $judge->lastName != '') {
                        $arr[] = $judge;
                    }
                }
            } elseif ($judge->roundId == $this->getBaseRoundId($roundId)) {
                $arr[] = $judge;
            }
        }

        return $arr;
    }

    public function getJudgesDB()
    {
        $this->readJudgesDB();

        return $this->judgesDB;
    }

    public function getJudgeDBbyID($id)
    {
        $this->readJudgesDB();
        if ($id == '000000') {
            return false;
        } elseif ($id == 0) { // return all
            return $this->judgesDB;
        } else {
            foreach ($this->judgesDB as $found) {
                if ($found->plId == $id) {
                    return $found;
                }
            }

            return false;
        }
    }

    public function getScrutineersCSV()
    {
        $this->readCSV();
        $aScr = [];

        foreach ($this->judgesCSV as $judge) {
            if (is_numeric($judge->sign)) {
                $aScr = Arr::add($aScr, $judge->plId, $judge);
            }
        }

        return $aScr;
    }

    public function getJudgesCSV()
    {
        $this->readCSV();
        $JudgesCSV = [];
        foreach ($this->judgesCSV as $judge) {
            if (! is_numeric($judge->sign)) {
                $JudgesCSV = Arr::add($JudgesCSV, $judge->plId, $judge);
            }
        }

        return $JudgesCSV;
    }

    public function getBaseJudges($roundId)
    {
        $this->readJudges();
        $arr = [];
        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1 || $judge->sign[0] < 'A' || $judge->sign[0] > 'Z') {
                continue;
            }
            if ($judge->roundId == $roundId) {
                $arr[] = $judge;
            }
        }

        return $arr;
    }

    public function getJudgesNo($roundId)
    {
        $this->readJudges();
        $arr = [];
        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1 || $judge->sign[0] < 'A' || $judge->sign[0] > 'Z') {
                continue;
            }
            if ($judge->roundId == $roundId) {
                $arr[] = $judge;
            }
        }

        return count($arr);
    }

    public function getJudgeSign($firstName, $lastName, $plId, $roundId)
    {
        $this->readJudges();
        $plIdToCheck = $plId;
        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1 || $judge->sign[0] < 'A' || $judge->sign[0] > 'Z') {
                continue;
            }

            if (! isset($plId) || $plId == '') {
                $plIdToCheck = $judge->plId;
            }

            if ($judge->firstName == $firstName &&
               $judge->lastName == $lastName &&
               $judge->plId == $plIdToCheck &&
               $judge->roundId == $this->getBaseRoundId($roundId)) {
                return $judge->sign;
            }
        }
    }

    public function getBaseJudgeSign($firstName, $lastName, $roundId)
    {
        $this->readJudges();

        foreach ($this->judges as $judge) {
            if (strlen($judge->sign) != 1) {// without Główny
                continue;
            }

            if ($judge->firstName == $firstName &&
               $judge->lastName == $lastName &&
               $judge->roundId == $roundId) {
                return $judge->sign;
            }
        }

        return ' ';
    }

    public function getPartsCSV()
    {
        $this->readCSV();

        return $this->categoriesCSV;
    }

    public function getCouplesCSV()
    {
        $this->readCSV();

        return $this->couplesCSV;
    }

    public function getRounds()
    {
        $this->readBaseRounds();
        $this->readRounds();

        return $this->rounds;
    }

    public function getBaseRounds()
    {
        $this->readBaseRounds();

        return $this->baseRounds;
    }

    public function getTopRounds()
    {
        $this->getRounds();
        $arr = [];
        foreach ($this->rounds as $round) {
            if ($round->isTop) {
                $arr[] = $round;
            }
        }

        return $arr;
    }

    public function getAdditionalRounds()
    {
        $this->getRounds();
        $arr = [];
        foreach ($this->rounds as $round) {
            if ($round->isAdditional) {
                $arr[] = $round;
            }
        }

        return $arr;
    }

    public function getRound($description)
    {
        $this->getRounds();
        if (is_string($description)) {
            if (mb_strpos($description, self::ROUND_TYPE_ADDITIONAL, 0, 'UTF-8') !== false) {
                return $this->findRoundByDescription($description, self::ROUND_TYPE_ADDITIONAL);
            } elseif (mb_strpos($description, self::ROUND_TYPE_PLAYOFF, 0, 'UTF-8') !== false) {
                return $this->findRoundByDescription($description, self::ROUND_TYPE_PLAYOFF);
            } else {
                return $this->findRoundByDescription($description, self::ROUND_TYPE_BASIC);
            }
        } else {
            foreach ($this->rounds as $round) {
                if ($round->roundId == $description) {
                    return $round;
                }
            }
        }

        return false;
    }

    public function getRoundWithType($description, $matchType)
    {
        $this->getRounds();
        if ($matchType == '') {
            $matchType = self::ROUND_TYPE_BASIC;
        }

        return $this->findRoundByDescription($description, $matchType);
    }

    public function getBaseRound($roundId)
    {
        $this->readBaseRounds();
        foreach ($this->baseRounds as $round) {
            if ($round->roundId == $roundId) {
                return $round;
            }
        }

        return false;
    }

    public function getCouples($baseRoundId = 0)
    {
        $this->readCouples();
        $arr = [];
        foreach ($this->couples as $couple) {
            if ($baseRoundId == 0) {
                $arr[] = $couple;
            } elseif ($couple->roundId == $baseRoundId) {
                $arr[] = $couple;
            }
        }
        // correct club
        $this->getCouplesCSV();
        if (count($this->couplesCSV) > 0) {
            foreach ($arr as $index => $couple) {
                foreach ($this->couplesCSV as $one) {
                    if ($couple->plIdA == $one->plIdA && $couple->plIdB == $one->plIdB) {
                        $arr[$index]->club = $one->club;
                        $arr[$index]->country = $one->country;
                    }
                }
            }
        }

        return $arr;
    }

    public function getCouplesInRound($myRound)
    {
        $this->getRounds();
        $this->readCouples();
        $allCouples = [];
        $baseRoundId = 0;
        $error = -1;
        foreach ($this->rounds as $round) {
            if ($round->roundId == $myRound->roundId) {
                $baseRoundId = $round->baseRoundId;
            }
        }
        // try find all the couples for this category
        foreach ($this->couples as $couple) {
            if ($baseRoundId == 0) {
                $allCouples[] = $couple;
            } elseif ($couple->roundId == $baseRoundId) {
                $allCouples[] = $couple;
            }
        }
        $couples4Round = $this->getDanceCouples($myRound->roundId, $myRound->dances[0], $error);
        $arr = [];
        for ($idx = 0; $idx < count($couples4Round->couples); $idx++) {
            foreach ($couples4Round->couples[$idx] as $noname) {
                foreach ($allCouples as $all) {
                    if ($all->number == $noname->number) {
                        $new = new Couple;
                        $new->number = $all->number;
                        $new->plIdA = $all->plIdA;
                        $new->plIdB = $all->plIdB;
                        $new->firstNameA = $all->firstNameA;
                        $new->lastNameA = $all->lastNameA;
                        $new->firstNameB = $all->firstNameB;
                        $new->lastNameB = $all->lastNameB;
                        $new->club = $all->club;
                        $new->country = $all->country;
                        $arr[] = $new;
                        break;
                    }
                }
            }
        }
        asort($arr);
        // correct club
        $this->getCouplesCSV();
        if (count($this->couplesCSV) > 0) {
            foreach ($arr as $index => $couple) {
                foreach ($this->couplesCSV as $one) {
                    if ($couple->plIdA == $one->plIdA && $couple->plIdB == $one->plIdB) {
                        $arr[$index]->club = $one->club;
                        $arr[$index]->country = $one->country;
                    }
                }
            }
        }

        return $arr;
    }

    public function getDanceCouples($roundId, $danceSignature, &$error)
    {
        $this->getRounds();
        $this->readDances();

        $dance = new Dance;
        $dance->roundId = $roundId;
        $dance->signature = $danceSignature;

        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber === false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            if ($danceNumber < 1) {
                $error = 0;
            } // mark error, no dance in list

            return false;
        }

        if (count($this->dances) < 1) {
            return $dance;
        }

        $groupMaxNumber = 1;
        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId) {
                if ($record->groupNumberArray[$danceNumber - 1] > $groupMaxNumber) {
                    $groupMaxNumber = $record->groupNumberArray[$danceNumber - 1];
                }
            }
        }
        if ($groupMaxNumber > 100) { // sentinel
            $groupMaxNumber = 100;
        }

        $groups = [];
        for ($i = 0; $i < $groupMaxNumber; $i++) {
            $group = [];
            $groups[] = $group;
        }

        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId) {
                $couple = new Couple;
                $couple->number = $record->coupleNumber;
                $groupNumber = $record->groupNumberArray[$danceNumber - 1];
                if ($groupNumber >= 1 && $groupNumber <= $groupMaxNumber) {
                    $groups[$groupNumber - 1][] = $couple;
                }
            }
        }

        for ($i = 0; $i < $groupMaxNumber; $i++) {
            if (count($groups[$i]) < 1) {
                break;
            }
            $dance->couples[] = $groups[$i];
        }

        return $dance;
    }

    public function parseScheduleFile($scheduleFile)
    {
        $schedule = [];
        if (! file_exists($scheduleFile)) {
            return $schedule;
        }
        $file = fopen($scheduleFile, 'r');
        $scheduleText = fread($file, strlen(self::SCHEDULE_HEADER));
        fclose($file);
        if (strncmp($scheduleText, self::SCHEDULE_HEADER, strlen(self::SCHEDULE_HEADER)) == 0) {
            return $this->parseSchedule(file_get_contents($scheduleFile));
        } else {
            return $schedule;
        }
    }

    public function setVotes($roundId, $danceSignature, $judgeSign, $votes)
    {
        $this->getRounds();
        $this->readDances();

        if (count($this->dances) < 1) {
            return false;
        }

        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            return false;
        }

        $round = $this->getRound($roundId);
        $isFinal = $round->isFinal;

        if ($round->isClosed) {
            return false;
        }

        ksort($votes);
        $this->collectVotes($roundId, $danceSignature, $judgeSign, $votes);

        return true;
    }

    public function clearVotes($roundId, $danceSignature, $judgeSign)
    {
        $this->getRounds();
        $this->readDances(true);

        if (count($this->dances) < 1) {
            return false;
        }

        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            $this->dropDances();

            return false;
        }

        $round = $this->getRound($roundId);

        if ($round->isClosed) {
            $this->dropDances();

            return false;
        }

        $couples = [];
        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId) {
                if ($judgeSign != '') {
                    $vote = new Vote;
                    $vote->note = '';
                    $vote->rmark = false;
                    $vote->judgeSign = $judgeSign;
                    $dbResults = $record->notesArray[$danceNumber - 1];
                    $result = $this->unpackDanceResults($dbResults);
                    $result->setVote($vote);
                    $dbResults = $this->packDanceResults($result);
                    $record->notesArray[$danceNumber - 1] = $dbResults->notes;
                    $record->sumArray[$danceNumber - 1] = $dbResults->sum;
                    $this->writeDanceResult($record, $danceNumber);
                } else {
                    $record->notesArray[$danceNumber - 1] = '';
                    $record->sumArray[$danceNumber - 1] = 0;
                    $this->writeDanceResult($record, $danceNumber);
                }
                $couples[] = $record->coupleNumber;
            }
        }

        $this->dropDances();

        $voteArray = [];
        $voteValue = ['note' => '', 'rmark' => 'false'];
        foreach ($couples as $couple) {
            $voteArray[$couple] = $voteValue;
        }

        $judges = $this->getJudges($roundId);
        foreach ($judges as $judge) {
            if ($judgeSign == '' || $judgeSign == $judge->sign) {
                $this->collectVotes($roundId, $danceSignature, $judge->sign, $voteArray);
            }
        }

        return true;
    }

    public function getVotes($roundId, $judgeSign, $danceSignature)
    {
        $round = $this->getRound($roundId);
        $collectionFile = new CollectionFile($this->votesname);

        $lock = new LockFile($this->lockname);
        $lock->acquire();
        $votes = $collectionFile->get($roundId);
        $lock->release();

        $results = [];

        $repeat = true;
        while ($repeat) {
            if (! $votes && $round !== false) {
                $lock->acquire();
                $votes = $collectionFile->getDescription($roundId, $this->createRoundDescription($round), $round->matchType);
                $lock->release();
                $repeat = false;
            }
            if (! $votes) {
                return $results;
            }

            foreach ($votes as $savedVote) {
                if ($savedVote->judgeSign == $judgeSign && $savedVote->danceSignature == $danceSignature) {
                    $vote = new Vote;
                    $vote->judgeSign = $savedVote->judgeSign;
                    $vote->note = $savedVote->note;
                    $vote->rmark = $savedVote->rmark;
                    $results[$savedVote->coupleNumber] = $vote;
                }
            }
            if (count($results) != 0) {
                break;
            }
            $votes = false;
        }

        return $results;
    }

    public function getSavedVotes($roundId, $judgeSign, $danceSignature)
    {
        $this->getRounds();
        $this->readDances();

        $results = [];
        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            return false;
        }

        if (count($this->dances) < 1) {
            return $results;
        }

        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId) {
                if (count($record->notesArray) <= $danceNumber - 1) {
                    continue;
                }
                $result = $this->unpackDanceResults($record->notesArray[$danceNumber - 1]);
                $result->coupleNumber = $record->coupleNumber;
                $vote = $result->getVote($judgeSign);
                if ($vote !== false) {
                    $results[$result->coupleNumber] = $vote;
                }
            }
        }

        return $results;
    }

    public function getCoupleVotes($roundId, $coupleNumber, $danceSignature)
    {
        $round = $this->getRound($roundId);
        $collectionFile = new CollectionFile($this->votesname);

        $lock = new LockFile($this->lockname);
        $lock->acquire();
        $votes = $collectionFile->get($roundId);
        $lock->release();

        $results = [];

        $repeat = true;
        while ($repeat) {
            if (! $votes && $round !== false) {
                $lock->acquire();
                $votes = $collectionFile->getDescription($roundId, $this->createRoundDescription($round), $round->matchType);
                $lock->release();
                $repeat = false;
            }
            if (! $votes) {
                return $results;
            }

            foreach ($votes as $savedVote) {
                if ($savedVote->coupleNumber == $coupleNumber && $savedVote->danceSignature == $danceSignature) {
                    $vote = new Vote;
                    $vote->judgeSign = $savedVote->judgeSign;
                    $vote->note = $savedVote->note;
                    $vote->rmark = $savedVote->rmark;
                    $results[$savedVote->judgeSign] = $vote;
                }
            }
            if (count($results) != 0) {
                break;
            }
            $votes = false;
        }

        return $results;
    }

    public function getSavedCoupleVotes($roundId, $coupleNumber, $danceSignature)
    {
        $this->getRounds();
        $this->readDances();

        $results = [];
        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            return false;
        }

        if (count($this->dances) < 1) {
            return $results;
        }

        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId && $record->coupleNumber == $coupleNumber) {
                if (count($record->notesArray) <= $danceNumber - 1) {
                    continue;
                }
                $result = $this->unpackDanceResults($record->notesArray[$danceNumber - 1]);
                for ($i = 0; $i < self::FIELD_SIZE_DANCES_VOTES; $i++) {
                    $judgeSign = chr(ord('A') + $i);
                    $vote = $result->getVote($judgeSign);
                    if ($vote !== false) {
                        $results[$judgeSign] = $vote;
                    }
                }
            }
        }

        return $results;
    }

    public function saveVotesToDatabase($roundId)
    {
        $round = $this->getRound($roundId);
        $collectionFile = new CollectionFile($this->votesname);

        $lock = new LockFile($this->lockname);
        $lock->acquire();
        $lastVotes = $collectionFile->get($roundId);
        if ($round !== false) {
            $prevVotes = $collectionFile->getDescription($roundId, $this->createRoundDescription($round), $round->matchType);
        }
        $lock->release();

        $votes = [];
        if ($prevVotes !== false) {
            foreach ($prevVotes as $prevVote) {
                $votes[] = $prevVote;
            }
        }
        if ($lastVotes !== false) {
            foreach ($lastVotes as $lastVote) {
                $votes[] = $lastVote;
            }
        }

        if (count($votes) == 0) {
            return false;
        }

        $result = true;

        set_time_limit(300);

        $this->readDances(true);

        foreach ($votes as $vote) {
            $voteArray = [];
            $voteValue = ['note' => $vote->note, 'rmark' => $vote->rmark == 'R' ? 'true' : 'false'];
            $voteArray[$vote->coupleNumber] = $voteValue;
            if ($this->saveVotes($roundId, $vote->danceSignature, $vote->judgeSign, $voteArray) == false) {
                $result = false;
            }
        }
        $this->dropDances();

        return $result;
    }

    public function checkVotesFile()
    {
        $collectionFile = new CollectionFile($this->votesname);

        $lock = new LockFile($this->lockname);
        $lock->acquire();
        $result = $collectionFile->check();
        $lock->release();

        return $result;
    }

    public function changeVotesFolder()
    {
        $collectionFile = new CollectionFile($this->votesname);
        $lock = new LockFile($this->lockname);
        $lock->acquire();
        $collectionFile->changeFolder();
        $lock->release();
    }

    public function setManualResults($results)
    {
        $this->readCouples();

        foreach ($results as $result) {
            foreach ($this->couples as $couple) {
                if ($couple->roundId == $result->roundId && $couple->number == $result->coupleNumber) {
                    $couple->manualPosition = $result->position;
                }
            }
        }

        $manualResults = new ManualResults($this->manualResultsName);
        foreach ($this->couples as $couple) {
            $result = new ManualResult;
            $result->roundId = $couple->roundId;
            $result->coupleNumber = $couple->number;
            $result->position = $couple->manualPosition;
            $manualResults->results[] = $result;
        }
        $manualResults->write();
    }

    public function createReportFile($rounds)
    {
        $reportFile = new ReportFile($this->reportname);
        if ($reportFile->create() == false) {
            return false;
        }

        if (count($rounds) < 1) {
            foreach ($this->baseRounds as $baseRound) {
                $rounds[] = $baseRound->roundId;
            }
        }

        $this->readProperty();
        $reportFile->writeLine('Nr_imprezy');
        $reportFile->writeLine($this->eventId);

        $this->readJudges();
        $this->readCouples();

        $reportFile->writeLine('Identyfikator;Kategoria_wiekowa;Klasa;Styl;Symbol;Nazwisko;Imie;Miejscowosc;Kraj');
        foreach ($rounds as $roundId) {
            $round = $this->getBaseRound($roundId);
            if ($round === false) {
                continue;
            }
            foreach ($this->judges as $judge) {
                if ($roundId != $judge->roundId) {
                    continue;
                }
                $reportFile->set($judge->plId2);
                $reportFile->append($round->categoryName);
                $reportFile->append($round->className);
                $reportFile->append($round->styleName);
                $reportFile->append($judge->sign);
                $reportFile->append($judge->lastName);
                $reportFile->append($judge->firstName);
                $reportFile->append($judge->city);
                $reportFile->append($judge->country);
                $reportFile->write();
            }
        }

        $reportFile->writeLine('Identyfikator;Kategoria_wiekowa;Klasa;Styl;Nazwisko;Imie;Kraj;Klub;Punkty;Podium;Miejsce;Klasa_nowa;Rodzaj_turnieju;Rodzaj_tanca;Rundy');

        $this->getRounds();
        $this->readDances(true);

        $baseRounds = [];
        foreach ($this->rounds as $round) {
            if ($round->isAdditional) {
                continue;
            }
            if (strcmp($round->roundName, 'Finał B') == 0 || strcmp($round->roundName, 'Finał C') == 0 || strcmp($round->roundName, 'Finał D') == 0) {
                continue;
            }
            $baseRounds[] = $round->baseRoundId;
        }
        $roundRange = array_count_values($baseRounds);

        foreach ($rounds as $roundId) {
            $round = $this->getBaseRound($roundId);
            if ($round === false) {
                continue;
            }
            foreach ($this->couples as $couple) {
                if ($roundId != $couple->roundId) {
                    continue;
                }

                $numberOfRounds = 0;
                foreach ($this->dances as $dance) {
                    if ($dance->coupleNumber != $couple->number) {
                        continue;
                    }
                    if ($dance->excluded != '' && $dance->excluded != ' ' && $dance->excluded != 'R' && $dance->excluded != 'K') {
                        continue;
                    }
                    $danceRound = $this->getRound($dance->roundId);
                    if ($danceRound->isAdditional) {
                        continue;
                    }
                    if ($danceRound->baseRoundId != $couple->roundId) {
                        continue;
                    }
                    $numberOfRounds++;
                }
                $numberOfAllRounds = $numberOfRounds;
                if (array_key_exists($couple->roundId, $roundRange)) {
                    $numberOfAllRounds = $roundRange[$couple->roundId];
                }

                $pointsA = sprintf('%5.1f', floatval($couple->resultPoints));
                $pointsB = sprintf('%2.0f', floatval($couple->resultPodium));
                $position = $couple->resultPosition.'/'.$round->baseNumberOfCouples;
                if ($couple->manualPosition > 0) {
                    $position = $couple->manualPosition.'/'.$round->baseNumberOfCouples;
                }
                $number = $numberOfRounds.'/'.$numberOfAllRounds;

                $reportFile->set($couple->plIdA);
                $reportFile->append($round->categoryName);
                $reportFile->append($round->className);
                $reportFile->append($round->styleName);
                $reportFile->append($couple->lastNameA);
                $reportFile->append($couple->firstNameA);
                $reportFile->append($couple->country);
                $reportFile->append($couple->club);
                $reportFile->append($pointsA);
                $reportFile->append($pointsB);
                $reportFile->append($position);
                $reportFile->append(''); // klasa nowa
                $reportFile->append(''); // rodzaj turnieju
                $reportFile->append($round->competitionDanceType);
                $reportFile->append($number);
                $reportFile->write();
                if ($couple->plIdB > 0) {
                    $reportFile->set($couple->plIdB);
                    $reportFile->append($round->categoryName);
                    $reportFile->append($round->className);
                    $reportFile->append($round->styleName);
                    $reportFile->append($couple->lastNameB);
                    $reportFile->append($couple->firstNameB);
                    $reportFile->append($couple->country);
                    $reportFile->append($couple->club);
                    $reportFile->append($pointsA);
                    $reportFile->append($pointsB);
                    $reportFile->append($position);
                    $reportFile->append(''); // klasa nowa
                    $reportFile->append(''); // rodzaj turnieju
                    $reportFile->append($round->competitionDanceType);
                    $reportFile->append($number);
                    $reportFile->write();
                }
            }
        }
        $this->dropDances();

        $reportFile->close();

        return true;
    }

    public function createResults($rounds)
    {
        $reportFile = new ReportFile($this->reportname);
        if ($reportFile->create() == false) {
            return false;
        }

        if (count($rounds) < 1) {
            foreach ($this->baseRounds as $baseRound) {
                $rounds[] = $baseRound->roundId;
            }
        }

        $this->readProperty();
        $reportFile->writeLine('Nr_imprezy');
        $reportFile->writeLine($this->eventId);

        $this->readJudges();
        $this->readCouples();

        $reportFile->writeLine('Identyfikator;Kategoria_wiekowa;Klasa;Styl;Symbol;Nazwisko;Imie;Miejscowosc;Kraj');
        foreach ($rounds as $roundId) {
            $round = $this->getBaseRound($roundId);
            if ($round === false) {
                continue;
            }
            foreach ($this->judges as $judge) {
                if ($roundId != $judge->roundId) {
                    continue;
                }
                $reportFile->set($judge->plId2);
                $reportFile->append($round->categoryName);
                $reportFile->append($round->className);
                $reportFile->append($round->styleName);
                $reportFile->append($judge->sign);
                $reportFile->append($judge->lastName);
                $reportFile->append($judge->firstName);
                $reportFile->append($judge->city);
                $reportFile->append($judge->country);
                $reportFile->write();
            }
        }

        $reportFile->writeLine('Identyfikator;Kategoria_wiekowa;Klasa;Styl;Nazwisko;Imie;Kraj;Klub;Punkty;Podium;Miejsce;Klasa_nowa;Rodzaj_turnieju;Rodzaj_tanca;Rundy');

        $this->getRounds();
        $this->readDances(true);

        $baseRounds = [];
        foreach ($this->rounds as $round) {
            if ($round->isAdditional) {
                continue;
            }
            if (strcmp($round->roundName, 'Finał B') == 0 || strcmp($round->roundName, 'Finał C') == 0 || strcmp($round->roundName, 'Finał D') == 0) {
                continue;
            }
            $baseRounds[] = $round->baseRoundId;
        }
        $roundRange = array_count_values($baseRounds);

        foreach ($rounds as $roundId) {
            $round = $this->getBaseRound($roundId);
            if ($round === false) {
                continue;
            }
            foreach ($this->couples as $couple) {
                if ($roundId != $couple->roundId) {
                    continue;
                }

                $numberOfRounds = 0;
                foreach ($this->dances as $dance) {
                    if ($dance->coupleNumber != $couple->number) {
                        continue;
                    }
                    if ($dance->excluded != '' && $dance->excluded != ' ' && $dance->excluded != 'R' && $dance->excluded != 'K') {
                        continue;
                    }
                    $danceRound = $this->getRound($dance->roundId);
                    if ($danceRound->isAdditional) {
                        continue;
                    }
                    if ($danceRound->baseRoundId != $couple->roundId) {
                        continue;
                    }
                    $numberOfRounds++;
                }
                $numberOfAllRounds = $numberOfRounds;
                if (array_key_exists($couple->roundId, $roundRange)) {
                    $numberOfAllRounds = $roundRange[$couple->roundId];
                }

                $pointsA = sprintf('%5.1f', floatval($couple->resultPoints));
                $pointsB = sprintf('%2.0f', floatval($couple->resultPodium));
                $position = $couple->resultPosition.'/'.$round->baseNumberOfCouples;
                if ($couple->manualPosition > 0) {
                    $position = $couple->manualPosition.'/'.$round->baseNumberOfCouples;
                }
                $number = $numberOfRounds.'/'.$numberOfAllRounds;

                $reportFile->set($couple->plIdA);
                $reportFile->append($round->categoryName);
                $reportFile->append($round->className);
                $reportFile->append($round->styleName);
                $reportFile->append($couple->lastNameA);
                $reportFile->append($couple->firstNameA);
                $reportFile->append($couple->country);
                $reportFile->append($couple->club);
                $reportFile->append($pointsA);
                $reportFile->append($pointsB);
                $reportFile->append($position);
                $reportFile->append(''); // klasa nowa
                $reportFile->append(''); // rodzaj turnieju
                $reportFile->append($round->competitionDanceType);
                $reportFile->append($number);
                $reportFile->write();
                if ($couple->plIdB > 0) {
                    $reportFile->set($couple->plIdB);
                    $reportFile->append($round->categoryName);
                    $reportFile->append($round->className);
                    $reportFile->append($round->styleName);
                    $reportFile->append($couple->lastNameB);
                    $reportFile->append($couple->firstNameB);
                    $reportFile->append($couple->country);
                    $reportFile->append($couple->club);
                    $reportFile->append($pointsA);
                    $reportFile->append($pointsB);
                    $reportFile->append($position);
                    $reportFile->append(''); // klasa nowa
                    $reportFile->append(''); // rodzaj turnieju
                    $reportFile->append($round->competitionDanceType);
                    $reportFile->append($number);
                    $reportFile->write();
                }
            }
        }
        $this->dropDances();

        $reportFile->close();

        return true;
    }

    // change polish letter to base latin: 'Ą' => 'A'
    private function convert_pl($first)
    {
        $second = [
            "\xc4\x85" => "\x61", "\xc4\x84" => "\x41",
            "\xc4\x87" => "\x63", "\xc4\x86" => "\x43",
            "\xc4\x98" => "\x45", "\xc4\x99" => "\x65",
            "\xc5\x81" => "\x4c", "\xc5\x82" => "\X6c",
            "\xc3\xb3" => "\x6f", "\xc3\x93" => "\x4f",
            "\xc5\x9b" => "\x73", "\xc5\x9a" => "\x53",
            "\xc5\xbc" => "\x7a", "\xc5\xbb" => "\x5a",
            "\xc5\xba" => "\x7a", "\xc5\xb9" => "\x5a",
            "\xc5\x84" => "\x6e", "\xc5\x83" => "\x4e",
        ];

        return strtr($first, $second);
    }

    public function SaveJudge2CSV($category_name, $part, $judgesId, $count)
    {
        $judgeFile = new FileCSV(CPConverter::convert($this->folder.'/Listy/'.'Judges '.$part.' '.$this->convert_pl($category_name).'.csv', 'UTF-8', 'CP1250'));
        $orderJ = '_ABCDEFGHIJKLMNOPRSTUWXYZ';

        if (is_dir($this->folder.'/Listy') == false) {
            mkdir($this->folder.'/Listy', 0777);
        }

        if ($judgeFile->create() == false) {
            return false;
        }

        $judgeFile->writeLine('Symbol;Nazwisko;Imię;Miejscowość;Kraj;Id;Kategoria');
        for ($i = 0; $i <= $count; $i++) {
            $judge = false;
            if (count($judgesId) > $i && $judgesId[$i] != '000000' && is_numeric($judgesId[$i])) {
                $judge = $this->getJudgeDBbyID($judgesId[$i]);
            }

            if ($i == 0) {
                $judgeFile->set('Główny');
            } elseif ($i <= $count) {
                $judgeFile->set($orderJ[$i]);
            } else {
                break;
            }
            if ($judge) {
                $judgeFile->append($judge->lastName);
                $judgeFile->append($judge->firstName);
                $judgeFile->append($judge->city);
                $judgeFile->append($judge->country);
                $judgeFile->append($judge->plId);
                $judgeFile->append($judge->categoryJ);
            } elseif (count($judgesId) > $i && ! is_numeric($judgesId[$i])) {// maybe manual added
                $parts = explode(';', $judgesId[$i]);
                $judgeFile->append($parts[0]);
                $judgeFile->append($parts[1]);
                $judgeFile->append($parts[2]);
                $judgeFile->append($parts[3]);
                $judgeFile->append('');
                $judgeFile->append('');
            } else {
                $judgeFile->append(';;;;;');
            }
            $judgeFile->write();
        }
        // scrutineers now
        $Scrutineers = [];
        $ScrutineersforRound = [];
        $Scrutineers = $this->getScrutineersCSV();
        // add scrutineers from ptt program to csv listed
        $ScrutineersforRound = $this->getScrutineers(0);
        if (count($ScrutineersforRound) > 0) {// add to all
            foreach ($ScrutineersforRound as $scrforR) {
                $yes = true;
                foreach ($Scrutineers as $scr) {
                    if ($scrforR->firstName == $scr->firstName && $scrforR->lastName == $scr->lastName) {
                        $yes = false;
                        break;
                    }
                }
                if ($yes) { // new, add to list
                    if ($scrforR->plId == '' && $scrforR->plId2 == '') { // without plId - maybe manual write, not form base
                        $scrforR->plId2 = $scrforR->lastName.';'.$scrforR->firstName.';'.$scrforR->city.';'.$scrforR->country;
                    }
                    $Scrutineers = Arr::add($Scrutineers, $scrforR->plId2, $scrforR);
                }
            }
        }
        $i = 0;
        foreach( $Scrutineers as $scr){
            $judge = false;
            if( $scr->plId != '000000' && $scr->plId != 0 && $scr->plId != ' ' && is_numeric($scr->plId)) {
                $judge = $this->getJudgeDBbyID($scr->plId);
            }
            $judgeFile->set($i + 1);
            $i++;
            if ($judge) {
                $judgeFile->append($judge->lastName);
                $judgeFile->append($judge->firstName);
                $judgeFile->append($judge->city);
                $judgeFile->append($judge->country);
                $judgeFile->append($judge->plId);
                $judgeFile->append($judge->categoryS);
            } elseif (count($Scrutineers) > $i && ! is_numeric($scr->plId)) {// maybe manual added
                $parts = explode(';', $scr);
                $judgeFile->append($parts[0]);
                $judgeFile->append($parts[1]);
                $judgeFile->append($parts[2]);
                $judgeFile->append($parts[3]);
                $judgeFile->append('');
                $judgeFile->append('');
            } else {
                $judgeFile->append(';;;;;');
            }
            $judgeFile->write();
        }
        $judgeFile->close();

        return true;
    }

    public function SaveCouples2CSV($couples, $round)
    {
        $coupleFile = new FileCSV(CPConverter::convert($this->folder.'/Listy/'.'Pary '.$round->positionW.' '.$this->convert_pl($round->description).'.csv', 'UTF-8', 'CP1250'));

        if (is_dir($this->folder.'/Listy') == false) {
            mkdir($this->folder.'/Listy', 0777);
        }

        if ($coupleFile->create() == false) {
            return false;
        }

        $coupleFile->writeLine('Nr;Nazwisko_partnera;Imię_partnera;Nazwisko_partnerki;Imię_partnerki;Klub;Kraj;Punkty;Podium;Wsk;Id_partnera;Id_partnerki;Wskpary');
        for ($i = $round->startNo; $i <= $round->endNo; $i++) {
            $couple = false;
            foreach ($couples as $pair) {
                if ($i == $pair->number) {
                    $couple = $pair;
                    break;
                }
            }
            if ($couple) {
                $coupleFile->set($couple->number);
                $coupleFile->append($couple->lastNameA);
                $coupleFile->append($couple->firstNameA);
                if (! empty($couple->lastNameB)) {
                    $coupleFile->append($couple->lastNameB);
                } else {
                    $coupleFile->append('*');
                }
                if (! empty($couple->firstNameB)) {
                    $coupleFile->append($couple->firstNameB);
                } else {
                    $coupleFile->append('*');
                }
                $coupleFile->append($couple->club);
                $coupleFile->append($couple->country);
                $coupleFile->append($couple->resultPosition);
                $coupleFile->append($couple->resultPoints);
                $coupleFile->append($couple->marker);
                $coupleFile->append($couple->plIdA);
                $coupleFile->append($couple->plIdB);
                $coupleFile->append('');
            } else {
                $coupleFile->set($i);
                $coupleFile->append(';;;;;;;;;;;');
            }
            $coupleFile->write();
        }
        $coupleFile->close();

        return true;
    }

    // temporary public for test purposes
    public function getDanceRecords()
    {
        $this->readDances();

        return $this->dances;
    }

    // ---------------------------------------------------

    public function __construct()
    {
        $this->lastError = 0;
        $this->haveProperty = false;
        $this->haveJudges = false;
        $this->haveJudgesDB = false;
        $this->haveCSV = false;
        $this->haveCouples = false;
        $this->judges = [];
        $this->judgesDB = [];
    }

    private function convert($text, $cp = 1250)
    {
        if ($cp == 1252) {
            $cp = 1250;
        }
        $cpName = 'CP'.$cp;

        return CPConverter::convert($text, $cpName, 'UTF-8');
    }

    private function readProperty()
    {
        if ($this->haveProperty) {
            return;
        }
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_COMPETITION);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        if (! $db->selectRecord(0)) {
            $this->lastError = self::ERROR_RECORD;

            return;
        }
        $this->eventName = $db->readStringAt(self::FIELD_COMPETITION_NAME, self::FIELD_SIZE_COMPETITION_NAME);
        $name2 = $db->readStringAt(self::FIELD_COMPETITION_NAME2, self::FIELD_SIZE_COMPETITION_NAME2);
        $this->eventId = $db->readStringAt(self::FIELD_COMPETITION_ID, self::FIELD_SIZE_COMPETITION_ID);
        $db->close();

        if (strlen($name2) > 0) {
            $this->eventName = $this->eventName.' '.$name2;
        }
        $this->eventName = $this->convert($this->eventName, $db->codePage());

        $this->haveProperty = true;
    }

    private function readJudges()
    {
        if ($this->haveJudges) {
            return;
        }
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_JUDGES);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }

        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $judge = new Judge;
            $judge->sign = $db->readStringAt(self::FIELD_JUDGES_SIGN, self::FIELD_SIZE_JUDGES_SIGN);
            // if(strlen($judge->sign) != 1 || $judge->sign[0] < 'A' || $judge->sign[0] > 'Z')
            //   continue; //read all
            $judge->firstName = $db->readStringAt(self::FIELD_JUDGES_FIRST_NAME, self::FIELD_SIZE_JUDGES_FIRST_NAME);
            $judge->lastName = $db->readStringAt(self::FIELD_JUDGES_LAST_NAME, self::FIELD_SIZE_JUDGES_LAST_NAME);
            $judge->plId = ''; // PTT database is inconsistent $db->readStringAt(self::FIELD_JUDGES_PL_ID, self::FIELD_SIZE_JUDGES_PL_ID);
            $judge->plId2 = $db->readStringAt(self::FIELD_JUDGES_PL_ID, self::FIELD_SIZE_JUDGES_PL_ID); // for reports only, do not use it for reference
            $judge->dbId = $db->readLongAt(self::FIELD_JUDGES_DB_ID);
            $judge->roundId = $db->readLongAt(self::FIELD_JUDGES_ROUND_ID);
            $judge->city = $db->readStringAt(self::FIELD_JUDGES_CITY, self::FIELD_SIZE_JUDGES_CITY);
            $judge->country = $db->readStringAt(self::FIELD_JUDGES_COUNTRY, self::FIELD_SIZE_JUDGES_COUNTRY);
            $judge->category = $db->readStringAt(self::FIELD_JUDGES_CATEGORY, self::FIELD_SIZE_JUDGES_DB_CATEGORY);
            // if( strlen($judge->firstName) && strlen($judge->lastName) )
            $this->judges[] = $judge;
        }
        $db->close();

        foreach ($this->judges as $judge) {
            $judge->firstName = $this->convert($judge->firstName, $db->codePage());
            $judge->lastName = $this->convert($judge->lastName, $db->codePage());
            $judge->sign = $this->convert($judge->sign, $db->codePage());
            $judge->city = $this->convert($judge->city, $db->codePage());
            $judge->country = $this->convert($judge->country, $db->codePage());
            $judge->category = $this->convert($judge->category, $db->codePage());
        }
        $this->haveJudges = true;
    }

    private function readJudgesDB()
    {
        if ($this->haveJudgesDB) {
            return;
        }
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_ALL_JUDGES);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $judge = new JudgeDB;
            $judge->plId = $db->readStringAt(self::FIELD_JUDGES_DB_ID, self::FIELD_SIZE_JUDGES_PL_ID);
            $judge->firstName = $db->readStringAt(self::FIELD_JUDGES_DB_FIRST_NAME, self::FIELD_SIZE_JUDGES_FIRST_NAME);
            $judge->lastName = $db->readStringAt(self::FIELD_JUDGES_DB_LAST_NAME, self::FIELD_SIZE_JUDGES_LAST_NAME);
            $judge->city = $db->readStringAt(self::FIELD_JUDGES_DB_CITY, self::FIELD_SIZE_JUDGES_CITY);
            $judge->country = $db->readStringAt(self::FIELD_JUDGES_DB_COUNTRY, self::FIELD_SIZE_JUDGES_DB_COUNTRY);
            if (strlen($judge->country) == 0) {
                $judge->country = 'Polska';
            }
            $judge->categoryJ = $db->readStringAt(self::FIELD_JUDGES_DB_CATEGORY_J, self::FIELD_SIZE_JUDGES_DB_CATEGORY);
            $judge->categoryS = $db->readStringAt(self::FIELD_JUDGES_DB_CATEGORY_S, self::FIELD_SIZE_JUDGES_DB_CATEGORY);
            $judge->sign = ' ';
            $this->judgesDB[] = $judge;
        }
        $db->close();

        foreach ($this->judgesDB as $judge) {
            $judge->firstName = $this->convert($judge->firstName, $db->codePage());
            $judge->lastName = $this->convert($judge->lastName, $db->codePage());
            $judge->city = $this->convert($judge->city, $db->codePage());
            $judge->country = $this->convert($judge->country, $db->codePage());
            $judge->categoryJ = $this->convert($judge->categoryJ, $db->codePage());
            $judge->categoryS = $this->convert($judge->categoryS, $db->codePage());
        }
        $this->haveJudgesDB = true;
    }

    private function readCSV()
    {
        if ($this->haveCSV) {
            return;
        }
        $counter = 0;
        $line = false;
        $name = false;
        $section = false;

        if (! file_exists($this->listycsvname)) {
            return false;
        }

        $file = @fopen($this->listycsvname, 'r');
        if ($file === false) {
            return false;
        }

        while (true) {
            $counter++;
            $line = fgets($file);

            if ($line === false) {
                break;
            }
            $parts = explode(';', $line);
            // judge - 8
            // category - 13
            // couple - 16

            if ($counter == 2 && ($parts[0] != $this->getEventId())) {// should be competition number
                return false;
            } // only read file for this competition

            if (count($parts) < 5) {
                continue;
            }

            if ($counter > 2 &&
                 ((count($parts) == 8 && (strlen($parts[0]) < 3 || $this->convert($parts[0]) == 'Główny')) ||
                 (count($parts) == 15 && is_numeric($parts[5])))) {
                // judges
                $judge = new JudgeDB;
                $judge->plId = trim($parts[5]);
                $judge->firstName = $this->convert(substr(trim($parts[2]), 0, 15));
                $judge->lastName = $this->convert(substr(trim($parts[1]), 0, 25));
                $judge->city = $this->convert(substr(trim($parts[3]), 0, 25));
                $judge->country = $this->convert(substr(trim($parts[4]), 0, 12));
                $judge->sign = ($this->convert($parts[0]) == 'Główny') ? '#' : trim($parts[0]);
                if (strlen($judge->country) == 0) {
                    $judge->country = 'Polska';
                }
                $judge->category = trim($parts[6]);
                $this->judgesCSV = Arr::add($this->judgesCSV, $judge->plId, $judge);
            } elseif ($counter > 2 &&
                  ((count($parts) == 13 && ! is_numeric($parts[0]) && $parts[5] == '' && $parts[6] == '') ||
                  ((count($parts) == 15 && $parts[5] == '' && $parts[6] == '')))) {
                // category name
                $category = new SchedulePart;
                if (mb_strpos(mb_strtoupper(trim($parts[2]), 'UTF-8'), 'KOMB') !== false) {
                    $parts[2] = 'Komb';
                }
                $name = $this->convert($parts[0].' '.$parts[1].' '.$parts[2]); // original name
                $category->name = $name;
                if ($parts[3] != '') {
                    // $category->part = $parts[3];
                    $category->part = ($parts[3] == 1 ? 'I' :
                                      ($parts[3] == 2 ? 'II' :
                                      ($parts[3] == 3 ? 'III' :
                                      ($parts[3] == 4 ? 'IV' :
                                      ($parts[3] == 5 ? 'V' :
                                      ($parts[3] == 6 ? 'VI' :
                                      ($parts[3] == 7 ? 'VII' :
                                      ($parts[3] == 8 ? 'VIII' :
                                      ($parts[3] == 9 ? 'IX' : '0')))))))));
                } else {
                    $category->part = '0';
                }
                $section = $category->part;
                $this->categoriesCSV[] = $category;
            } elseif ($counter > 2 && count($parts) > 14 && is_numeric($parts[0]) && is_numeric($parts[10]) /* && is_numeric($parts[11]) */) {
                // couples
                $couple = new Couple;
                if (is_numeric($parts[0])) {
                    $couple->number = $parts[0];
                } else {
                    $couple->number = 0;
                }
                $couple->roundId = $name;
                $couple->plIdA = $parts[10];
                $couple->plIdB = $parts[11];
                $couple->firstNameA = $this->convert(trim($parts[2]));
                $couple->lastNameA = $this->convert(trim($parts[1]));
                $couple->firstNameB = $this->convert(trim($parts[4]));
                $couple->lastNameB = $this->convert(trim($parts[3]));
                $couple->club = $this->convert(trim($parts[5]));
                $couple->country = $this->convert(trim($parts[6]));
                $couple->resultPosition = $parts[7];
                $couple->resultPoints = $parts[8];
                // $couple->resultPodium =
                // $couple->manualPosition =
                $couple->marker = $parts[9];
                $couple->section = $section;
                $this->couplesCSV[] = $couple;
            }
        }
        /*usort($this->categoriesCSV, function($a, $b) {
           return( $a->part > $b->part );
        });*/
        fclose($file);
        $this->haveCSV = true;
    }

    public function existListyCSV()
    {
        if (! file_exists($this->listycsvname)) {
            return false;
        }

        $file = @fopen($this->listycsvname, 'r');
        if ($file === false) {
            return false;
        }

        return true;
    }

    private function splitDanceNames($names)
    {
        $split = [];
        // $split = preg_split('/\s+/', $names);
        $triple = 0;
        $name = '';
        for ($i = 0; $i < strlen($names); $i++) {
            $ch = $names[$i];
            $triple = $triple + 1;
            if (ord($ch) > 0x20) {
                $name = $name.$ch;
            }
            if (ord($ch) <= 0x20 || $triple >= 3) { // separator or 3 chars
                if (strlen($name) > 0) { // there was a name of dance
                    $split[] = $name;
                    $name = '';
                }
                $triple = 0;
            }
        }
        if (strlen($name) > 0) { // last one
            $split[] = $name;
        }

        return $split;
    }

    private function readBaseRounds()
    {
        if ($this->haveBaseRounds) {
            return;
        }
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_BASE_ROUNDS);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $round = new Round;
            $round->roundId = $db->readLongAt(self::FIELD_BASE_ROUNDS_ID);
            $round->categoryName = $db->readStringAt(self::FIELD_BASE_ROUNDS_CATEGORY, self::FIELD_SIZE_BASE_ROUNDS_CATEGORY);
            $round->className = $db->readStringAt(self::FIELD_BASE_ROUNDS_CLASS, self::FIELD_SIZE_BASE_ROUNDS_CLASS);
            $round->styleName = $db->readStringAt(self::FIELD_BASE_ROUNDS_STYLE, self::FIELD_SIZE_BASE_ROUNDS_STYLE);
            $round->roundName = $db->readStringAt(self::FIELD_BASE_ROUNDS_NAME, self::FIELD_SIZE_BASE_ROUNDS_NAME);
            $danceOrder = $db->readStringAt(self::FIELD_BASE_ROUNDS_DANCES, self::FIELD_SIZE_BASE_ROUNDS_DANCES);
            $round->baseNumberOfCouples = $db->readIntAt(self::FIELD_BASE_ROUNDS_COUPLES);
            $round->competitionDanceType = $db->readStringAt(self::FIELD_BASE_ROUNDS_COMP_DANCE_TYPE, self::FIELD_SIZE_BASE_ROUNDS_COMP_DANCE_TYPE);
            $round->dances = $this->splitDanceNames($danceOrder);
            $round->startNo = $db->readIntAt(self::FIELD_BASE_ROUNDS_START_NO);
            $round->endNo = $db->readIntAt(self::FIELD_BASE_ROUNDS_END_NO);
            $this->baseRounds[] = $round;
        }
        $db->close();
        foreach ($this->baseRounds as $round) {
            $round->roundName = $this->convert($round->roundName, $db->codePage());
            $round->categoryName = $this->convert($round->categoryName, $db->codePage());
            $round->className = $this->convert($round->className, $db->codePage());
            $round->styleName = $this->convert($round->styleName, $db->codePage());
            $round->competitionDanceType = $this->convert($round->competitionDanceType, $db->codePage());
            if (strncmp($round->roundName, 'Fina', 4) == 0) {
                $round->isFinal = true;
            } else {
                $round->isFinal = false;
            }
            $round->votesRequired = 0;
            $round->baseRoundId = $round->roundId;
            $round->matchType = self::ROUND_TYPE_BASIC;
            $round->isTop = true;
            $round->isAdditional = false;
        }
        $this->haveBaseRounds = true;
    }

    private function readRounds()
    {
        if ($this->haveRounds) {
            return;
        }
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_ROUNDS);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $round = new Round;
            $round->roundId = $db->readLongAt(self::FIELD_ROUNDS_ID);
            $round->baseRoundId = $db->readLongAt(self::FIELD_ROUNDS_BASE_ROUND_ID);
            if ($db->readIntAt(self::FIELD_ROUNDS_ROUND_SEQUENCE) == 1) {
                $round->isTop = true;
            } else {
                $round->isTop = false;
            }
            $round->matchType = $db->readStringAt(self::FIELD_ROUNDS_TYPE, self::FIELD_SIZE_ROUNDS_TYPE);
            $round->matchType = $this->convert($round->matchType, $db->codePage());
            if (strncmp($round->matchType, self::ROUND_TYPE_BASIC, strlen(self::ROUND_TYPE_BASIC)) == 0) {
                $round->isAdditional = false;
            } else {
                $round->isAdditional = true;
            }
            $round->roundName = $db->readStringAt(self::FIELD_ROUNDS_NAME, self::FIELD_SIZE_ROUNDS_NAME);
            $round->roundName = $this->convert($round->roundName, $db->codePage());
            if (strncmp($round->roundName, 'Fina', 4) == 0) {
                $round->isFinal = true;
            } else {
                $round->isFinal = false;
            }
            $round->isClosed = $db->readBoolAt(self::FIELD_ROUNDS_CLOSED);
            $nCouples = $db->readIntAt(self::FIELD_ROUNDS_N_COUPLES);
            $round->votesRequired = $db->readIntAt(self::FIELD_ROUNDS_N_VOTES);
            if ($round->isFinal && $nCouples != $round->votesRequired) {
                $round->votesRequired = $nCouples;
            }
            $round->NumberOfCouples = $nCouples;
            $this->rounds[] = $round;
        }
        $db->close();

        foreach ($this->rounds as $round) {
            $baseRound = $this->getBaseRound($round->baseRoundId);
            if ($baseRound == false) {
                continue;
            }
            $round->categoryName = $baseRound->categoryName;
            $round->className = $baseRound->className;
            $round->styleName = $baseRound->styleName;
            $round->dances = $baseRound->dances;
        }
        $this->haveRounds = true;
    }

    private function dropDances()
    {
        $this->dances = null;
        $this->haveDances = false;
    }

    private function readDances($allCouples = false)
    {
        if ($this->haveDances && ! $allCouples) {
            return;
        }
        if ($allCouples) {
            $this->dropDances();
        }

        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_DANCES);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $excluded = $db->readStringAt(self::FIELD_DANCES_VOTES_1 + self::FIELD_DANCES_MAX_DANCE * self::FIELD_DANCES_OFFSET + self::FIELD_DANCES_EXCLUDED_ADD_OFFSET, self::FIELD_SIZE_DANCES_EXCLUDED);
            if (! $allCouples && $excluded != '' && $excluded != ' ' && $excluded != 'R') { // R=disqualified by 3xR but still dancing
                continue;
            }

            $dance = new DanceRecord;
            $dance->dbId = $db->readLongAt(self::FIELD_DANCES_ID);
            $dance->roundId = $db->readLongAt(self::FIELD_DANCES_ROUND_ID);
            $dance->coupleId = $db->readLongAt(self::FIELD_DANCES_COUPLE_ID);
            $dance->coupleNumber = $db->readStringAt(self::FIELD_DANCES_COUPLE_NUMBER, self::FIELD_SIZE_DANCES_COUPLE_NUMBER);
            $dance->excluded = $excluded;
            for ($n = 0; $n < self::FIELD_DANCES_MAX_DANCE; $n++) {
                $dance->notesArray[] = $db->readStringAt(self::FIELD_DANCES_VOTES_1 + $n * self::FIELD_DANCES_OFFSET, self::FIELD_SIZE_DANCES_VOTES);
                $dance->groupNumberArray[] = $db->readIntAt(self::FIELD_DANCES_GROUP_1 + $n * self::FIELD_DANCES_OFFSET);
            }
            $this->dances[] = $dance;
        }
        $db->close();

        $this->haveDances = true;
    }

    private function readCouples()
    {
        if ($this->haveCouples) {
            return;
        }

        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_COUPLES);
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return;
        }
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return;
            }
            $couple = new Couple;

            $couple->plIdA = $db->readStringAt(self::FIELD_COUPLES_PL_ID_A, self::FIELD_SIZE_COUPLES_PL_ID_A);
            $couple->plIdB = $db->readStringAt(self::FIELD_COUPLES_PL_ID_B, self::FIELD_SIZE_COUPLES_PL_ID_B);
            // if(strlen($couple->plIdA) < 1 && strlen($couple->plIdB) < 1)
            //   continue; //allow couples with no Id
            $couple->resultPosition = $db->readStringAt(self::FIELD_COUPLES_RESULT_POSITION, self::FIELD_SIZE_COUPLES_RESULT_POSITION);
            $couple->marker = $db->readStringAt(self::FIELD_COUPLES_MARKER, self::FIELD_SIZE_COUPLES_MARKER);
            // if(strlen($couple->resultPosition) < 1)
            if (strlen($couple->marker) < 1) {
                continue;
            }
            $couple->roundId = $db->readLongAt(self::FIELD_COUPLES_ROUND_ID);
            $couple->number = $db->readStringAt(self::FIELD_COUPLES_NUMBER, self::FIELD_SIZE_COUPLES_NUMBER);
            $couple->firstNameA = $db->readStringAt(self::FIELD_COUPLES_FIRST_NAME_A, self::FIELD_SIZE_COUPLES_FIRST_NAME_A);
            $couple->lastNameA = $db->readStringAt(self::FIELD_COUPLES_LAST_NAME_A, self::FIELD_SIZE_COUPLES_LAST_NAME_A);
            $couple->firstNameB = $db->readStringAt(self::FIELD_COUPLES_FIRST_NAME_B, self::FIELD_SIZE_COUPLES_FIRST_NAME_B);
            $couple->lastNameB = $db->readStringAt(self::FIELD_COUPLES_LAST_NAME_B, self::FIELD_SIZE_COUPLES_LAST_NAME_B);
            $couple->club = $db->readStringAt(self::FIELD_COUPLES_CLUB, self::FIELD_SIZE_COUPLES_CLUB);
            $couple->country = $db->readStringAt(self::FIELD_COUPLES_COUNTRY, self::FIELD_SIZE_COUPLES_COUNTRY);
            $couple->resultPoints = $db->readFloatAt(self::FIELD_COUPLES_RESULT_POINTS_AFTER);
            $couple->resultPoints -= $db->readFloatAt(self::FIELD_COUPLES_RESULT_POINTS_BEFORE);
            $couple->resultPodium = $db->readIntAt(self::FIELD_COUPLES_RESULT_PODIUM_AFTER);
            $couple->resultPodium -= $db->readIntAt(self::FIELD_COUPLES_RESULT_PODIUM_BEFORE);
            $this->couples[] = $couple;
        }
        $db->close();

        foreach ($this->couples as $couple) {
            $couple->firstNameA = $this->convert($couple->firstNameA, $db->codePage());
            $couple->lastNameA = $this->convert($couple->lastNameA, $db->codePage());
            $couple->firstNameB = $this->convert($couple->firstNameB, $db->codePage());
            $couple->lastNameB = $this->convert($couple->lastNameB, $db->codePage());
            $couple->club = $this->convert($couple->club, $db->codePage());
            $couple->country = $this->convert($couple->country, $db->codePage());
            $couple->manualPosition = 0;
        }

        $manualResults = new ManualResults($this->manualResultsName);
        $manualResults->read();
        foreach ($manualResults->results as $result) {
            foreach ($this->couples as $couple) {
                if ($couple->roundId == $result->roundId && $couple->number == $result->coupleNumber) {
                    $couple->manualPosition = $result->position;
                }
            }
        }
        $this->haveCouples = true;
    }

    private function writeDanceResult($record, $danceNumber)
    {
        $db = new DatabaseFile;
        $db->open($this->folder, self::DB_DANCES, true); // open to write access
        if ($db->isError()) {
            $this->lastError = self::ERROR_FILE;

            return false;
        }
        $written = false;
        for ($i = 0; $i < $db->numberOfRecords(); $i++) {
            if (! $db->selectRecord($i)) {
                $this->lastError = self::ERROR_RECORD;

                return false;
            }
            if ($record->dbId != $db->readLongAt(self::FIELD_DANCES_ID)) {
                continue;
            }
            $offset = self::FIELD_DANCES_VOTES_1 + ($danceNumber - 1) * self::FIELD_DANCES_OFFSET;
            $length = self::FIELD_SIZE_DANCES_VOTES;
            $db->writeStringAt($record->notesArray[$danceNumber - 1], $offset, $length);
            $written = $db->storeRecord($i, $offset, $length);

            // clear sum of all votes
            if (! $written) {
                break;
            }
            $offset = self::FIELD_DANCES_SUM;
            $db->writeFloatAt(0, $offset);
            $written = $db->storeRecord($i, $offset, self::DB_SIZE_OF_FLOAT);
            if (! $written) {
                break;
            }
            // write string in place
            $offset = self::FIELD_DANCES_PLACE;
            $length = self::FIELD_SIZE_DANCES_PLACE;
            $db->writeStringAt('-POLICZ-', $offset, $length);
            $written = $db->storeRecord($i, $offset, $length);
            break;
        }
        $db->close();

        if ($written == false) {
            $this->dropDances();
        }

        return $written;
    }

    private function getBaseRoundId($roundId)
    {
        $this->readBaseRounds();
        $this->readRounds();
        foreach ($this->rounds as $round) {
            if ($round->roundId == $roundId) {
                return $round->baseRoundId;
            }
        }

        return 0;
    }

    private function findRoundByDescription($description, $matchType)
    {
        $this->readBaseRounds();
        $this->readRounds();
        if (count($this->rounds) < 1) {
            return false;
        }
        foreach ($this->rounds as $round) {
            $info = $this->createRoundDescription($round);
            $pos = strpos($description, $info);
            if ($pos !== false && strcmp($round->matchType, $matchType) == 0) {
                return $round;
            }
        }

        return false;
    }

    private function createRoundDescription($round)
    {
        $info = $round->roundName.' '.$round->categoryName.' '.$round->className.' '.$round->styleName;

        return $info;
    }

    private function getDanceNumberInRound($roundId, $danceSignature)
    {
        $round = $this->getRound($roundId);
        if ($round === false) {
            return false;
        }

        for ($i = 0; $i < count($round->dances); $i++) {
            if (strcmp($round->dances[$i], $danceSignature) == 0) {
                return $i + 1;
            }
        }

        return 0; // mark unexisting dance
    }

    private function getDanceRecord($roundId, $coupleNumber)
    {
        $this->readDances();
        foreach ($this->dances as $dance) {
            if ($dance->roundId == $roundId && $dance->coupleNumber == $coupleNumber) {
                return $dance;
            }
        }

        return false;
    }

    private function unpackDanceResults($dbResult)
    {
        $result = new DanceResult;
        for ($i = 0; $i < strlen($dbResult); $i++) {
            $dbNote = substr($dbResult, $i, 1);
            $vote = new Vote;
            $vote->judgeSign = chr(ord('A') + $i);
            $vote->note = ' ';
            $vote->rmark = false;
            if ($dbNote >= '0' && $dbNote <= '9') {
                $vote->note = $dbNote;
            }
            if ($dbNote == 'x' || $dbNote == 'X' || $dbNote == 'R') {
                $vote->note = 'X';
            }
            if ($dbNote == 'r' || $dbNote == 'R') {
                $vote->rmark = true;
            }
            if ($dbNote >= 'A' && $dbNote <= 'I') {
                $vote->note = chr(ord('1') + ord($dbNote) - ord('A'));
                $vote->rmark = true;
            }
            $result->setVote($vote);
        }

        return $result;
    }

    private function packDanceResults($result)
    {
        $dbResults = '';
        $dbResult = '';
        $sumVotes = 0;
        for ($i = 0; $i < self::FIELD_SIZE_DANCES_VOTES; $i++) {
            $judgeSign = chr(ord('A') + $i);
            $vote = $result->getVote($judgeSign);
            if ($vote === false) {
                $dbResult = ' ';
            } else {
                if ($vote->note == 'X' || $vote->note == 'x') {
                    if ($vote->rmark) {
                        $dbResult = 'R';
                    } else {
                        $dbResult = 'X';
                    }
                    $sumVotes += 1;
                }
                if ($vote->note == ' ' || $vote->note == '') {
                    if ($vote->rmark) {
                        $dbResult = 'r';
                    } else {
                        $dbResult = ' ';
                    }
                }
                if ($vote->note >= '1' && $vote->note <= '9') {
                    if ($vote->rmark) {
                        $dbResult = chr(ord('A') + ord($vote->note) - ord('1'));
                    } else {
                        $dbResult = chr(ord($vote->note));
                    } // to be sure of 1 char
                    $sumVotes += intval($vote->note);
                }
            }
            $dbResults = $dbResults.$dbResult;
        }
        $dbResults = rtrim($dbResults);
        $res = new DanceDatabaseResult;
        $res->notes = $dbResults;
        $res->sum = $sumVotes;

        return $res;
    }

    // array: ['judge']=>'A-W' ['note']=>''/'X'/'1-n' ['rmark']=>true/false
    private function unpackVotes($dbVotes)
    {
        $arr = [];
        for ($i = 0; $i < strlen($dbVotes); $i++) {
            $note = substr($dbVotes, $i, 1);
            $rmark = false;
            if ($note < '0' || $note > '9') {
                if ($note == ' ') {
                    $note = '';
                } elseif ($note == 'X' || $note == 'x') {
                    $note = 'X';
                } else {
                    $rmark = true;
                    if ($note == 'r' || $note == 'R') {
                        $note = '';
                    } else {
                        $note = chr(ord('1') + ord($note) - ord('A'));
                    }
                }
            }
            $vote = ['judge' => chr(ord('A') + $i), 'note' => $note, 'rmark' => $rmark];
            $arr[] = $vote;
        }

        return $arr;
    }

    private function collectVotes($roundId, $danceSignature, $judgeSign, $votes)
    {
        $round = $this->getRound($roundId);
        $collectionFile = new CollectionFile($this->votesname);

        $lock = new LockFile($this->lockname);
        $lock->acquire();
        if ($round !== false) {
            $collectionFile->appendDescription($roundId, $danceSignature, $judgeSign, $votes, $this->createRoundDescription($round), $round->matchType);
        } else {
            $collectionFile->append($roundId, $danceSignature, $judgeSign, $votes);
        }
        $lock->release();
    }

    private function saveVotes($roundId, $danceSignature, $judgeSign, $votes)
    {
        $this->getRounds();
        $this->readDances();

        if (count($this->dances) < 1) {
            return false;
        }

        $danceNumber = $this->getDanceNumberInRound($roundId, $danceSignature);
        if ($danceNumber == false || $danceNumber < 1 || $danceNumber > self::FIELD_DANCES_MAX_DANCE) {
            return false;
        }

        $round = $this->getRound($roundId);
        $isFinal = $round->isFinal;

        if ($round->isClosed) {
            return false;
        }

        foreach ($this->dances as $record) {
            if ($record->roundId == $roundId) {
                if (! array_key_exists($record->coupleNumber, $votes)) {
                    continue;
                }
                $newVote = $votes[$record->coupleNumber];
                $vote = new Vote;
                $vote->note = $newVote['note'];
                $vote->rmark = $newVote['rmark'] == 'true' ? true : false;

                $vote->judgeSign = $judgeSign;
                $dbResults = $record->notesArray[$danceNumber - 1];
                $result = $this->unpackDanceResults($dbResults);
                $result->setVote($vote);
                $dbResults = $this->packDanceResults($result);
                $record->notesArray[$danceNumber - 1] = $dbResults->notes;
                $record->sumArray[$danceNumber - 1] = $dbResults->sum;
                $this->writeDanceResult($record, $danceNumber);
            }
        }

        return true;
    }

    private function parseSchedule($scheduleText)
    {
        $schedule = [];
        if (strncmp($scheduleText, self::SCHEDULE_HEADER, strlen(self::SCHEDULE_HEADER)) != 0) {
            return $schedule;
        }
        $state = 0; // 0->start of line, 1-start of item
        $name = '';
        $triple = 0;
        $item = new ScheduledRound;
        $scheduleText = $scheduleText.';';  // end sentinel
        for ($i = strlen(self::SCHEDULE_HEADER); $i < strlen($scheduleText); $i++) {
            $ch = $scheduleText[$i];
            if (ord($ch) < 0x20) {
                if ($state == 2) {
                    $ch = ';';
                } else {
                    continue;
                }
            }
            if ($state == 0 && $ch == ';') { // start of line, new item
                $state = 1; // item to read

                continue;
            }
            if ($state == 1) { // start of item "<magic number>1/2 final..."
                if (ord($ch) >= 0x30 && ord($ch) <= 0x39) {
                    $name = '';
                    $item = new ScheduledRound;
                    $state = 2;
                }

                continue;
            }
            if ($state == 2) { // round description "1/2 final ...("
                if ($ch == '(') { // dances follow
                    $item->isDance = true;
                    $item->description = $this->convert($name);
                    $name = '';
                    $triple = 0;
                    $state = 3;
                } elseif ($ch == ';') { // end, no dance
                    $item->description = $this->convert($name);
                    if (strlen($item->description) > 0) {
                        $schedule[] = $item;
                    }
                    $state = 0; // new line
                    $i--; // look again for separator
                } else {
                    $name = $name.$ch;
                }

                continue;
            }
            if ($state == 3) { // dances
                if (ord($ch) <= 0x20 || $ch == ')' || $ch == ';') { // separator or end
                    if (strlen($name) > 0) { // there was a name of dance
                        $item->dances[] = $name;
                        $name = '';
                    }
                    if ($ch == ')' || $ch == ';') { // end
                        if (strlen($item->description) > 0) {
                            $schedule[] = $item;
                        }
                        if ($ch == ';') {
                            $state = 1;
                        } // next item
                        else {
                            $state = 0;
                        } // next line
                    }
                    $triple = 0;
                } else {
                    $name = $name.$ch;
                    $triple = $triple + 1;
                    if ($triple >= 3) {
                        if (strlen($name) > 0) { // there was a name of dance
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

    private $folder;

    private $lockname;

    private $votesname;

    private $reportname;

    private $manualResultsName;

    private $lastError;

    private $haveProperty;

    private $haveJudges;

    private $haveJudgesDB;

    private $haveCSV;

    private $haveBaseRounds;

    private $haveRounds;

    private $haveDances;

    private $haveCouples;

    private $eventName;

    private $eventId;

    private $listycsvname;

    private $judges = [];

    private $judgesDB = [];

    private $judgesCSV = [];

    private $categoriesCSV = [];

    private $baseRounds = [];

    private $rounds = [];

    private $dances = [];

    private $couples = [];

    private $couplesCSV = [];

    const ERROR_FILE = 1;

    const ERROR_RECORD = 2;

    const DB_SIZE_OF_FLOAT = 8;

    const SCHEDULE_HEADER = 'Program_turnieju';

    const ROUND_TYPE_BASIC = 'Eliminacje';

    const ROUND_TYPE_ADDITIONAL = 'Dodatkowa';

    const ROUND_TYPE_PLAYOFF = 'Baraż';

    const DB_LOCK = 'lock.tmp';

    const DB_VOTES = 'votes.sav';

    const DB_REPORT = 'impreza.csv';

    const DB_ALL_JUDGES = 'Baza_sedziowie';

    const DB_LISTY_CSV = 'Listy_';

    const DB_MANUAL_RESULTS = 'zmodify.zav';

    const DB_COMPETITION = 'Organizacja';

    const DB_JUDGES = 'Sedziowie';

    const DB_BASE_ROUNDS = 'Struktura';

    const DB_ROUNDS = 'Struktura_rund';

    const DB_DANCES = 'Rundy';

    const DB_COUPLES = 'Pary';

    const FIELD_COMPETITION_NAME = 0;

    const FIELD_COMPETITION_NAME2 = 70;

    const FIELD_COMPETITION_ID = 490; // 7*70

    const FIELD_JUDGES_DB_ID = 0;

    const FIELD_JUDGES_ROUND_ID = 4;

    const FIELD_JUDGES_SIGN = 8;

    const FIELD_JUDGES_LAST_NAME = 14;

    const FIELD_JUDGES_FIRST_NAME = 39; // 14+25

    const FIELD_JUDGES_CITY = 54;

    const FIELD_JUDGES_COUNTRY = 79;

    const FIELD_JUDGES_PL_ID = 91;

    const FIELD_JUDGES_CATEGORY = 97;

    const FIELD_JUDGES_DB_LAST_NAME = 72;

    const FIELD_JUDGES_DB_FIRST_NAME = 97;

    const FIELD_JUDGES_DB_CITY = 112;

    const FIELD_JUDGES_DB_COUNTRY = 137;

    const FIELD_JUDGES_DB_CATEGORY_J = 154;

    const FIELD_JUDGES_DB_CATEGORY_S = 169;

    const FIELD_BASE_ROUNDS_ID = 0;

    const FIELD_BASE_ROUNDS_CATEGORY = 4;

    const FIELD_BASE_ROUNDS_CLASS = 24;

    const FIELD_BASE_ROUNDS_STYLE = 44;

    const FIELD_BASE_ROUNDS_NAME = 84;

    const FIELD_BASE_ROUNDS_DANCES = 104;

    const FIELD_BASE_ROUNDS_COUPLES = 149;

    const FIELD_BASE_ROUNDS_START_NO = 155;

    const FIELD_BASE_ROUNDS_END_NO = 157;

    const FIELD_BASE_ROUNDS_COMP_DANCE_TYPE = 160;

    const FIELD_ROUNDS_ID = 0;

    const FIELD_ROUNDS_BASE_ROUND_ID = 4;

    const FIELD_ROUNDS_ROUND_SEQUENCE = 8;

    const FIELD_ROUNDS_TYPE = 10;

    const FIELD_ROUNDS_NAME = 30;

    const FIELD_ROUNDS_N_COUPLES = 50;

    const FIELD_ROUNDS_CLOSED = 58;

    const FIELD_ROUNDS_N_VOTES = 59; // 58+boolean

    const FIELD_DANCES_ID = 0;

    const FIELD_DANCES_ROUND_ID = 4;

    const FIELD_DANCES_COUPLE_ID = 8;

    const FIELD_DANCES_COUPLE_NUMBER = 12;

    const FIELD_DANCES_VOTES_1 = 16;

    const FIELD_DANCES_SUM_1 = 37;

    const FIELD_DANCES_GROUP_1 = 54;

    const FIELD_DANCES_OFFSET = 48;

    const FIELD_DANCES_MAX_DANCE = 15;

    const FIELD_DANCES_SUM = 729;

    const FIELD_DANCES_PLACE = 737;

    const FIELD_DANCES_EXCLUDED_ADD_OFFSET = -8;

    const FIELD_COUPLES_ROUND_ID = 4;

    const FIELD_COUPLES_NUMBER = 125;

    const FIELD_COUPLES_PL_ID_A = 190;

    const FIELD_COUPLES_PL_ID_B = 196;

    const FIELD_COUPLES_FIRST_NAME_A = 33;

    const FIELD_COUPLES_LAST_NAME_A = 8;

    const FIELD_COUPLES_FIRST_NAME_B = 73;

    const FIELD_COUPLES_LAST_NAME_B = 48;

    const FIELD_COUPLES_CLUB = 88;

    const FIELD_COUPLES_COUNTRY = 113;

    const FIELD_COUPLES_RESULT_POSITION = 158;

    const FIELD_COUPLES_RESULT_POINTS_BEFORE = 129;

    const FIELD_COUPLES_RESULT_POINTS_AFTER = 139;

    const FIELD_COUPLES_RESULT_PODIUM_BEFORE = 137;

    const FIELD_COUPLES_RESULT_PODIUM_AFTER = 147;

    const FIELD_COUPLES_MARKER = 149;

    const FIELD_SIZE_COMPETITION_NAME = 70;

    const FIELD_SIZE_COMPETITION_NAME2 = 70;

    const FIELD_SIZE_COMPETITION_ID = 12;

    const FIELD_SIZE_JUDGES_SIGN = 6;

    const FIELD_SIZE_JUDGES_FIRST_NAME = 15;

    const FIELD_SIZE_JUDGES_LAST_NAME = 25;

    const FIELD_SIZE_JUDGES_PL_ID = 6;

    const FIELD_SIZE_JUDGES_CITY = 25;

    const FIELD_SIZE_JUDGES_COUNTRY = 12;

    const FIELD_SIZE_JUDGES_DB_COUNTRY = 15;

    const FIELD_SIZE_JUDGES_DB_CATEGORY = 15;

    const FIELD_SIZE_BASE_ROUNDS_CATEGORY = 20;

    const FIELD_SIZE_BASE_ROUNDS_CLASS = 20;

    const FIELD_SIZE_BASE_ROUNDS_STYLE = 20;

    const FIELD_SIZE_BASE_ROUNDS_NAME = 20;

    const FIELD_SIZE_BASE_ROUNDS_DANCES = 45;

    const FIELD_SIZE_BASE_ROUNDS_COMP_DANCE_TYPE = 20;

    const FIELD_SIZE_ROUNDS_TYPE = 20;

    const FIELD_SIZE_ROUNDS_NAME = 20;

    const FIELD_SIZE_DANCES_COUPLE_NUMBER = 4;

    const FIELD_SIZE_DANCES_VOTES = 21;

    const FIELD_SIZE_DANCES_EXCLUDED = 1;

    const FIELD_SIZE_DANCES_PLACE = 10;

    const FIELD_SIZE_COUPLES_NUMBER = 4;

    const FIELD_SIZE_COUPLES_PL_ID_A = 6;

    const FIELD_SIZE_COUPLES_PL_ID_B = 6;

    const FIELD_SIZE_COUPLES_FIRST_NAME_A = 15;

    const FIELD_SIZE_COUPLES_LAST_NAME_A = 25;

    const FIELD_SIZE_COUPLES_FIRST_NAME_B = 15;

    const FIELD_SIZE_COUPLES_LAST_NAME_B = 25;

    const FIELD_SIZE_COUPLES_CLUB = 25;

    const FIELD_SIZE_COUPLES_COUNTRY = 12;

    const FIELD_SIZE_COUPLES_RESULT_POSITION = 7;

    const FIELD_SIZE_COUPLES_MARKER = 2;
}
