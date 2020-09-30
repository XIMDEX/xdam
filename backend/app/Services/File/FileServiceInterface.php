<?php

namespace App\Services\File;

use App\Models\CrawlerJob;
use App\Models\File;
use App\Services\Thumbnail\ThumbnailGeneratorInterface;
use Solarium\Core\Query\DocumentInterface;

interface FileServiceInterface
{
    /**
     * FileService constructor.
     * @param File $file
     * @param ThumbnailGeneratorInterface $thumbnailService
     */
    public function __construct( File $file, ThumbnailGeneratorInterface $thumbnailService );

    /**
     * Get local file path from a document solr id
     * @param $solrId
     * @return \Exception|string
     */
    public function getLocalFilePathFromSolrId( $solrId );

    /**
     * Get dam file directory from .env
     * @return \Exception|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getDamFileDirectory();

    /**
     * Make a copy of a file in the dam directory
     * @param File $file
     * @return File
     */
    public function makeBackupOfFile( File $file );

    /**
     * Check if solrid already exists
     * @param $solrId
     * @return mixed
     */
    public function fileSolrIDAlreadyExists( $solrId );

    /**
     * Get origin from uri solr
     * @param $uri
     * @return mixed|string
     */
    public function getOriginFromSolrId( $uri );

    /**
     * delete physical file associated with file in db
     * @param File $file
     */
    public function deleteAssociatedContent( File $file );

    /**
     * Convert mime type to type of file
     * @param $mimeType
     * @return string
     */
    public function getTypeByMimeType( $mimeType );

    /**
     * Create a new file row in db
     * @param CrawlerJob $crawlerJob
     * @param Object $indexedDoc
     * @param File $file
     * @return File|\ErrorException
     */
    public function createNewFile( CrawlerJob $crawlerJob, object $indexedDoc, File $file );

    /**
     * Get hash from file content
     * @param $filename
     * @param $pathToFile
     * @return false|string
     */
    public function getHashForFile( $filename, $pathToFile );

    /**
     * Make dir recursive
     * @param $path
     * @param int $permissions
     * @return bool
     */
    public function make_dir( $path, $permissions = 0755 );

    /**
     * set file as deleted
     * @param File $file
     */
    public function processFileDeleted( File $file );

    /**
     * Process a document from crawler core
     * @param CrawlerJob $crawlerJob
     * @param DocumentInterface $crawlerFile
     * @return File|\ErrorException
     */
    public function processCrawlerFile( CrawlerJob $crawlerJob, DocumentInterface $crawlerFile );
}
