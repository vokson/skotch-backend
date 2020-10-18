<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StatisticController;

class GetCountOfMistakesByProbability extends Command
{
    protected $signature = 'checker:probability';
    protected $description = 'Get count of mistakes by probability';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $value = StatisticController::getCountOfMistakesByProbability(
            SettingsController::take('RATING_MISTAKE_PROBABILITY')
        );

        SettingsController::save('RATING_MISTAKE_COUNT',$value);
        $this->info('RATING_MISTAKE_COUNT set to '. $value);

        return 0;
    }
}
