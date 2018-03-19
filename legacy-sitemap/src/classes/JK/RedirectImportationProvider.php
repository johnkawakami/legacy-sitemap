<?php

namespace JK;

/** 
 * Manages importation of redirect rules.
 *
 * Importation takes data from a source table of origin files/URLs and destination URLs,
 * persists that data to a database, and then saves the database to a destination file
 * format.
 *
 * The canonical example is reading an existing .htaccess file for redirects, saving it
 * to a database file, then merging in a new table of redirects. The database is then
 * dumped back to the .htaccess file.
 *
 * Example:
 *    $rip = new RedirectImportationProvider( $SQLdatastore, $formInput, $htaccessGateway );
 *
 *
 * @property $importedRows is an array of redirects processed:
 * <pre>
 *      [
 *             { "file": "old/path/tofile.html", "url": "http://new.url" }, 
 *             ...
 *      ]
 *  </pre>
 *
 */
class RedirectImportationProvider 
{
    protected $errors;
    private $table;
    private $source;
    private $destination;
    private $importedRows;

    function __construct($table, $source, $destination) 
    {
        $this->table = $table;
        $this->source = $source;
        $this->destination = $destination;
        $this->importedRows = [];
    }

    /**
     * Imports the text and merges it with existing redirects.
     * Then, writes it back.
     */
    function importText($text)
    {
        // load the destination data into the table
        $this->destination->loadTable();
        $this->table->addRedirects($this->destination->getData());

        // read the source data, convert to table
        $this->source->loadText($text);

        // put the source data into the table, using a fuzzy match
        foreach($this->source->getData() as $data) {
            $newdata = $this->table->addRedirectBestMatchFirst($this->stripHttps($data['file']), $data['url']); 
            $this->importedRows[] = $newdata;
        }

        // save to the destination 
        $this->destination->replaceData($this->table->getData());
        $this->destination->saveTable();
    }

    function stripHttps($url): string 
    {
        return preg_replace('/^http[s]*:\\/\\//', '', $url);
    }

    function getImportData(): array 
    {
        return $this->source->getData();
    }

    function getImportErrors(): array
    {
        return $this->source->getErrors();
    }

    function getImportedRows(): array 
    {
        return $this->importedRows;
    }
}
