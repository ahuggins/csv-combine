<?php

namespace CSV\Combine;

use LimitIterator;
use SplFileObject;

class ProcessCSV
{
    protected $filename;

    protected $file;

    /**
     * Loop over each row and pass to write().
     */
    public function process()
    {
        while ($row = $this->file->fgetcsv()) {
            $this->write($row);
        }
    }

    /**
     * write row to stdout
     * @param  array  $row The array represenation of a row
     */
    public function write(array $row)
    {
        (new SplFileObject('php://stdout'))->fputcsv(
            [$row[0], $row[1], $this->filename()]
        );
    }

    /**
     * If the content is empty that means we are on the heading line,
     * return the heading,
     * @return string    heading or the filename
     */
    protected function filename()
    {
        return empty($this->filename) ? 'filename' : $this->filename;
    }

    /**
     * set the new filename and load the file
     * @param  string $filename filename of csv to load
     * @return ProcessCSV        For chaining calls
     */
    public function load(string $filename)
    {
        $this->file = new SplFileObject($filename);
        $this->file->setFlags(SplFileObject::SKIP_EMPTY);

        $this->skipHeadingIfNotFirstFileProcessed($filename);

        return $this;
    }

    /**
     * Remove the heading row if not first file processed
     * so we do not have duplicate heading row
     * @param  string $filename The filename of the csv
     */
    public function skipHeadingIfNotFirstFileProcessed(string $filename)
    {
        if (! empty($this->filename)) {
            $this->file->current();
            $this->file->next();
        }

        $this->filename = $filename;
    }
}
