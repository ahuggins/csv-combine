<?php

namespace CSV\Combine;

use SplFileObject;

class ProcessCSV
{
    protected $filename;

    protected $file;

    protected $output;

    public function __construct(SplFileObject $output = null)
    {
        $this->output = $output ?? new SplFileObject('php://stdout', 'w');
    }
    
    /**
     * Set the File property, set the flags to skip empty lines,
     * skip the heading row. Set the filename.
     * @param  string $filename filename of csv to load
     * @return ProcessCSV        For chaining calls
     */
    public function load(string $filename)
    {
        $this->file = new SplFileObject($filename);
        $this->file->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::READ_AHEAD |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        $this->skipHeadingIfNotFirstFileProcessed();

        $this->filename = $filename;

        return $this;
    }

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
    protected function write(array $row)
    {
        $this->output->fputcsv(
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
        return $this->filename ?? 'filename';
    }

    /**
     * Skip the heading row if not first file processed
     * so we do not have duplicate heading row
     */
    protected function skipHeadingIfNotFirstFileProcessed()
    {
        if (! empty($this->filename)) {
            $this->file->current();
            $this->file->next();
        }
    }
}
