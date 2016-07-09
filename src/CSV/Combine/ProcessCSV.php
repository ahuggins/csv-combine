<?php

namespace CSV\Combine;

use LimitIterator;
use SplFileObject;

class ProcessCSV
{
    protected $filename;

    protected $contents = [];

    protected $file;

    protected $start_with_row = 0;

    protected $i = 0;
    
    /**
     * set the new filename and load the file
     * @param  string $filename filename of csv to load
     * @return ProcessCSV        For chaining calls
     */
    public function load(string $filename)
    {
        $this->file = new SplFileObject($filename);

        $this->removeHeadingIfNotFirstFileProcessed($filename);

        return $this;
    }

    /**
     * Loop over each row and pass to write().
     */
    public function process()
    {
        foreach (new LimitIterator($this->file, $this->start_with_row) as $row) {
            if (! empty($row)) {
                $this->write($row);
            }
        }
    }

    /**
     * write row to stdout
     * @param  array  $row The array represenation of a row
     */
    protected function write($row)
    {
        (new SplFileObject('php://stdout'))->fputcsv(
            [$row, $this->filename()]
        );
        $this->i++;
    }

    /**
     * If the content is empty that means we are on the heading line,
     * return the heading,
     * @param  int    $i the row number
     * @return string    heading or the filename
     */
    protected function filename()
    {
        return $this->i === 0 ? 'filename' : $this->filename;
    }


    /**
     * Remove the heading row if not first file processed
     * so we do not have duplicate heading row
     * @param  string $filename The filename of the csv
     */
    protected function removeHeadingIfNotFirstFileProcessed(string $filename)
    {
        if (! empty($this->filename)) {
            $this->start_with_row = 1;
        }

        $this->filename = $filename;
    }
}
