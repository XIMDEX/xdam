<?php


namespace App\Services\File;


use App\Models\CrawlerJob;
use App\Models\File;
use App\Helpers\PropsConverter;
use App\Models\FileDeleted;
use App\Services\Thumbnail\ThumbnailGeneratorInterface;
use Solarium\Core\Query\DocumentInterface;

class FileService implements FileServiceInterface
{
    /**
     * @var File
     */
    private $file;
    /**
     * @var ThumbnailGeneratorInterface
     */
    private $thumbnailService;

    /**
     * FileService constructor.
     * @param File $file
     * @param ThumbnailGeneratorInterface $thumbnailService
     */
    public function __construct(File $file, ThumbnailGeneratorInterface $thumbnailService)
    {
        $this->file = $file;
        $this->thumbnailService = $thumbnailService;
    }

    /**
     * Get local file path from a document solr id
     * @param $solrId
     * @return \Exception|string
     */
    public function getLocalFilePathFromSolrId( $solrId)
    {

        $cacheDir = config('app.crawler_cache_dir', '');

        if (empty($cacheDir)){
            return new \Exception('App crawler cache dir is not setted!');
        }

        $solrIdToPath = str_replace(":", "", $solrId);

        return $cacheDir . DIRECTORY_SEPARATOR . $solrIdToPath;
    }

    /**
     * Get dam file directory from .env
     * @return \Exception|\Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    public function getDamFileDirectory()
    {
        $damFilesDir = config('app.dam_files_dir', '');

        if (empty($damFilesDir)) {
            return new \Exception('App dam file dir is not setted!');
        }

        return $damFilesDir;
    }

    /**
     * Make a copy of a file in the dam directory
     * @param File $file
     * @return File
     */
    public function makeBackupOfFile( File $file)
    {
        $path =  str_replace(":", "", $file->solr_id);
        $damFolder = $this->getDamFileDirectory();
        $pathFileDam = $damFolder . DIRECTORY_SEPARATOR ;
        $createdPath = $this->make_dir($pathFileDam . dirname($path), 0755);

        if($createdPath){
            copy($file->crawler_path, $pathFileDam . $path);
            $file->dam_path = $pathFileDam . $path;
        }
        return $file;
    }

    /**
     * Check if solrid already exists
     * @param $solrId
     * @return mixed
     */
    public function fileSolrIDAlreadyExists( $solrId) {
       return File::where('solr_id', $solrId)->get();
    }

    /**
     * Get origin from uri solr
     * @param $uri
     * @return mixed|string
     */
    public function getOriginFromSolrId( $uri){
        $explodedString = explode(":/", $uri, 2);
        if (empty($explodedString)){
            return "null";
        }
        return $explodedString[0];
    }

    /**
     * delete physical file associated with file in db
     * @param File $file
     */
    public function deleteAssociatedContent( File $file) {
        if (file_exists($file->dam_path)) {
            unlink($file->dam_path);
        }
    }

    /**
     * Convert mime type to type of file
     * @param $mimeType
     * @return string
     */
    public function getTypeByMimeType( $mimeType) {
        $parts = explode('/', $mimeType);
        $typeIdentifier = strtolower($parts[0]);

        switch($typeIdentifier)
        {
            case "video":
            case "audio":
            case "image":
                return $typeIdentifier;
            default:
                return 'file';
        }
    }

    /**
     * Create a new file row in db
     * @param CrawlerJob $crawlerJob
     * @param Object $indexedDoc
     * @param File $file
     * @return File|\ErrorException
     */
    public function createNewFile( CrawlerJob $crawlerJob, Object $indexedDoc, File $file)
    {
        $file->solr_id = $indexedDoc->id;
        try {
            $file->crawler_path = $this->getLocalFilePathFromSolrId( $file->solr_id );
        } catch ( \Exception $e ) {
            return new \ErrorException( $e);
        }

        $pathParts = pathinfo($file->crawler_path);

        $file->filename = $pathParts['filename'];
        $file->extension = $pathParts['extension'];
        $file->mime_type = $indexedDoc->mime_type;
        $file->type = $this->getTypeByMimeType($file->extension);
        $file->uri = $indexedDoc->uri;
        $file->encoding = $indexedDoc->content_encoding;
        $file->content_type = $indexedDoc->content_type;
        $file->size = $indexedDoc->stream_size;
        $file->metadata = "";
        $file->origin = $this->getOriginFromSolrId( $file->solr_id );
        $file->crawler_job_id = $crawlerJob->id;

        try {
            $file->hash = $this->getHashForFile( $file->filename, $file->crawler_path );
        } catch ( \Exception $e ) {
            return new \ErrorException( $e);
        }
        $file = $this->makeBackupOfFile($file);
        $file->save();

        $this->thumbnailService->create($file);
        return $file;
    }

    /**
     * Get hash from file content
     * @param $filename
     * @param $pathToFile
     * @return false|string
     */
    public function getHashForFile($filename, $pathToFile)
    {
        if ($pathToFile){
            return hash('sha256',$filename) . hash_file('sha256', $pathToFile);
        }
        return false;
    }

    /**
     * Make dir recursive
     * @param $path
     * @param int $permissions
     * @return bool
     */
    public function make_dir( $path, $permissions = 0755 ) {
        return is_dir( $path ) || mkdir( $path, $permissions, true );
    }

    /**
     * set file as deleted
     * @param File $file
     */
    public function processFileDeleted(File $file) {
        $file->deletedFile()->save(new FileDeleted);
    }

    /**
     * Process a document from crawler core
     * @param CrawlerJob $crawlerJob
     * @param DocumentInterface $crawlerFile
     * @return File|\ErrorException
     */
    public function processCrawlerFile(CrawlerJob $crawlerJob, DocumentInterface $crawlerFile)
    {
        $normalizedDoc = PropsConverter::arrayPropsToObjectProps($crawlerFile->getFields());
        $filesWithSameId = $this->fileSolrIDAlreadyExists($normalizedDoc->id);

        if( count($filesWithSameId) >= 1 ) {
            foreach ($filesWithSameId as $fileItem) {
                $this->deleteAssociatedContent($fileItem);
                $fileItem->delete();
            }
        }

        return $this->createNewFile($crawlerJob, $normalizedDoc, new File());
    }

}
