<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;

class GenerateCommand extends Command
{
    /**
     * Le dossier où sera stocké les fichiers csv
     *
     * @var string
     */
    protected $tmp = __DIR__.'/../../tmp';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'generate
                            {periode : La période à générer}
                            {--name=all : Le fichier à générer}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Génère les factures de la période donnée';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $files = [];
        $periode = $this->argument('periode');
        $to_generate = $this->option('name');
        $saved_directory = implode(DIRECTORY_SEPARATOR, [$this->tmp, $periode, '']);

        if ($to_generate === 'all') {
            $files = glob($saved_directory . '*.csv');
        } else {
            foreach (explode(',', $to_generate) as $name) {
                $files[] = $saved_directory . $name . '.csv';
            }
        }

        foreach ($files as $file) {
            if (! file_exists($file)) {
                $this->error(basename($file, '.csv') . ' does not exists');
                continue;
            }

            $reader = Reader::createFromPath($file, 'r');
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
