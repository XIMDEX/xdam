<?php


namespace App\Services\File;


use App\Enums\YesNo;
use App\Models\File;
use Carbon\Carbon;
use TSterker\Solarium\SolariumManager;

class FileIndexerService implements FileIndexerServiceInterface
{
    /**
     * @var File
     */
    private $file;
    /**
     * @var SolariumManager
     */
    private $solarium;
    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $crawlerCore;

    /**
     * FileIndexerService constructor.
     * @param File $file
     * @param SolariumManager $solarium
     */
    public function __construct( File $file, SolariumManager $solarium)
    {
        $this->solarium = $solarium;
        $this->file = $file;
    }

    /**
     * Set current core to dam core
     */
    public function setCurrentCore(){
        $this->crawlerCore = config('app.solr_core_dam', 'dam');
        if($this->solarium->getEndpoint()->getCore() != $this->crawlerCore) {
            $this->solarium->getEndpoint()->setCore($this->crawlerCore);
        }
    }

    /**
     * Add a document to dam solr
     * @param File $file
     */
    public function indexFile( File $file) {
        $createCommand = $this->solarium->createUpdate();

        $document = $createCommand->createDocument();
        $document->id = $file->id;
        $document->name = [$file->filename];
        $document->extension = $file->extension;
        $document->owner = "admin";
        $document->preview = route('resource.image', ['id' => $file->id]);;
        $document->public = [true];
        $document->type = $file->type;
        $document->mime_type = $file->mime_type;
        $document->auth_groups = ["test"];
        $document->auth_users = ["test"];
        $document->tags = ["1","2"];
        $document->created_at = Carbon::now();
        $document->updated_at = $file->indexed_at;

        $createCommand->addDocument($document);
        $createCommand->addCommit();
        $this->solarium->update($createCommand);
    }

    public function getFileById() {
        $this->setCurrentCore();

    }

    /**
     * Iterate over file table and process each file and append to dam index
     */
    public function indexFilesInDam(){
        $this->setCurrentCore();
        $filesNotIndexed = $this->file::where('in_dam_index', YesNO::No)->get();

        foreach($filesNotIndexed as $file) {
            $file->in_dam_index = YesNo::Yes;
            $file->indexed_at = Carbon::now();

            /* If document exists in index, so update the document, if it doesn't exist create a new one */
            $this->indexFile($file);
            $file->save();
        }
    }
}
