<?php

namespace App;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Templater
{
    /** @var IReader $template */
    protected $template;

    /** @var Worksheet $worksheet */
    protected $worksheet;

    /**
     * Constructeur
     * @param String $template
     */
    public function __construct(String $template)
    {
        $this->template = IOFactory::load($template);
        $this->template->getDefaultStyle()->getFont()->setName('Liberation Sans');
        $this->template->getDefaultStyle()->getFont()->setSize(8);

        $this->worksheet = $this->template->getActiveSheet();
    }

    /**
     * Set cell value
     * @param String $cell
     * @param String $placeholder
     * @param String $value
     */
    public function setCellValue(String $cell, String $placeholder, String $value)
    {
        $to_replace = $this->worksheet->getCell($cell)->getValue();

        $this->worksheet
             ->getCell($cell)
             ->setValue(str_replace($placeholder, $value, $to_replace));
    }

    /**
     * Get the template
     * @return $template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Disconnect worksheets to free memory
     */
    public function disconnectWorksheets()
    {
        $this->template->disconnectWorksheets();
    }
}
