<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;
// use App\Models\User;
use App\Models\Chart;
use App\Services\Spotify;

class GenerateUsersCharts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chart:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate users\' weekly charts';

    protected $spotify;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Spotify $spotify)
    {
        parent::__construct();

        $this->spotify = $spotify;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Starting chart:generate ...');

        Chart::whereNull('is_stopped')
            ->chunk(100, function($charts) {
                foreach ($charts as $chart) {
                    $this->info('Generating chart ' . $chart->name);

                    $this->spotify->generateChart($chart);

                    $this->info('Generated chart for ' . $chart->name . '!');
                }
            });

        $this->info('All charts generated successfully!');
        Log::info('[Commands\GenerateUsersCharts] All charts generated successfully!');
    }
}
