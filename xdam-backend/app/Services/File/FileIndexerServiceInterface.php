<?php

namespace App\Services\File;

use App\Models\File;
use TSterker\Solarium\SolariumManager;

interface FileIndexerServiceInterface
{
    /**
     * FileIndexerService constructor.
     * @param File $file
     * @param SolariumManager $solarium
     */
    public function __construct( File $file, SolariumManager $solarium );

    /**
     * Set current core to dam core
     */
    public function setCurrentCore();

    /**
     * Add a document to dam solr
     * @param File $file
     */
    public function indexFile( File $file );

    public function getFileById();

    /**
     * Iterate over file table and process each file and append to dam index
     */
    public function indexFilesInDam();
}
