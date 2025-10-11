<?php namespace App\Http\Controllers;

require_once 'pttcompetitionPTT.php';
include 'pttcompetitionW.php';

class Competition
{
   public static function create($folder) {
      $filename = str_replace('\\', '/', $folder);
      while(substr($filename, -1) == '/')
         $filename = substr($filename, 0, strlen($filename) - 1);
      if( file_exists($filename . '/' . self::DB_COMPETITION_FILE) ){
         $competition = new CompetitionPTT();
      }
      else if( file_exists($filename . '/' . self::DB_COMPETITION_FILE_W) ){
         $competition = new CompetitionW();
      }
      else{
         $competition = new CompetitionPTT(); //no database
      }
      $competition->connect($folder);
      return $competition;
   }

   const DB_COMPETITION_FILE = 'Organizacja.DB';
   const DB_COMPETITION_FILE_W = 'SYSTEM/ORGANTUR.DBF';
}

?>
