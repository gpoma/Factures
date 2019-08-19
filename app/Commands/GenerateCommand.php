<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use League\Csv\Reader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Templater;

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
    public function handle(Templater $templater)
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

            $templater->setCellValue('E1', '%%date_court%%', date('d/m/Y'));
            $templater->setCellValue('B7', '%%societe%%', config('factures.societe.name'));
            $templater->setCellValue('B8', '%%societe_statut%%', config('factures.societe.statut'));
            $templater->setCellValue('B9', '%%societe_statut2%%', config('factures.societe.statut2'));
            $templater->setCellValue('B10', '%%societe_adresse%%', config('factures.societe.adresse'));
            $templater->setCellValue('B11', '%%societe_cp%%', config('factures.societe.cp'));
            $templater->setCellValue('B12', '%%societe_email%%', config('factures.societe.contact'));

            $templater->setCellValue('E7', '%%client%%', config('clients.client.'.$client.'.name'));
            $templater->setCellValue('E8', '%%client_adresse%%', config('clients.client.'.$client.'.adresse'));
            $templater->setCellValue('E9', '%%client_cp%%', config('clients.client.'.$client.'.cp'));
            $templater->setCellValue('E10', '%%client_siret%%', config('clients.client.'.$client.'.siret'));

            $templater->setCellValue('B14', '%%siren%%', config('factures.societe.administratif.siren'));
            $templater->setCellValue('B15', '%%immatriculation%%', config('factures.societe.administratif.immatriculation'));
            $templater->setCellValue('B16', '%%tva%%', config('factures.societe.administratif.tva'));
            $templater->setCellValue('B17', '%%naf%%', config('factures.societe.administratif.naf'));

            $templater->setCellValue('E19', '%%date_long%%', date('d l Y'));

            $templater->setCellValue('B21', '%%facture%%', $no_facture);

            $templater->setCellValue('B33', '%%reglement%%', config('factures.societe.reglement.texte'));
            $templater->setCellValue('B35', '%%rib%%', config('factures.societe.reglement.rib'));
            $templater->setCellValue('B36', '%%iban%%', config('factures.societe.reglement.iban'));
            $templater->setCellValue('B37', '%%bic%%', config('factures.societe.reglement.bic'));

            $output_writer = IOFactory::createWriter($templater->getTemplate(), 'Ods');
            $output_writer->save('/tmp/'.sprintf($this->placeholder_file, $no_facture, $client).'.ods');

            $templater->disconnectWorksheets();
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
