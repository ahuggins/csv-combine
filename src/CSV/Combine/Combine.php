<?php

namespace CSV\Combine;

use CSV\Combine\CSV;
use Illuminate\Support\Collection;
use CSV\Combine\Exceptions\FileDoesNotExist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Combine extends Command
{
    protected $heading;

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
     * @param  OutputInterface $output [description]
     * @return [type]                  [description]
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $csv_names = $input->getArgument('csv_names');

        $contents = $this->processCsvFiles($csv_names);

        $this->writeFile($contents);
    }

    /**
     * Take the names of the files passed to command and process them.
     * Shift of the heading row in each file storing one copy
     * Then prepend the heading row
     * Flatten the Collection to one array
     * then implode it to a string separated by ','
     * @param  array $csv_names list of the csv names to process
     * @return string            Contents of the combined csv files
     */
    public function processCsvFiles($csv_names)
    {
        if (! $this->filesExist($csv_names)) {
            throw new FileDoesNotExist;
        }

        return (new Collection($csv_names))
            ->transform(function ($csv) {
                $csv = (new CSV($csv))->process();
                $rows = $this->grabRows($csv);
                $this->heading = $rows->shift()->flatten();
                return var_dump($rows->all());
            })
            ->prepend($this->heading)
            ->flatten()
            ->implode(',');
    }

    /**
     * Grabs the rows from the csv, removing any empty from the end
     * @param  string $csv the contents of the csv
     * @return Collection      an array of rows of csv data
     */
    public function grabRows($csv)
    {
        // filter removes the empty row left by explode
        return (new Collection(explode(',', $csv)))->chunk(3)->filter(function ($row) {
            if (count($row) === 3) {
                return $row;
            }
        });
    }

    /**
     * Writes file to stdout
     * @param  string $contents the contents of the file
     */
    public function writeFile($contents)
    {
        // file_put_contents('combined.csv', $contents);
        $file = fopen('php://stdout', 'w');
        fwrite($file, $contents);
        fclose($file);
        // This is here simply to bump the terminal prompt to new line.
        echo PHP_EOL;
    }

    public function filesExist($csv_names)
    {
        foreach ($csv_names as $csv) {
            if (! file_exists($csv)) {
                return false;
            }
        }
        return true;
    }
}
