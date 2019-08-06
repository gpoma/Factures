<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use GrahamCampbell\GitHub\GitHubManager;

class DownloadCommand extends Command
{
    /**
     * Le dossier où sera stocké les fichiers csv
     *
     * @var string
     */
    protected $tmp = __DIR__.'/../../tmp/';

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'download
                           {periode : Le sous dossier de la periode (requis) [i.e. YYYYMMDD]}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Télécharge les fichiers CSV';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(GithubManager $github)
    {
        $periode = $this->argument('periode');
        $repo_path = env('ARCHIVE_DIR') . '/' . $periode;
        $github_username = env('USERNAME');
        $github_repo = env('REPO');

        $this->info('Checking existing path : ' . $repo_path);
        if (! $github->connection('none')->api('repo')->contents()->exists($github_username, $github_repo, $repo_path)) {
            $this->error('The path does not exists. Did you type the right period ?');
            return false;
        }

        $save_directory = implode(DIRECTORY_SEPARATOR, [$this->tmp, $periode]);
        if (! file_exists($save_directory)) {
            mkdir($save_directory);
        }

        $this->info('Entering ' . $repo_path);
        $files = $github->connection('none')->api('repo')->contents()->show($github_username, $github_repo, $repo_path);
        $this->info(count($files) . ' files');

        foreach ($files as $file) {
            $source = fopen($file['download_url'], 'r');
            $dest = fopen(implode(DIRECTORY_SEPARATOR, [$save_directory, $file['name']]), 'w+');

            if ($source === false || $dest === false) {
                fclose($source);
                fclose($dest);
                $this->error('Unable to open ' . $file['html_url']);
                continue;
            }

            if (stream_copy_to_stream($source, $dest)) {
                $this->info($file['name'] . ' successfully saved.');
            } else {
                $this->error('Failed to save ' . $file['name']);
            }
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
