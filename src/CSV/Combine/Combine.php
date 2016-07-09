<?php

namespace CSV\Combine;

use CSV\Combine\ProcessCSV;
use CSV\Combine\Exceptions\FileDoesNotExist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Combine extends Command
{
    protected $processor;

    public function __construct(ProcessCSV $processor = null)
    {
        parent::__construct();
        $this->processor = $processor ?: new ProcessCSV;
    }
    
    /**
     * Setting up tool for Symfony Console Command
     */
    protected function configure()
    {
        $this->setName('combine')
            ->setDescription('Combine the given CSV files')
            ->addArgument('csv_names', InputArgument::IS_ARRAY, 'List of filenames you want combined');
    }

    /**
     * Required by Symfony Console Command
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csv_names = $input->getArgument('csv_names');

        $this->checkIfFilesExist($csv_names);

        $this->processCsvFiles($csv_names);
    }

    /**
     * Take the names of the files passed to command and process them.
     * @param  array $csv_names list of the csv names to process
     */
    protected function processCsvFiles(array $csv_names)
    {
        foreach ($csv_names as $csv) {
            $this->processor->load($csv)->process();
        }
    }

    /**
     * make sure the file exists
     * @param  array $csv_names An array of csv filenames
     */
    protected function checkIfFilesExist(array $csv_names)
    {
        foreach ($csv_names as $csv) {
            if (! file_exists($csv)) {
                throw new FileDoesNotExist;
            }
        }
    }
}
