<?php

namespace App\Console\Commands;

use App\Services\Solr\SolrConfig;
use App\Services\Solr\SolrService;
use Illuminate\Console\Command;

class SolrCoresMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solrCores:maintenance {--action=} {--core=*} {--coreVersion=} {--y} {--force} {--f} {--avoidDuplicates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to execute the maintenance of the Solr cores.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private function printErrorMessage()
    {
        echo 'ERROR! You must provide a valid --action parameter. This can be either "CREATE", "DELETE", or "REINDEX"' . PHP_EOL;
	    echo 'SYNTAX: solrCores:maintenance {--action=} {--core=*} {--coreVersion=}' . PHP_EOL;
    }

    /**
     * Gets the excluded cores array.
     * @param array $cores
     * @param array $coresToManage
     * @return array
     */
    private function getExcludedCores($cores, $coresToManage)
    {
        $excludedCores = [];

        foreach ($cores as $core) {
            if (!in_array($core, $coresToManage)) $excludedCores[] = $core;
        }

        return $excludedCores;
    }

    /**
     * Gets the core name versioned.
     * @param SolrService $solrService
     * @param string $core
     * @param string $coreVersion
     * @return string
     */
    private function getCoreNameVersioned(SolrService $solrService, $core, $coreVersion)
    {
        $solrVersionUsed = $solrService->getCoreVersion($coreVersion);
        return $solrService->getCoreNameVersioned($core, $solrVersionUsed);
    }

    /**
     * Manages the cores creation.
     * @param SolrService $solrService
     * @param string $path
     * @param array $coresToInstall
     * @param array $solrCores
     * @param string $coreVersion
     * @return bool
     */
    private function manageCoresCreation(SolrService $solrService, $path, $coresToInstall, $solrCores, $coreVersion, $confirmation)
    {
        // Asks for a double user confirmation
        if ($confirmation || $this->confirm('New CORES with version '.$coreVersion.' will be created. OK?', false)) {
            // Iterates through the cores...
            foreach ($coresToInstall as $core) {
                // Checks if the core exists
                if (in_array($core, $solrCores)) {
                    // Gets the core name versioned
                    $coreNameVersioned = $this->getCoreNameVersioned($solrService, $core, $coreVersion);
                    echo "Creating CORE " . $coreNameVersioned . PHP_EOL;

                    // Executes the shell command showing error or success
                    echo shell_exec("$path create $core $coreNameVersioned");
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Manages the cores deleting.
     * @param SolrService $solrService
     * @param string $path
     * @param array $coresToDelete
     * @param array $solrCores
     * @param string $coreVersion
     * @return bool
     */
    private function manageCoresDeleting(SolrService $solrService, $path, $coresToDelete, $solrCores, $coreVersion, $confirmation)
    {
        // Asks for a double user confirmation
        if ($confirmation || $this->confirm('Deletion of a set of cores will remove information. ' .
                            'OK to proceed?', false)) {
            if ($confirmation || $this->confirm('Are you absolutely sure? This may cause irreversible damage to the stored data.', false)) {
                // Iterates through the cores...
                foreach ($coresToDelete as $core) {
                    // Checks if the core exists
                    if (in_array($core, $solrCores)) {
                        // Gets the core name versioned
                        $coreNameVersioned = $this->getCoreNameVersioned($solrService, $core, $coreVersion);
                        echo "Deleting CORE " . $coreNameVersioned . PHP_EOL;

                        // Executes the shell command
                        echo shell_exec("$path delete $core $coreNameVersioned");
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Manages the cores reindexing.
     * @param SolrService $solrService
     * @param array $coresToReindex
     * @param array $solrCores
     * @param string $coreVersion
     * @param bool $confirmation
     * @param bool $force
     * @param bool $avoidDuplicates
     * @return bool
     */
    private function manageCoresReindexing(
        SolrService $solrService,
        $coresToReindex,
        $solrCores,
        $coreVersion,
        $confirmation,
        $force,
        $avoidDuplicates
    ) {
        // Asks for a double user confirmation
        if ($confirmation || $this->confirm('Reindexing of a set of cores could remove stored information. ' .
                            'OK to proceed?', false)) {
            if ($confirmation || $this->confirm('Are you absolutely sure? This may cause irreversible damage to the stored data.', false)) {
                // Checks the excluded cores
                $excludedCores = $this->getExcludedCores($solrCores, $coresToReindex);
    
                // Sets the array for the arguments
                $args = [
                    '--exclude'     => $excludedCores,
                    '--solrVersion' => $solrService->getCoreVersion($coreVersion)
                ];

                // Adds, or not, the Tika arguments
                if ($force) $args['--force'] = true;
                if ($avoidDuplicates) $args['--avoidDuplicates'] = true;

                // Calls to the proper command to execute the action
                $this->call("solr:reindex", $args);
            }
        }

        return false;
    }

    /**
     * Execute the console command.
     * @param SolrService $solrService
     * @param SolrConfig $solrConfig
     * @return bool
     */
    public function handle(SolrService $solrService, SolrConfig $solrConfig)
    {
        // Reads the command parameters
        $action = $this->option('action');
        $coresToManage = $this->option('core');
        $coreVersion = $this->option('coreVersion');
        $confirmation = $this->option('y');
        $force = $this->option('f') || $this->option('force');
        $avoidDuplicates = $this->option('avoidDuplicates');

        // Checks if the action is correct
        if ($action === null || ($action !== 'CREATE' && $action !== 'DELETE' && $action !== 'REINDEX' && $action !== 'ALL')) {
            $this->printErrorMessage();
            return false;
        }

        // Gets the entire set of Solr cores available in the config file
        $solrCores = $solrConfig->getSolrCores();

        // Checks the cores to manage
        $allCores = (count($coresToManage) == 0);
        $coresToManage = ($allCores ? $solrCores : $coresToManage);
        // Gets the script path
        $path = getcwd() . '/scripts/solr_cores_management.sh';

        // Checks the action to execute
        if ($action === 'CREATE' || $action === 'DELETE') {
            // Checks the action to execute
            if ($action === 'CREATE') {
                return $this->manageCoresCreation($solrService, $path, $coresToManage, $solrCores, $coreVersion, $confirmation);
            } else if ($action === 'DELETE') {
                return $this->manageCoresDeleting($solrService, $path, $coresToManage, $solrCores, $coreVersion, $confirmation);
            }
        } else if ($action === 'REINDEX') {
            return $this->manageCoresReindexing($solrService, $coresToManage, $solrCores, $coreVersion, $confirmation,
                                                $force, $avoidDuplicates);
        } else if ($action === 'ALL') {
            $this->manageCoresDeleting($solrService, $path, $coresToManage, $solrCores, $coreVersion, $confirmation);
            $this->manageCoresCreation($solrService, $path, $coresToManage, $solrCores, $coreVersion, $confirmation);
            $this->manageCoresReindexing($solrService, $coresToManage, $solrCores, $coreVersion, $confirmation,
                                            $force, $avoidDuplicates);
        }

        return false;
    }
}
