<?php

namespace CSV\Combine;

use Illuminate\Support\Collection;

class CSV
{
    protected $filename;

    protected $contents = '';

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Add the filename to each row.
     * @return string    contents with added column of filename
     */
    public function process()
    {
        $file = fopen($this->filename, 'r');
        while ($contents = fgetcsv($file, 8000)) {
            $this->contents .= $contents[0] . ', ' . $contents[1] . ', ' . $this->filename() . ',   ' . PHP_EOL;
        }
        return $this->contents;
    }

    /**
     * If the content is empty that means we are on the heading line,
     * return the heading,
     * else return the filename
     * @return string
     */
    protected function filename()
    {
        return empty($this->contents) ? 'filename' : $this->filename;
    }
}
