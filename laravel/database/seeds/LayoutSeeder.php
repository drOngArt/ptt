<?php
use App\Layout;
use Illuminate\Database\Seeder;

class LayoutSeeder extends Seeder {

    public function run()
    {
        DB::table('layout')->delete();
      
        $layout = new Layout();
        $layout->startTime = '10:00';
        $layout->durationRound = 95;
        $layout->durationFinal = 105;
        $layout->parameter1 = 15;
        $layout->parameter2 = 15;
        $layout->save();
    }
}