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
     * Le template de création de numéro de facture
     *
     * @var string
     */
    protected $placeholder_numero = '%d';

    /**
     * Le template de création de numéro de facture
     *
     * @var string
     */
    protected $placeholder_file = '%s_Facture24eme_%s';

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
            $client = basename($file, '.csv');
            if (! file_exists($file)) {
                $this->error($client . ' does not exists');
                continue;
            }

            if (in_array($client, config('factures.blacklist'))) {
                continue;
            }

            $no_facture = date('Ymd').str_pad('XXX', 5, 0, STR_PAD_LEFT);

            $input_reader = Reader::createFromPath($file, 'r');
            $input_reader->setHeaderOffset(0);
            $input_reader->setDelimiter(';');

            $temps_total = [];
            foreach ($input_reader->fetchPairs(4, 3) as $tache => $temps_ligne) {
                if (! array_key_exists($tache, $temps_total)) {
                    $temps_total[$tache] = 0;
                }
                $temps_total[$tache] += floatval(str_replace(',', '.', $temps_ligne));
            }

            $template = IOFactory::load(config('factures.template'));
            $template->getDefaultStyle()->getFont()->setName('Liberation Sans');
            $template->getDefaultStyle()->getFont()->setSize(8);

            $worksheet = $template->getActiveSheet();

            $cell_date = $worksheet->getCell('E1');
            $date_value = $cell_date->getValue();
            $cell_date->setValue(str_replace('%%date_court%%', date('d/m/Y'), $date_value));

            $worksheet->getCell('B7')->setValue(config('factures.societe.name'));
            $worksheet->getCell('B8')->setValue(config('factures.societe.statut'));
            $worksheet->getCell('B9')->setValue(config('factures.societe.statut2'));
            $worksheet->getCell('B10')->setValue(config('factures.societe.adresse'));
            $worksheet->getCell('B11')->setValue(config('factures.societe.cp'));
            $email = $worksheet->getCell('B12')->getValue();
            $worksheet->getCell('B12')->setValue(
                str_replace('%%societe_email%%', config('factures.societe.contact'), $email)
            );

            $output_writer = IOFactory::createWriter($template, 'Ods');
            $output_writer->save('/tmp/'.sprintf($this->placeholder_file, $no_facture, $client).'.ods');

            $template->disconnectWorksheets();
            unset($template);
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
